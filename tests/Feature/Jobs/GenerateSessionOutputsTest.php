<?php

use App\Ai\Agents\FunctionalRequirementsGenerationAgent;
use App\Ai\Agents\HtmlPageContentAgent;
use App\Ai\Agents\PrdGenerationAgent;
use App\Ai\Agents\UserStoriesGenerationAgent;
use App\Jobs\GenerateSessionOutputs;
use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use App\Models\User;
use App\Services\Generation\SessionGenerationService;
use Laravel\Ai\Exceptions\ProviderOverloadedException;

test('generation job updates outputs in order and completes session when all succeed', function () {
    PrdGenerationAgent::fake([['content' => "```md\n# Wrong Title\n- PRD body\n```"]])->preventStrayPrompts();
    UserStoriesGenerationAgent::fake([['content' => "```md\n# Stories\n- Story body\n```"]])->preventStrayPrompts();
    FunctionalRequirementsGenerationAgent::fake([['content' => "```md\n# Requirements\n- Requirement body\n```"]])->preventStrayPrompts();
    HtmlPageContentAgent::fake([[
        'title' => 'SpecSprint',
        'tagline' => '<b>Fast</b> product planning.',
        'sections' => [
            ['heading' => '<b>Overview</b>', 'body' => 'Generated <script>alert(1)</script> page body.'],
        ],
    ]])->preventStrayPrompts();

    $transcriptSession = preparedTranscriptSession();

    app(SessionGenerationService::class)->ensureOutputs($transcriptSession);

    app()->call([new GenerateSessionOutputs($transcriptSession->id), 'handle']);

    $transcriptSession->refresh();

    expect($transcriptSession->status)->toBe(TranscriptSession::STATUS_COMPLETED);
    expect($transcriptSession->generationOutputs()->where('status', GenerationOutput::STATUS_COMPLETED)->count())->toBe(4);
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_PRD)->first()?->content)->not->toContain('```');
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_PRD)->first()?->content)->not->toContain('# Wrong Title');
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_HTML_PAGE)->first()?->content)->toContain('SpecSprint');
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_HTML_PAGE)->first()?->content)->not->toContain('<script>');
});

test('generation continues after one output fails and marks the session partial', function () {
    PrdGenerationAgent::fake(function (): never {
        throw new RuntimeException('PRD generation failed.');
    })->preventStrayPrompts();
    UserStoriesGenerationAgent::fake([['content' => '- Story body']])->preventStrayPrompts();
    FunctionalRequirementsGenerationAgent::fake([['content' => '- Requirement body']])->preventStrayPrompts();
    HtmlPageContentAgent::fake([[
        'title' => 'SpecSprint',
        'tagline' => 'Fast product planning.',
        'sections' => [
            ['heading' => 'Overview', 'body' => 'Generated page body.'],
        ],
    ]])->preventStrayPrompts();

    $transcriptSession = preparedTranscriptSession();

    app(SessionGenerationService::class)->ensureOutputs($transcriptSession);

    app()->call([new GenerateSessionOutputs($transcriptSession->id), 'handle']);

    $transcriptSession->refresh();

    expect($transcriptSession->status)->toBe(TranscriptSession::STATUS_PARTIAL);
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_PRD)->first()?->status)->toBe(GenerationOutput::STATUS_FAILED);
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_USER_STORIES)->first()?->status)->toBe(GenerationOutput::STATUS_COMPLETED);
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_HTML_PAGE)->first()?->status)->toBe(GenerationOutput::STATUS_COMPLETED);
});

test('provider overload triggers retry path and resumes from pending outputs', function () {
    PrdGenerationAgent::fake([['content' => '- PRD body']])->preventStrayPrompts();
    UserStoriesGenerationAgent::fake(function (): never {
        throw ProviderOverloadedException::forProvider('gemini');
    })->preventStrayPrompts();
    FunctionalRequirementsGenerationAgent::fake([['content' => '- Requirement body']])->preventStrayPrompts();
    HtmlPageContentAgent::fake([[
        'title' => 'SpecSprint',
        'tagline' => 'Fast product planning.',
        'sections' => [
            ['heading' => 'Overview', 'body' => 'Generated page body.'],
        ],
    ]])->preventStrayPrompts();

    $transcriptSession = preparedTranscriptSession();

    app(SessionGenerationService::class)->ensureOutputs($transcriptSession);

    expect(fn () => app()->call([new GenerateSessionOutputs($transcriptSession->id), 'handle']))
        ->toThrow(ProviderOverloadedException::class);

    $transcriptSession->refresh();

    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_PRD)->first()?->status)
        ->toBe(GenerationOutput::STATUS_COMPLETED);
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_USER_STORIES)->first()?->status)
        ->toBe(GenerationOutput::STATUS_PENDING);
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_USER_STORIES)->first()?->error_message)
        ->toContain('Temporary AI provider issue');
    expect($transcriptSession->generationOutputs()->where('type', GenerationOutput::TYPE_HTML_PAGE)->first()?->status)
        ->toBe(GenerationOutput::STATUS_PENDING);

    UserStoriesGenerationAgent::fake([['content' => '- Story body']])->preventStrayPrompts();
    FunctionalRequirementsGenerationAgent::fake([['content' => '- Requirement body']])->preventStrayPrompts();
    HtmlPageContentAgent::fake([[
        'title' => 'SpecSprint',
        'tagline' => 'Fast product planning.',
        'sections' => [
            ['heading' => 'Overview', 'body' => 'Generated page body.'],
        ],
    ]])->preventStrayPrompts();

    app()->call([new GenerateSessionOutputs($transcriptSession->id), 'handle']);

    $transcriptSession->refresh();

    expect($transcriptSession->status)->toBe(TranscriptSession::STATUS_COMPLETED);
    expect($transcriptSession->generationOutputs()->where('status', GenerationOutput::STATUS_COMPLETED)->count())->toBe(4);
});

function preparedTranscriptSession(): TranscriptSession
{
    return TranscriptSession::factory()
        ->for(User::factory())
        ->create([
            'status' => TranscriptSession::STATUS_CLARIFYING,
            'project_name' => 'SpecSprint',
            'project_summary' => 'Turn transcripts into planning artifacts.',
            'target_users' => 'Product managers and founders',
            'goals' => ['Generate docs quickly'],
            'key_features' => ['Transcript extraction', 'Clarification workflow'],
            'template_family' => 'app_shell',
            'design_system' => 'modern',
            'layout_recommendations' => ['landing', 'app_shell'],
            'design_system_recommendations' => ['minimal', 'modern', 'corporate'],
        ]);
}
