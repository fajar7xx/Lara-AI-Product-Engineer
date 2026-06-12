<?php

use App\Ai\Agents\TranscriptExtractionAgent;
use App\Jobs\GenerateSessionOutputs;
use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Livewire\Livewire;

test('successful extract redirects to the clarification page', function () {
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

    $this->actingAs(User::factory()->create());

    Livewire::test('pages::transcripts.create')
        ->set('transcript', 'We are building a product for product managers and founders. The app should turn transcripts into structured documentation, recommend a dashboard-ready shell, and capture the team goals and feature requirements for a cleaner planning workflow.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('transcripts.clarify', ['transcriptSession' => TranscriptSession::first()]));
});

test('provider overload during extraction shows a user facing retry message', function () {
    TranscriptExtractionAgent::fake(function (): never {
        throw ProviderOverloadedException::forProvider('gemini');
    })->preventStrayPrompts();

    $this->actingAs(User::factory()->create());

    Livewire::test('pages::transcripts.create')
        ->set('transcript', 'We are building a product for product managers and founders. The app should turn transcripts into structured documentation, recommend a dashboard-ready shell, and capture the team goals and feature requirements for a cleaner planning workflow.')
        ->call('save')
        ->assertHasErrors(['transcript'])
        ->assertSee('Gemini is temporarily overloaded. Please try again in a minute.');
});

test('clarification page shows extracted defaults and recommendations', function () {
    $user = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($user)->create([
        'status' => 'clarifying',
        'project_name' => 'SpecSprint',
        'project_summary' => 'Turn transcripts into planning artifacts.',
        'target_users' => 'Product managers and founders',
        'goals' => ['Generate structured product docs quickly'],
        'key_features' => ['Transcript extraction', 'Clarification workflow'],
        'template_family' => 'app_shell',
        'design_system' => 'modern',
        'layout_recommendations' => ['landing', 'app_shell'],
        'design_system_recommendations' => ['minimal', 'modern', 'corporate'],
    ]);

    $this->actingAs($user)
        ->get(route('transcripts.clarify', ['transcriptSession' => $transcriptSession]))
        ->assertOk()
        ->assertSee('SpecSprint')
        ->assertSee('landing')
        ->assertSee('app_shell')
        ->assertSee('modern');
});

test('user may edit context fields and keep clarification status', function () {
    $user = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($user)->create([
        'status' => 'clarifying',
        'project_name' => 'SpecSprint',
        'project_summary' => 'Turn transcripts into planning artifacts.',
        'target_users' => 'Product managers and founders',
        'goals' => ['Generate structured product docs quickly'],
        'key_features' => ['Transcript extraction', 'Clarification workflow'],
        'template_family' => 'app_shell',
        'design_system' => 'modern',
        'layout_recommendations' => ['landing', 'app_shell'],
        'design_system_recommendations' => ['minimal', 'modern', 'corporate'],
    ]);

    foreach (GenerationOutput::supportedTypes() as $type) {
        $transcriptSession->generationOutputs()->create([
            'type' => $type,
            'status' => GenerationOutput::STATUS_COMPLETED,
            'content' => 'existing content',
        ]);
    }

    $this->actingAs($user);

    Livewire::test('pages::transcripts.clarify', ['transcriptSession' => $transcriptSession])
        ->set('projectName', 'SpecSprint Plus')
        ->set('projectSummary', 'Updated summary')
        ->set('targetUsers', 'PMs and startup founders')
        ->set('goals', ['Capture requirements faster'])
        ->set('keyFeatures', ['Editable clarification form'])
        ->set('templateFamily', 'landing')
        ->set('designSystem', 'corporate')
        ->call('save')
        ->assertHasNoErrors();

    $transcriptSession->refresh();

    expect($transcriptSession->project_name)->toBe('SpecSprint Plus');
    expect($transcriptSession->project_summary)->toBe('Updated summary');
    expect($transcriptSession->target_users)->toBe('PMs and startup founders');
    expect($transcriptSession->goals)->toBe(['Capture requirements faster']);
    expect($transcriptSession->key_features)->toBe(['Editable clarification form']);
    expect($transcriptSession->template_family)->toBe('landing');
    expect($transcriptSession->design_system)->toBe('corporate');
    expect($transcriptSession->status)->toBe('clarifying');
});

test('template family and design system must stay inside ai offered options', function () {
    $user = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($user)->create([
        'status' => 'clarifying',
        'project_name' => 'SpecSprint',
        'project_summary' => 'Turn transcripts into planning artifacts.',
        'target_users' => 'Product managers and founders',
        'goals' => ['Generate structured product docs quickly'],
        'key_features' => ['Transcript extraction', 'Clarification workflow'],
        'template_family' => 'app_shell',
        'design_system' => 'modern',
        'layout_recommendations' => ['landing', 'app_shell'],
        'design_system_recommendations' => ['minimal', 'modern', 'corporate'],
    ]);

    $this->actingAs($user);

    Livewire::test('pages::transcripts.clarify', ['transcriptSession' => $transcriptSession])
        ->set('templateFamily', 'admin_portal')
        ->set('designSystem', 'playful')
        ->call('save')
        ->assertHasErrors(['templateFamily', 'designSystem']);
});

test('first clarification save dispatches generation and redirects to the result page', function () {
    Queue::fake();

    $user = User::factory()->create();
    $transcriptSession = TranscriptSession::factory()->for($user)->create([
        'status' => 'clarifying',
        'project_name' => 'SpecSprint',
        'project_summary' => 'Turn transcripts into planning artifacts.',
        'target_users' => 'Product managers and founders',
        'goals' => ['Generate structured product docs quickly'],
        'key_features' => ['Transcript extraction', 'Clarification workflow'],
        'template_family' => 'app_shell',
        'design_system' => 'modern',
        'layout_recommendations' => ['landing', 'app_shell'],
        'design_system_recommendations' => ['minimal', 'modern', 'corporate'],
    ]);

    $this->actingAs($user);

    Livewire::test('pages::transcripts.clarify', ['transcriptSession' => $transcriptSession])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('transcripts.show', ['transcriptSession' => $transcriptSession]));

    Queue::assertPushed(GenerateSessionOutputs::class);
});
