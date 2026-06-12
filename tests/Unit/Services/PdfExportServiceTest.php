<?php

use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use App\Models\User;
use App\Services\Exports\PdfExportService;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;

test('pdf export service renders a pdf file through chromium', function () {
    Process::fake(function (PendingProcess $process): ProcessResult {
        foreach ($process->command as $argument) {
            if (is_string($argument) && str_starts_with($argument, '--print-to-pdf=')) {
                file_put_contents(str_replace('--print-to-pdf=', '', $argument), 'fake-pdf-content');
            }
        }

        return Process::result();
    });

    $transcriptSession = TranscriptSession::factory()->for(User::factory())->create([
        'project_name' => 'SpecSprint',
        'project_summary' => 'Turn transcripts into planning artifacts.',
    ]);

    $output = $transcriptSession->generationOutputs()->create([
        'type' => GenerationOutput::TYPE_PRD,
        'status' => GenerationOutput::STATUS_COMPLETED,
        'content' => '# PRD',
    ]);

    $export = app(PdfExportService::class)->exportTranscriptOutput($transcriptSession, $output);

    expect($export['filename'])->toBe('specsprint-prd.pdf');
    expect(file_exists($export['path']))->toBeTrue();

    Process::assertRan(function (PendingProcess $process): bool {
        return is_array($process->command)
            && $process->command[0] === '/usr/bin/chromium-browser';
    });

    @unlink($export['path']);
});
