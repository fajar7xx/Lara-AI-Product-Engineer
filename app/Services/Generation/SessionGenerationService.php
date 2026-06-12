<?php

namespace App\Services\Generation;

use App\Ai\Agents\FunctionalRequirementsGenerationAgent;
use App\Ai\Agents\HtmlPageContentAgent;
use App\Ai\Agents\PrdGenerationAgent;
use App\Ai\Agents\UserStoriesGenerationAgent;
use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use App\Services\Ai\TextProviderResolver;
use Illuminate\Contracts\Support\Arrayable;
use Laravel\Ai\Exceptions\FailoverableException;
use Throwable;

class SessionGenerationService
{
    public function __construct(
        protected TextProviderResolver $textProviderResolver,
        protected MarkdownBlueprintService $markdownBlueprintService,
        protected HtmlAssemblyService $htmlAssemblyService,
        protected PromptBuilder $promptBuilder,
    ) {}

    /**
     * @param  list<string>|null  $targetTypes
     * @return list<string>
     */
    public function ensureOutputs(TranscriptSession $transcriptSession, ?array $targetTypes = null): array
    {
        $types = $targetTypes ?? GenerationOutput::supportedTypes();

        foreach ($types as $type) {
            $transcriptSession->generationOutputs()->firstOrCreate(
                ['type' => $type],
                ['status' => GenerationOutput::STATUS_PENDING]
            );
        }

        return $types;
    }

    /**
     * @param  list<string>|null  $targetTypes
     */
    public function resetOutputsForRegeneration(TranscriptSession $transcriptSession, ?array $targetTypes = null): void
    {
        $types = $this->ensureOutputs($transcriptSession, $targetTypes);

        $outputs = $transcriptSession->generationOutputs()
            ->whereIn('type', $types)
            ->get();

        foreach ($outputs as $output) {
            $output->forceFill([
                'status' => GenerationOutput::STATUS_PENDING,
                'content' => null,
                'error_message' => null,
                'revision_number' => $output->revision_number + 1,
                'clarification_snapshot' => $this->clarificationSnapshot($transcriptSession),
            ])->save();
        }
    }

    /**
     * @param  list<string>|null  $targetTypes
     */
    public function generate(TranscriptSession $transcriptSession, ?array $targetTypes = null): TranscriptSession
    {
        $types = $this->ensureOutputs($transcriptSession, $targetTypes);

        $transcriptSession->forceFill([
            'status' => TranscriptSession::STATUS_GENERATING,
        ])->save();

        $outputs = $transcriptSession->generationOutputs()
            ->whereIn('type', $types)
            ->get()
            ->keyBy('type');

        foreach ($types as $type) {
            /** @var GenerationOutput $output */
            $output = $outputs[$type];

            if ($output->status === GenerationOutput::STATUS_COMPLETED) {
                continue;
            }

            $output->forceFill([
                'status' => GenerationOutput::STATUS_PROCESSING,
                'error_message' => null,
                'clarification_snapshot' => $this->clarificationSnapshot($transcriptSession),
            ])->save();

            try {
                $content = $this->generateOutputContent($transcriptSession, $type);

                $output->forceFill([
                    'status' => GenerationOutput::STATUS_COMPLETED,
                    'content' => $content,
                    'error_message' => null,
                ])->save();
            } catch (FailoverableException $exception) {
                $output->forceFill([
                    'status' => GenerationOutput::STATUS_PENDING,
                    'error_message' => $this->transientProviderMessage($exception),
                ])->save();

                throw $exception;
            } catch (Throwable $throwable) {
                $output->forceFill([
                    'status' => GenerationOutput::STATUS_FAILED,
                    'error_message' => $throwable->getMessage(),
                ])->save();
            }
        }

        return $this->refreshSessionStatus($transcriptSession);
    }

    public function refreshSessionStatus(TranscriptSession $transcriptSession): TranscriptSession
    {
        $outputs = $transcriptSession->generationOutputs()->get();

        $completedCount = $outputs->where('status', GenerationOutput::STATUS_COMPLETED)->count();
        $failedCount = $outputs->where('status', GenerationOutput::STATUS_FAILED)->count();

        $status = match (true) {
            $completedCount === $outputs->count() && $outputs->isNotEmpty() => TranscriptSession::STATUS_COMPLETED,
            $completedCount > 0 && $failedCount > 0 => TranscriptSession::STATUS_PARTIAL,
            $failedCount === $outputs->count() && $outputs->isNotEmpty() => TranscriptSession::STATUS_FAILED,
            default => TranscriptSession::STATUS_GENERATING,
        };

        $transcriptSession->forceFill([
            'status' => $status,
        ])->save();

        return $transcriptSession->refresh();
    }

    protected function generateOutputContent(TranscriptSession $transcriptSession, string $type): string
    {
        return match ($type) {
            GenerationOutput::TYPE_PRD => $this->generatePrd($transcriptSession),
            GenerationOutput::TYPE_USER_STORIES => $this->generateUserStories($transcriptSession),
            GenerationOutput::TYPE_FUNCTIONAL_REQUIREMENTS => $this->generateFunctionalRequirements($transcriptSession),
            GenerationOutput::TYPE_HTML_PAGE => $this->generateHtmlPage($transcriptSession),
            default => throw new \InvalidArgumentException('Unsupported output type.'),
        };
    }

    protected function generatePrd(TranscriptSession $transcriptSession): string
    {
        $response = $this->structuredResponse(
            PrdGenerationAgent::make()->prompt(
                $this->promptBuilder->buildPrdPrompt($transcriptSession),
                provider: $this->textProviderResolver->providerChain(),
                timeout: (int) config('services.gemini.timeout', 120),
            )
        );

        return $this->markdownBlueprintService->prd(
            $this->clarificationSnapshot($transcriptSession),
            $this->sanitizeGeneratedMarkdownBody($response['content'])
        );
    }

    protected function generateUserStories(TranscriptSession $transcriptSession): string
    {
        $response = $this->structuredResponse(
            UserStoriesGenerationAgent::make()->prompt(
                $this->promptBuilder->buildUserStoriesPrompt($transcriptSession),
                provider: $this->textProviderResolver->providerChain(),
                timeout: (int) config('services.gemini.timeout', 120),
            )
        );

        return $this->markdownBlueprintService->userStories(
            $this->clarificationSnapshot($transcriptSession),
            $this->sanitizeGeneratedMarkdownBody($response['content'])
        );
    }

    protected function generateFunctionalRequirements(TranscriptSession $transcriptSession): string
    {
        $response = $this->structuredResponse(
            FunctionalRequirementsGenerationAgent::make()->prompt(
                $this->promptBuilder->buildFunctionalRequirementsPrompt($transcriptSession),
                provider: $this->textProviderResolver->providerChain(),
                timeout: (int) config('services.gemini.timeout', 120),
            )
        );

        return $this->markdownBlueprintService->functionalRequirements(
            $this->clarificationSnapshot($transcriptSession),
            $this->sanitizeGeneratedMarkdownBody($response['content'])
        );
    }

    protected function generateHtmlPage(TranscriptSession $transcriptSession): string
    {
        $response = $this->structuredResponse(
            HtmlPageContentAgent::make()->prompt(
                $this->promptBuilder->buildHtmlPagePrompt($transcriptSession),
                provider: $this->textProviderResolver->providerChain(),
                timeout: (int) config('services.gemini.timeout', 120),
            )
        );

        return $this->htmlAssemblyService->render(
            $transcriptSession->template_family ?? 'landing',
            $transcriptSession->design_system ?? 'minimal',
            [
                'title' => $response['title'],
                'tagline' => $this->sanitizeInlineText($response['tagline']),
                'sections' => collect($response['sections'])
                    ->take(5)
                    ->map(fn (array $section): array => [
                        'heading' => $this->sanitizeInlineText($section['heading']),
                        'body' => $this->sanitizeInlineText($section['body']),
                    ])
                    ->all(),
            ],
        );
    }

    /**
     * @return array{
     *     project_name: string,
     *     project_summary: string,
     *     target_users: string,
     *     goals: array<int, string>,
     *     key_features: array<int, string>,
     *     template_family: string,
     *     design_system: string
     * }
     */
    public function clarificationSnapshot(TranscriptSession $transcriptSession): array
    {
        return [
            'project_name' => $transcriptSession->project_name ?? '',
            'project_summary' => $transcriptSession->project_summary ?? '',
            'target_users' => $transcriptSession->target_users ?? '',
            'goals' => $transcriptSession->goals ?? [],
            'key_features' => $transcriptSession->key_features ?? [],
            'template_family' => $transcriptSession->template_family ?? 'landing',
            'design_system' => $transcriptSession->design_system ?? 'minimal',
        ];
    }

    protected function sanitizeGeneratedMarkdownBody(string $content): string
    {
        $trimmed = trim($content);

        $withoutFences = preg_replace('/^```[a-zA-Z0-9_-]*\s*|\s*```$/m', '', $trimmed) ?? $trimmed;
        $withoutTopHeading = preg_replace('/^\s*# .+\n+/m', '', $withoutFences, 1) ?? $withoutFences;

        return trim($withoutTopHeading);
    }

    protected function sanitizeInlineText(string $content): string
    {
        $normalized = preg_replace('/\s+/', ' ', strip_tags($content)) ?? $content;

        return trim($normalized);
    }

    protected function transientProviderMessage(FailoverableException $exception): string
    {
        return sprintf(
            'Temporary AI provider issue: %s The system will retry automatically.',
            $exception->getMessage()
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function structuredResponse(mixed $response): array
    {
        if ($response instanceof Arrayable) {
            /** @var array<string, mixed> $structured */
            $structured = $response->toArray();

            return $structured;
        }

        throw new \RuntimeException('Expected a structured AI response.');
    }
}
