<?php

use App\Services\Ai\TextProviderResolver;

test('resolver returns gemini only when no openai key is configured', function () {
    config()->set('ai.providers.gemini.key', 'gemini-key');
    config()->set('ai.providers.gemini.models.text.default', 'gemini-3.5-flash');
    config()->set('ai.providers.openai.key', null);

    $chain = app(TextProviderResolver::class)->providerChain();

    expect($chain)->toBe([
        'gemini' => 'gemini-3.5-flash',
    ]);
});

test('resolver appends openai when openai key is configured', function () {
    config()->set('ai.providers.gemini.key', 'gemini-key');
    config()->set('ai.providers.gemini.models.text.default', 'gemini-3.5-flash');
    config()->set('ai.providers.openai.key', 'openai-key');
    config()->set('ai.providers.openai.models.text.default', 'gpt-4.1-mini');

    $chain = app(TextProviderResolver::class)->providerChain();

    expect($chain)->toBe([
        'gemini' => 'gemini-3.5-flash',
        'openai' => 'gpt-4.1-mini',
    ]);
});
