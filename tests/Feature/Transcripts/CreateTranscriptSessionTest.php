<?php

use App\Ai\Agents\TranscriptExtractionAgent;
use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function fakeTranscriptExtraction(): void
{
    TranscriptExtractionAgent::fake([
        [
            'project_name' => 'SpecSprint',
            'project_summary' => 'Turn transcripts into planning artifacts.',
            'target_users' => 'Product managers and founders',
            'goals' => ['Generate structured product docs quickly'],
            'key_features' => ['Transcript extraction', 'Clarification workflow'],
            'template_family_recommendation' => 'app_shell',
            'template_family_options' => ['landing', 'app_shell'],
            'design_system_recommendation' => 'modern',
            'design_system_options' => ['minimal', 'modern', 'corporate'],
        ],
    ])->preventStrayPrompts();
}

test('authenticated users can view the transcript intake page', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('transcripts.create'))->assertOk();
});

test('authenticated users can create a transcript session draft', function () {
    fakeTranscriptExtraction();

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::transcripts.create')
        ->set('transcript', 'We are designing a product for product managers who need faster documentation. The app should capture user goals, project requirements, and key features so the team can create a dashboard-ready plan with a clearer workflow and a better internal process.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('transcripts.clarify', ['transcriptSession' => TranscriptSession::first()]));

    $transcriptSession = TranscriptSession::query()->first();

    expect($transcriptSession)->not->toBeNull();
    expect($transcriptSession->user_id)->toBe($user->id);
    expect($transcriptSession->status)->toBe('clarifying');
    expect(GenerationOutput::query()->count())->toBe(0);
});

test('invalid transcript shows validation feedback', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::transcripts.create')
        ->set('transcript', 'too short')
        ->call('save')
        ->assertHasErrors(['transcript']);
});

test('txt upload is accepted', function () {
    fakeTranscriptExtraction();

    Storage::fake('local');

    $this->actingAs(User::factory()->create());

    $file = UploadedFile::fake()->createWithContent('transcript.txt', 'We are building a product app for internal teams. Users need a better dashboard, clearer goals, and improved feature planning so the team can create a landing page and a reusable workflow from a long transcript.');

    Livewire::test('pages::transcripts.create')
        ->set('transcriptFile', $file)
        ->call('save')
        ->assertHasNoErrors();

    expect(TranscriptSession::query()->first()?->source_type)->toBe('txt_upload');
});

test('non txt upload is rejected', function () {
    $this->actingAs(User::factory()->create());

    $file = UploadedFile::fake()->create('transcript.pdf', 10, 'application/pdf');

    Livewire::test('pages::transcripts.create')
        ->set('transcriptFile', $file)
        ->call('save')
        ->assertHasErrors(['transcriptFile']);
});
