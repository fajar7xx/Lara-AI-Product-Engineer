<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Gemini)]
class FunctionalRequirementsGenerationAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
        Generate functional requirements grouped by logical modules.

        Constraints:
        - Return body content only, not a full markdown document.
        - Group requirements into clear modules or sections.
        - Prefer explicit "system shall" style requirements when possible.
        - Avoid speculative features not supported by the input context.
        - Do not repeat the document title.
        PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()->required(),
        ];
    }
}
