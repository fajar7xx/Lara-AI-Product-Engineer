<?php

use App\Models\TranscriptSession;
use App\Services\Generation\PromptBuilder;

test('prd prompt includes structural constraints and shared context', function () {
    $transcriptSession = TranscriptSession::factory()->make([
        'project_name' => 'SpecSprint',
        'project_summary' => 'Generate product planning docs.',
        'target_users' => 'Product managers',
        'goals' => ['Reduce manual documentation'],
        'key_features' => ['Transcript intake'],
        'template_family' => 'app_shell',
        'design_system' => 'modern',
    ]);

    $prompt = app(PromptBuilder::class)->buildPrdPrompt($transcriptSession);

    expect($prompt)->toContain('Do not write the top-level title.');
    expect($prompt)->toContain('Do not wrap the result in code fences.');
    expect($prompt)->toContain('Project: SpecSprint');
    expect($prompt)->toContain('Template family: app_shell');
    expect($prompt)->toContain('Design system: modern');
});

test('html page prompt constrains output to structured content only', function () {
    $transcriptSession = TranscriptSession::factory()->make([
        'project_name' => 'SpecSprint',
        'project_summary' => 'Generate product planning docs.',
        'target_users' => 'Product managers',
        'goals' => ['Reduce manual documentation'],
        'key_features' => ['Transcript intake'],
        'template_family' => 'landing',
        'design_system' => 'corporate',
    ]);

    $prompt = app(PromptBuilder::class)->buildHtmlPagePrompt($transcriptSession);

    expect($prompt)->toContain('Do not output HTML.');
    expect($prompt)->toContain('Sections: 3 to 5 sections.');
    expect($prompt)->toContain('single-page landing');
    expect($prompt)->toContain('Design system: corporate');
});
