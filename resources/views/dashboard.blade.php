<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <flux:card class="max-w-4xl">
            <div class="space-y-4">
                <div class="space-y-2">
                    <flux:heading size="xl">{{ __('AI PRD UI Generator') }}</flux:heading>
                    <flux:text>
                        {{ __('Create a transcript session, validate the meeting content, and prepare the AI-assisted documentation workflow.') }}
                    </flux:text>
                </div>

                <div class="flex items-center gap-3">
                    <flux:button :href="route('transcripts.create')" variant="primary" wire:navigate>
                        {{ __('Start New Transcript Session') }}
                    </flux:button>
                </div>
            </div>
        </flux:card>
    </div>
</x-layouts::app>
