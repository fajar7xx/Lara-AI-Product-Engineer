<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Gemini)]
class UserStoriesGenerationAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
        Generate user stories for the provided product context.

        Constraints:
        - Return body content only, not a full markdown document.
        - Each story should be concrete and implementation-relevant.
        - Include acceptance criteria inline with each story.
        - Prefer short bullet lists over long prose.
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
