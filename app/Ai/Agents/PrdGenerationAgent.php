<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Gemini)]
class PrdGenerationAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
        Generate the body content for a product requirements document.

        Constraints:
        - Return body content only, not a full markdown document.
        - Do not repeat the main document title.
        - Use concise markdown lists and short paragraphs.
        - Stay grounded in the provided project context.
        - Avoid filler, marketing language, and implementation trivia.
        PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()->required(),
        ];
    }
}
