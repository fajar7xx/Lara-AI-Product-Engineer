<?php

use App\Jobs\GenerateSessionOutputs;
use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use App\Services\Exports\PdfExportService;
use App\Services\Generation\SessionGenerationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Transcript session')] class extends Component
{
    use AuthorizesRequests;

    public TranscriptSession $transcriptSession;

    /** @var array<int, string> */
    public array $selectedOutputTypes = [];

    public function mount(TranscriptSession $transcriptSession): void
    {
        $this->authorize('view', $transcriptSession);
    }

    public function regenerateSelectedOutputs(SessionGenerationService $sessionGenerationService): void
    {
        $this->authorize('update', $this->transcriptSession);

        $validated = $this->validate([
            'selectedOutputTypes' => ['required', 'array', 'min:1'],
            'selectedOutputTypes.*' => ['required', 'string', 'in:'.implode(',', GenerationOutput::supportedTypes())],
        ]);

        /** @var list<string> $types */
        $types = array_values($validated['selectedOutputTypes']);

        $sessionGenerationService->resetOutputsForRegeneration($this->transcriptSession, $types);

        GenerateSessionOutputs::dispatch($this->transcriptSession->id, $types);

        $this->selectedOutputTypes = [];
        $this->transcriptSession->refresh();
    }

    public function downloadPdf(PdfExportService $pdfExportService, string $type)
    {
        $this->authorize('view', $this->transcriptSession);

        $output = $this->outputs()->firstWhere('type', $type);

        abort_unless($output instanceof GenerationOutput, 404);
        abort_unless($output->status === GenerationOutput::STATUS_COMPLETED, 422);
        abort_if(blank($output->content), 422);

        $export = $pdfExportService->exportTranscriptOutput($this->transcriptSession, $output);

        return response()->download($export['path'], $export['filename'])->deleteFileAfterSend(true);
    }

    public function retryFailedOutputs(SessionGenerationService $sessionGenerationService): void
    {
        $this->authorize('update', $this->transcriptSession);

        $types = $this->outputs()
            ->where('status', GenerationOutput::STATUS_FAILED)
            ->pluck('type')
            ->values()
            ->all();

        if ($types === []) {
            return;
        }

        $sessionGenerationService->resetOutputsForRegeneration($this->transcriptSession, $types);

        GenerateSessionOutputs::dispatch($this->transcriptSession->id, $types);

        $this->transcriptSession->refresh();
    }

    /**
     * @return Collection<int, GenerationOutput>
     */
    public function outputs(): Collection
    {
        return $this->transcriptSession->generationOutputs()
            ->orderByRaw(
                "case type
                    when '".GenerationOutput::TYPE_PRD."' then 1
                    when '".GenerationOutput::TYPE_USER_STORIES."' then 2
                    when '".GenerationOutput::TYPE_FUNCTIONAL_REQUIREMENTS."' then 3
                    when '".GenerationOutput::TYPE_HTML_PAGE."' then 4
                    else 99
                end"
            )
            ->get();
    }

    #[Computed]
    public function sessionStatusColor(): string
    {
        return match ($this->transcriptSession->status) {
            TranscriptSession::STATUS_COMPLETED => 'green',
            TranscriptSession::STATUS_PARTIAL => 'amber',
            TranscriptSession::STATUS_FAILED => 'red',
            TranscriptSession::STATUS_GENERATING => 'blue',
            default => 'zinc',
        };
    }

    public function outputStatusColor(GenerationOutput $output): string
    {
        return match ($output->status) {
            GenerationOutput::STATUS_COMPLETED => 'green',
            GenerationOutput::STATUS_FAILED => 'red',
            GenerationOutput::STATUS_PROCESSING => 'blue',
            default => 'zinc',
        };
    }

    public function renderedMarkdown(GenerationOutput $output): string
    {
        return Str::markdown($output->content ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function outputStatusLabel(GenerationOutput $output): string
    {
        return match ($output->status) {
            GenerationOutput::STATUS_COMPLETED => 'Completed and ready to review.',
            GenerationOutput::STATUS_FAILED => 'Generation failed. You can retry this output.',
            GenerationOutput::STATUS_PROCESSING => 'Generation is currently running.',
            default => 'Waiting to be generated.',
        };
    }

    #[Computed]
    public function hasSelectedOutputs(): bool
    {
        return $this->selectedOutputTypes !== [];
    }

    #[Computed]
    public function hasFailedOutputs(): bool
    {
        return $this->outputs()->contains(fn (GenerationOutput $output): bool => $output->status === GenerationOutput::STATUS_FAILED);
    }
}; ?>

<section class="w-full" wire:poll.5s>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Transcript Session') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('Review the saved transcript session and continue the generation flow.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="mx-auto max-w-6xl space-y-6">
        <flux:card>
            <div class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <flux:text class="font-medium">{{ __('Status') }}</flux:text>
                        <div class="mt-2">
                            <flux:badge :color="$this->sessionStatusColor" rounded>{{ $transcriptSession->status }}</flux:badge>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <flux:button :href="route('transcripts.clarify', ['transcriptSession' => $transcriptSession])" variant="ghost" wire:navigate>
                            {{ __('Edit Clarification') }}
                        </flux:button>
                    </div>
                </div>

                <div>
                    <flux:text class="font-medium">{{ __('Source') }}</flux:text>
                    <flux:text>{{ $transcriptSession->source_type }}</flux:text>
                </div>

                <div>
                    <flux:text class="font-medium">{{ __('Transcript') }}</flux:text>
                    <flux:text>{{ $transcriptSession->transcript_text }}</flux:text>
                </div>

                @if ($transcriptSession->status === \App\Models\TranscriptSession::STATUS_GENERATING)
                    <flux:callout variant="info" icon="information-circle">
                        {{ __('Generation is running in the background. This page refreshes automatically every 5 seconds.') }}
                    </flux:callout>
                @elseif ($this->hasFailedOutputs)
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        {{ __('Some outputs failed. Retry only the failed outputs or edit the clarification before regenerating.') }}
                    </flux:callout>
                @endif
            </div>
        </flux:card>

        <form wire:submit="regenerateSelectedOutputs" class="space-y-6">
            @if ($this->hasFailedOutputs)
                <div class="flex items-center gap-3">
                    <flux:button type="button" variant="subtle" wire:click="retryFailedOutputs" icon="arrow-path">
                        {{ __('Retry Failed Only') }}
                    </flux:button>
                </div>
            @endif

            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($this->outputs() as $output)
                    <flux:card>
                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <flux:heading size="lg">{{ str($output->type)->replace('_', ' ')->headline() }}</flux:heading>
                                    <div class="mt-2">
                                        <flux:badge :color="$this->outputStatusColor($output)" rounded>{{ $output->status }}</flux:badge>
                                    </div>
                                    <flux:text class="mt-2">{{ $this->outputStatusLabel($output) }}</flux:text>
                                </div>

                                <div class="flex flex-col items-end gap-3">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="selectedOutputTypes" value="{{ $output->type }}">
                                        <span>{{ __('Regenerate') }}</span>
                                    </label>

                                    @if ($output->status === \App\Models\GenerationOutput::STATUS_COMPLETED && $output->content)
                                        <div class="flex items-center gap-2">
                                            @if ($output->type === \App\Models\GenerationOutput::TYPE_HTML_PAGE)
                                                <flux:button type="button" size="sm" variant="ghost" tag="a" :href="route('transcripts.preview', ['transcriptSession' => $transcriptSession, 'type' => $output->type])" target="_blank" icon="arrow-top-right-on-square">
                                                    {{ __('Preview') }}
                                                </flux:button>
                                            @endif
                                            <flux:button type="button" size="sm" variant="ghost" wire:click="downloadPdf('{{ $output->type }}')" icon="arrow-down-tray">
                                                {{ __('Export PDF') }}
                                            </flux:button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($output->error_message)
                                <flux:callout variant="danger" icon="exclamation-triangle">
                                    {{ $output->error_message }}
                                </flux:callout>
                            @endif

                            @if ($output->type === \App\Models\GenerationOutput::TYPE_HTML_PAGE && $output->content)
                                <iframe
                                    class="h-[32rem] w-full rounded-xl border border-zinc-200"
                                    srcdoc="{{ e($output->content) }}"
                                    title="{{ $output->type }}"
                                ></iframe>
                            @elseif ($output->content)
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                    <div class="prose prose-zinc max-w-none dark:prose-invert prose-pre:hidden prose-code:before:content-none prose-code:after:content-none">
                                        {!! $this->renderedMarkdown($output) !!}
                                    </div>
                                </div>
                            @else
                                <flux:text>{{ __('No content generated yet.') }}</flux:text>
                            @endif
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                <flux:button variant="primary" type="submit" :disabled="! $this->hasSelectedOutputs">
                    {{ __('Regenerate Selected Outputs') }}
                </flux:button>

                @unless ($this->hasSelectedOutputs)
                    <flux:text>{{ __('Select at least one output to regenerate.') }}</flux:text>
                @endunless
            </div>
        </form>
    </div>
</section>
