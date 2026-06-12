<?php

use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('transcripts/create', 'pages::transcripts.create')->name('transcripts.create');
    Route::livewire('transcripts/{transcriptSession}/clarify', 'pages::transcripts.clarify')->name('transcripts.clarify');
    Route::livewire('transcripts/{transcriptSession}', 'pages::transcripts.show')->name('transcripts.show');
    Route::get('transcripts/{transcriptSession}/preview/{type}', function (TranscriptSession $transcriptSession, string $type) {
        abort_unless(request()->user()->can('view', $transcriptSession), 403);

        $output = $transcriptSession->generationOutputs()
            ->where('type', $type)
            ->where('status', GenerationOutput::STATUS_COMPLETED)
            ->firstOrFail();

        abort_unless($output->type === GenerationOutput::TYPE_HTML_PAGE, 404);
        abort_if(blank($output->content), 422);

        return response($output->content, 200, ['Content-Type' => 'text/html']);
    })->name('transcripts.preview');
});

require __DIR__.'/settings.php';
