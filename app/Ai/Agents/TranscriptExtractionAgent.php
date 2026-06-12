<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Gemini)]
class TranscriptExtractionAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
        You extract product-planning context from a transcript.

        Return a concise structured result using only the supported template families and design systems.
        Valid template families: landing, app_shell.
        Valid design systems: minimal, modern, corporate.
        PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_name' => $schema->string()->required(),
            'project_summary' => $schema->string()->required(),
            'target_users' => $schema->string()->required(),
            'goals' => $schema->array()->items($schema->string())->required(),
            'key_features' => $schema->array()->items($schema->string())->required(),
            'template_family_recommendation' => $schema->string()->enum(['landing', 'app_shell'])->required(),
            'template_family_options' => $schema->array()->items($schema->string()->enum(['landing', 'app_shell']))->required(),
            'design_system_recommendation' => $schema->string()->enum(['minimal', 'modern', 'corporate'])->required(),
            'design_system_options' => $schema->array()->items($schema->string()->enum(['minimal', 'modern', 'corporate']))->required(),
        ];
    }
}
