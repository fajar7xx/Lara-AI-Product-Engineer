<?php

use App\Services\Ai\TextProviderResolver;

test('resolver returns openrouter only when no gemini key is configured', function () {
    config()->set('ai.providers.openrouter.key', 'or-key');
    config()->set('ai.providers.openrouter.models.text.default', 'deepseek/deepseek-v4-flash');
    config()->set('ai.providers.gemini.key', null);

    $chain = app(TextProviderResolver::class)->providerChain();

    expect($chain)->toBe([
        'openrouter' => 'deepseek/deepseek-v4-flash',
    ]);
});

test('resolver appends gemini when gemini key is configured', function () {
    config()->set('ai.providers.openrouter.key', 'or-key');
    config()->set('ai.providers.openrouter.models.text.default', 'deepseek/deepseek-v4-flash');
    config()->set('ai.providers.gemini.key', 'gemini-key');
    config()->set('ai.providers.gemini.models.text.default', 'gemini-3.5-flash');

    $chain = app(TextProviderResolver::class)->providerChain();

    expect($chain)->toBe([
        'openrouter' => 'deepseek/deepseek-v4-flash',
        'gemini' => 'gemini-3.5-flash',
    ]);
});
