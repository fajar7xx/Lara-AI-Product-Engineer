<?php

use App\Services\Generation\HtmlAssemblyService;

test('landing template renders expected section skeleton', function () {
    $html = app(HtmlAssemblyService::class)->render('landing', 'minimal', [
        'title' => 'SpecSprint',
        'tagline' => 'Turn transcripts into specs.',
        'sections' => [
            ['heading' => 'Fast Output', 'body' => 'Generate docs quickly.'],
        ],
    ]);

    expect($html)->toContain('SpecSprint');
    expect($html)->toContain('Fast Output');
    expect($html)->toContain('Get Started');
    expect($html)->toContain('Features');
});

test('app shell template renders expected shell structure', function () {
    $html = app(HtmlAssemblyService::class)->render('app_shell', 'modern', [
        'title' => 'SpecSprint Workspace',
        'tagline' => 'Track generated assets.',
        'sections' => [
            ['heading' => 'Recent Outputs', 'body' => 'See generated artifacts.'],
        ],
    ]);

    expect($html)->toContain('SpecSprint Workspace');
    expect($html)->toContain('Modules');
    expect($html)->toContain('Recent Outputs');
});

test('design system changes token output predictably', function () {
    $minimal = app(HtmlAssemblyService::class)->render('landing', 'minimal', [
        'title' => 'Minimal',
        'sections' => [],
    ]);

    $modern = app(HtmlAssemblyService::class)->render('landing', 'modern', [
        'title' => 'Modern',
        'sections' => [],
    ]);

    expect($minimal)->toContain('bg-white text-zinc-900');
    expect($modern)->toContain('bg-slate-950 text-white');
});
