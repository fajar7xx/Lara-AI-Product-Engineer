<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Gemini)]
class HtmlPageContentAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
        Generate structured content for a constrained single-page website.

        Constraints:
        - Stay within the requested template family.
        - Output concise copy, not HTML.
        - Prefer 3 to 5 sections.
        - Section headings should be short and specific.
        - Section bodies should be plain text paragraphs, not markdown lists.
        PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'tagline' => $schema->string()->required(),
            'sections' => $schema->array()->items(
                $schema->object(fn (JsonSchema $schema): array => [
                    'heading' => $schema->string()->required(),
                    'body' => $schema->string()->required(),
                ])
            )->required(),
        ];
    }
}
