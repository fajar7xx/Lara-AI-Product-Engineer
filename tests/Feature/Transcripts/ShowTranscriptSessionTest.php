<?php

use App\Jobs\GenerateSessionOutputs;
use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use App\Models\User;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('guests are redirected to the login page for transcript sessions', function () {
    $transcriptSession = TranscriptSession::factory()->create();

    $this->get(route('transcripts.show', ['transcriptSession' => $transcriptSession]))
        ->assertRedirect(route('login'));
});

test('owners can open their transcript session', function () {
    $user = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('transcripts.show', ['transcriptSession' => $transcriptSession]))
        ->assertOk()
        ->assertSee('Transcript Session');
});

test('other authenticated users cannot open a transcript session they do not own', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('transcripts.show', ['transcriptSession' => $transcriptSession]))
        ->assertForbidden();
});

test('owner sees per output statuses on the result page', function () {
    $user = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($user)->create([
        'status' => TranscriptSession::STATUS_PARTIAL,
    ]);

    $transcriptSession->generationOutputs()->createMany([
        ['type' => GenerationOutput::TYPE_PRD, 'status' => GenerationOutput::STATUS_COMPLETED, 'content' => '# PRD'],
        ['type' => GenerationOutput::TYPE_USER_STORIES, 'status' => GenerationOutput::STATUS_FAILED, 'error_message' => 'Failed story generation'],
        ['type' => GenerationOutput::TYPE_FUNCTIONAL_REQUIREMENTS, 'status' => GenerationOutput::STATUS_PENDING],
        ['type' => GenerationOutput::TYPE_HTML_PAGE, 'status' => GenerationOutput::STATUS_COMPLETED, 'content' => '<html><body>Page</body></html>'],
    ]);

    $this->actingAs($user)
        ->get(route('transcripts.show', ['transcriptSession' => $transcriptSession]))
        ->assertOk()
        ->assertSee('Prd')
        ->assertSee('User Stories')
        ->assertSee('Functional Requirements')
        ->assertSee('Html Page')
        ->assertSee('Generation failed. You can retry this output.')
        ->assertSee('Retry Failed Only')
        ->assertSee('Export PDF');
});

test('owner can regenerate selected outputs without resetting untouched ones', function () {
    Queue::fake();

    $user = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($user)->create([
        'status' => TranscriptSession::STATUS_PARTIAL,
        'project_name' => 'SpecSprint',
        'project_summary' => 'Turn transcripts into planning artifacts.',
        'target_users' => 'Product managers and founders',
        'goals' => ['Generate docs quickly'],
        'key_features' => ['Transcript extraction'],
        'template_family' => 'app_shell',
        'design_system' => 'modern',
    ]);

    $transcriptSession->generationOutputs()->createMany([
        ['type' => GenerationOutput::TYPE_PRD, 'status' => GenerationOutput::STATUS_COMPLETED, 'content' => '# PRD', 'revision_number' => 1],
        ['type' => GenerationOutput::TYPE_USER_STORIES, 'status' => GenerationOutput::STATUS_FAILED, 'error_message' => 'Failed', 'revision_number' => 1],
        ['type' => GenerationOutput::TYPE_FUNCTIONAL_REQUIREMENTS, 'status' => GenerationOutput::STATUS_COMPLETED, 'content' => '# Requirements', 'revision_number' => 1],
        ['type' => GenerationOutput::TYPE_HTML_PAGE, 'status' => GenerationOutput::STATUS_COMPLETED, 'content' => '<html></html>', 'revision_number' => 1],
    ]);

    $this->actingAs($user);

    Livewire::test('pages::transcripts.show', ['transcriptSession' => $transcriptSession])
        ->set('selectedOutputTypes', [GenerationOutput::TYPE_USER_STORIES])
        ->call('regenerateSelectedOutputs')
        ->assertHasNoErrors();

    $userStories = $transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_USER_STORIES)->first();
    $prd = $transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_PRD)->first();

    expect($userStories?->status)->toBe(GenerationOutput::STATUS_PENDING);
    expect($userStories?->revision_number)->toBe(2);
    expect($prd?->status)->toBe(GenerationOutput::STATUS_COMPLETED);
    expect($prd?->revision_number)->toBe(1);

    Queue::assertPushed(GenerateSessionOutputs::class, function (GenerateSessionOutputs $job) use ($transcriptSession): bool {
        return $job->transcriptSessionId === $transcriptSession->id
            && $job->targetTypes === [GenerationOutput::TYPE_USER_STORIES];
    });
});

test('owner can retry failed outputs only', function () {
    Queue::fake();

    $user = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($user)->create([
        'status' => TranscriptSession::STATUS_PARTIAL,
        'project_name' => 'SpecSprint',
        'project_summary' => 'Turn transcripts into planning artifacts.',
        'target_users' => 'Product managers and founders',
        'goals' => ['Generate docs quickly'],
        'key_features' => ['Transcript extraction'],
        'template_family' => 'app_shell',
        'design_system' => 'modern',
    ]);

    $transcriptSession->generationOutputs()->createMany([
        ['type' => GenerationOutput::TYPE_PRD, 'status' => GenerationOutput::STATUS_COMPLETED, 'content' => '# PRD', 'revision_number' => 1],
        ['type' => GenerationOutput::TYPE_USER_STORIES, 'status' => GenerationOutput::STATUS_FAILED, 'error_message' => 'Failed stories', 'revision_number' => 1],
        ['type' => GenerationOutput::TYPE_FUNCTIONAL_REQUIREMENTS, 'status' => GenerationOutput::STATUS_FAILED, 'error_message' => 'Failed requirements', 'revision_number' => 1],
        ['type' => GenerationOutput::TYPE_HTML_PAGE, 'status' => GenerationOutput::STATUS_COMPLETED, 'content' => '<html></html>', 'revision_number' => 1],
    ]);

    $this->actingAs($user);

    Livewire::test('pages::transcripts.show', ['transcriptSession' => $transcriptSession])
        ->call('retryFailedOutputs')
        ->assertHasNoErrors();

    $userStories = $transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_USER_STORIES)->first();
    $requirements = $transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_FUNCTIONAL_REQUIREMENTS)->first();
    $prd = $transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_PRD)->first();

    expect($userStories?->status)->toBe(GenerationOutput::STATUS_PENDING);
    expect($userStories?->revision_number)->toBe(2);
    expect($requirements?->status)->toBe(GenerationOutput::STATUS_PENDING);
    expect($requirements?->revision_number)->toBe(2);
    expect($prd?->status)->toBe(GenerationOutput::STATUS_COMPLETED);
    expect($prd?->revision_number)->toBe(1);

    Queue::assertPushed(GenerateSessionOutputs::class, function (GenerateSessionOutputs $job) use ($transcriptSession): bool {
        return $job->transcriptSessionId === $transcriptSession->id
            && $job->targetTypes === [
                GenerationOutput::TYPE_USER_STORIES,
                GenerationOutput::TYPE_FUNCTIONAL_REQUIREMENTS,
            ];
    });
});

test('owner can export a completed output as pdf', function () {
    Process::fake(function (PendingProcess $process): ProcessResult {
        foreach ($process->command as $argument) {
            if (is_string($argument) && str_starts_with($argument, '--print-to-pdf=')) {
                file_put_contents(str_replace('--print-to-pdf=', '', $argument), 'fake-pdf-content');
            }
        }

        return Process::result();
    });

    $user = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($user)->create([
        'project_name' => 'SpecSprint',
        'project_summary' => 'Turn transcripts into planning artifacts.',
        'status' => TranscriptSession::STATUS_COMPLETED,
    ]);

    $transcriptSession->generationOutputs()->create([
        'type' => GenerationOutput::TYPE_PRD,
        'status' => GenerationOutput::STATUS_COMPLETED,
        'content' => '# PRD',
    ]);

    $this->actingAs($user);

    Livewire::test('pages::transcripts.show', ['transcriptSession' => $transcriptSession])
        ->call('downloadPdf', GenerationOutput::TYPE_PRD)
        ->assertFileDownloaded('specsprint-prd.pdf');
});
