<?php

use App\Services\Generation\MarkdownBlueprintService;

test('prd blueprint always contains fixed sections', function () {
    $markdown = app(MarkdownBlueprintService::class)->prd([
        'project_name' => 'SpecSprint',
        'project_summary' => 'Generate structured documentation.',
        'target_users' => 'Product managers',
        'goals' => ['Reduce manual documentation'],
        'key_features' => ['Transcript intake'],
    ], '- Functional body');

    expect($markdown)->toContain('# Product Requirements Document');
    expect($markdown)->toContain('## Product Overview');
    expect($markdown)->toContain('## Target Users');
    expect($markdown)->toContain('## Goals');
    expect($markdown)->toContain('## Key Features');
    expect($markdown)->toContain('## Functional Scope');
    expect($markdown)->toContain('## Success Metrics');
    expect($markdown)->toContain('## Risks');
});

test('user stories blueprint always contains fixed sections', function () {
    $markdown = app(MarkdownBlueprintService::class)->userStories([
        'project_name' => 'SpecSprint',
        'target_users' => 'Product managers',
        'goals' => ['Reduce manual documentation'],
    ], '- As a PM, I want faster specs');

    expect($markdown)->toContain('# User Stories');
    expect($markdown)->toContain('## Product Context');
    expect($markdown)->toContain('## User Goals');
    expect($markdown)->toContain('## Stories');
    expect($markdown)->toContain('## Acceptance Criteria');
});

test('functional requirements blueprint always contains fixed sections', function () {
    $markdown = app(MarkdownBlueprintService::class)->functionalRequirements([
        'project_name' => 'SpecSprint',
        'key_features' => ['Transcript intake'],
    ], '- The system shall validate transcript input.');

    expect($markdown)->toContain('# Functional Requirements');
    expect($markdown)->toContain('## Product Context');
    expect($markdown)->toContain('## Key Features');
    expect($markdown)->toContain('## Requirements by Module');
    expect($markdown)->toContain('## Constraints');
});
