<?php

namespace App\Services\Ai;

class TextProviderResolver
{
    /**
     * @return array<string, string|null>
     */
    public function providerChain(): array
    {
        $providers = [
            'gemini' => $this->providerModel('gemini', 'gemini-3.5-flash'),
        ];

        if ($this->hasProviderKey('openai')) {
            $providers['openai'] = $this->providerModel('openai', 'gpt-4.1-mini');
        }

        return $providers;
    }

    protected function hasProviderKey(string $provider): bool
    {
        $key = config("ai.providers.{$provider}.key");

        return is_string($key) && $key !== '';
    }

    protected function providerModel(string $provider, string $fallback): ?string
    {
        $configured = config("ai.providers.{$provider}.models.text.default");

        return is_string($configured) && $configured !== '' ? $configured : $fallback;
    }
}
