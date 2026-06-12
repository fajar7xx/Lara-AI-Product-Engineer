<?php

use App\Models\TranscriptSession;
use App\Jobs\GenerateSessionOutputs;
use App\Models\GenerationOutput;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Clarify transcript session')] class extends Component {
    use AuthorizesRequests;

    public TranscriptSession $transcriptSession;
    public string $projectName = '';
    public string $projectSummary = '';
    public string $targetUsers = '';
    /** @var array<int, string> */
    public array $goals = [];
    /** @var array<int, string> */
    public array $keyFeatures = [];
    public string $templateFamily = '';
    public string $designSystem = '';

    public function mount(TranscriptSession $transcriptSession): void
    {
        $this->authorize('update', $transcriptSession);

        $this->projectName = $transcriptSession->project_name ?? '';
        $this->projectSummary = $transcriptSession->project_summary ?? '';
        $this->targetUsers = $transcriptSession->target_users ?? '';
        $this->goals = $transcriptSession->goals ?? [];
        $this->keyFeatures = $transcriptSession->key_features ?? [];
        $this->templateFamily = $transcriptSession->template_family ?? '';
        $this->designSystem = $transcriptSession->design_system ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'projectName' => ['required', 'string'],
            'projectSummary' => ['required', 'string'],
            'targetUsers' => ['required', 'string'],
            'goals' => ['array', 'min:1'],
            'goals.*' => ['required', 'string'],
            'keyFeatures' => ['array', 'min:1'],
            'keyFeatures.*' => ['required', 'string'],
            'templateFamily' => ['required', 'string', 'in:'.implode(',', $this->transcriptSession->layout_recommendations ?? [])],
            'designSystem' => ['required', 'string', 'in:'.implode(',', $this->transcriptSession->design_system_recommendations ?? [])],
        ]);

        $this->transcriptSession->forceFill([
            'project_name' => $validated['projectName'],
            'project_summary' => $validated['projectSummary'],
            'target_users' => $validated['targetUsers'],
            'goals' => array_values(array_filter($validated['goals'])),
            'key_features' => array_values(array_filter($validated['keyFeatures'])),
            'template_family' => $validated['templateFamily'],
            'design_system' => $validated['designSystem'],
            'status' => TranscriptSession::STATUS_CLARIFYING,
        ])->save();

        if (! $this->transcriptSession->generationOutputs()->exists()) {
            foreach (GenerationOutput::supportedTypes() as $type) {
                $this->transcriptSession->generationOutputs()->firstOrCreate(
                    ['type' => $type],
                    ['status' => GenerationOutput::STATUS_PENDING]
                );
            }

            GenerateSessionOutputs::dispatch($this->transcriptSession->id);

            Flux::toast(variant: 'success', text: __('Generation started.'));
        } else {
            Flux::toast(variant: 'success', text: __('Clarification updated.'));
        }

        $this->redirectRoute('transcripts.show', ['transcriptSession' => $this->transcriptSession]);
    }

    public function addGoal(): void
    {
        $this->goals[] = '';
    }

    public function addFeature(): void
    {
        $this->keyFeatures[] = '';
    }
}; ?>

<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Clarification') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('This page will host the guided clarification form after extraction is implemented.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="mx-auto max-w-4xl space-y-6">
        <form wire:submit="save" class="space-y-6">
            <flux:card>
                <div class="space-y-4">
                    <flux:heading size="lg">{{ __('Project Context') }}</flux:heading>

                    <flux:input wire:model="projectName" :label="__('Project name')" />
                    <flux:textarea wire:model="projectSummary" :label="__('Project summary')" rows="4" />
                    <flux:textarea wire:model="targetUsers" :label="__('Target users')" rows="3" />
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <flux:heading size="lg">{{ __('Goals') }}</flux:heading>
                        <flux:button type="button" variant="ghost" wire:click="addGoal">
                            {{ __('Add goal') }}
                        </flux:button>
                    </div>

                    @foreach ($goals as $index => $goal)
                        <flux:input wire:model="goals.{{ $index }}" :label="__('Goal').' '.($index + 1)" />
                    @endforeach
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <flux:heading size="lg">{{ __('Key Features') }}</flux:heading>
                        <flux:button type="button" variant="ghost" wire:click="addFeature">
                            {{ __('Add feature') }}
                        </flux:button>
                    </div>

                    @foreach ($keyFeatures as $index => $feature)
                        <flux:input wire:model="keyFeatures.{{ $index }}" :label="__('Feature').' '.($index + 1)" />
                    @endforeach
                </div>
            </flux:card>

            <flux:card>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <flux:heading size="lg">{{ __('Template Family') }}</flux:heading>

                        @foreach ($transcriptSession->layout_recommendations ?? [] as $option)
                            <flux:radio.group wire:model="templateFamily">
                                <flux:radio :value="$option">{{ $option }}</flux:radio>
                            </flux:radio.group>
                        @endforeach
                    </div>

                    <div class="space-y-4">
                        <flux:heading size="lg">{{ __('Design System') }}</flux:heading>

                        @foreach ($transcriptSession->design_system_recommendations ?? [] as $option)
                            <flux:radio.group wire:model="designSystem">
                                <flux:radio :value="$option">{{ $option }}</flux:radio>
                            </flux:radio.group>
                        @endforeach
                    </div>
                </div>
            </flux:card>

            <div class="flex items-center gap-3">
                <flux:button variant="primary" type="submit">
                    {{ $transcriptSession->generationOutputs()->exists() ? __('Save Clarification') : __('Save and Generate') }}
                </flux:button>
            </div>
        </form>
    </div>
</section>
