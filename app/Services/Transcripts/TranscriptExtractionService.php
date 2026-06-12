<?php

namespace App\Services\Transcripts;

use App\Ai\Agents\TranscriptExtractionAgent;
use App\Models\TranscriptSession;
use App\Services\Ai\TextProviderResolver;
use App\Services\Generation\PromptBuilder;
use Illuminate\Contracts\Support\Arrayable;
use Throwable;

class TranscriptExtractionService
{
    public function __construct(
        protected TextProviderResolver $textProviderResolver,
        protected PromptBuilder $promptBuilder,
    ) {}

    public function extractAndPersist(TranscriptSession $transcriptSession): TranscriptSession
    {
        $transcriptSession->forceFill([
            'status' => 'extracting',
        ])->save();

        try {
            $response = TranscriptExtractionAgent::make()->prompt(
                $this->promptBuilder->buildTranscriptExtractionPrompt($transcriptSession->transcript_text),
                provider: $this->textProviderResolver->providerChain(),
                timeout: (int) config('services.gemini.timeout', 120),
            );

            /** @var array<string, mixed> $structured */
            $structured = $response instanceof Arrayable ? $response->toArray() : throw new \RuntimeException('Expected a structured AI response.');

            /** @var array<string, mixed> $payload */
            $payload = [
                'project_name' => $structured['project_name'],
                'project_summary' => $structured['project_summary'],
                'target_users' => $structured['target_users'],
                'goals' => $structured['goals'],
                'key_features' => $structured['key_features'],
                'template_family_recommendation' => $structured['template_family_recommendation'],
                'template_family_options' => $structured['template_family_options'],
                'design_system_recommendation' => $structured['design_system_recommendation'],
                'design_system_options' => $structured['design_system_options'],
            ];

            $transcriptSession->forceFill([
                'status' => 'clarifying',
                'extracted_context' => [
                    'project_name' => $payload['project_name'],
                    'project_summary' => $payload['project_summary'],
                    'target_users' => $payload['target_users'],
                    'goals' => $payload['goals'],
                    'key_features' => $payload['key_features'],
                ],
                'layout_recommendations' => $payload['template_family_options'],
                'design_system_recommendations' => $payload['design_system_options'],
                'project_name' => $payload['project_name'],
                'project_summary' => $payload['project_summary'],
                'target_users' => $payload['target_users'],
                'goals' => $payload['goals'],
                'key_features' => $payload['key_features'],
                'template_family' => $payload['template_family_recommendation'],
                'design_system' => $payload['design_system_recommendation'],
            ])->save();
        } catch (Throwable $throwable) {
            $transcriptSession->forceFill([
                'status' => 'draft',
            ])->save();

            throw $throwable;
        }

        return $transcriptSession->refresh();
    }
}
