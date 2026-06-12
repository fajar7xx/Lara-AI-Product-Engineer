<?php

use App\Models\TranscriptSession;
use App\Services\Transcripts\TranscriptValidationService;
use App\Services\Transcripts\TranscriptExtractionService;
use Flux\Flux;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Title('Create transcript session')] class extends Component {
    use WithFileUploads;

    public string $transcript = '';

    public ?UploadedFile $transcriptFile = null;

    public function save(): void
    {
        $this->validate([
            'transcript' => ['nullable', 'string'],
            'transcriptFile' => ['nullable', 'file', 'extensions:txt', 'max:5120'],
        ]);

        $transcriptText = trim($this->transcript);

        if ($this->transcriptFile instanceof UploadedFile) {
            $transcriptText = trim(file_get_contents($this->transcriptFile->getRealPath()) ?: '');
        }

        $validation = app(TranscriptValidationService::class)->validate($transcriptText);

        if (! $validation['valid']) {
            foreach ($validation['errors'] as $error) {
                $this->addError('transcript', $error);
            }

            return;
        }

        $transcriptSession = TranscriptSession::create([
            'user_id' => Auth::id(),
            'source_type' => $this->transcriptFile instanceof UploadedFile ? 'txt_upload' : 'paste',
            'transcript_text' => $transcriptText,
            'status' => 'draft',
        ]);

        app(TranscriptExtractionService::class)->extractAndPersist($transcriptSession);

        Flux::toast(variant: 'success', text: __('Transcript extracted. Review the clarification details.'));

        $this->redirectRoute('transcripts.clarify', ['transcriptSession' => $transcriptSession]);
    }
}; ?>

<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('New Transcript Session') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('Paste a transcript or upload a .txt file to begin generating product documentation.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="mx-auto max-w-4xl">
        <form wire:submit="save" class="space-y-6">
            <flux:textarea
                wire:model="transcript"
                :label="__('Transcript')"
                rows="12"
                :placeholder="__('Paste a meeting transcript that clearly discusses users, goals, and features.')"
            />

            <flux:input wire:model="transcriptFile" :label="__('Transcript file (.txt only)')" type="file" accept=".txt,text/plain" />

            <div class="flex items-center gap-3">
                <flux:button variant="primary" type="submit">
                    {{ __('Analyze Transcript') }}
                </flux:button>
            </div>
        </form>
    </div>
</section>
