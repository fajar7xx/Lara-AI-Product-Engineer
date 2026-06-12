<?php

namespace App\Services\Generation;

use App\Models\TranscriptSession;

class PromptBuilder
{
    public function buildTranscriptExtractionPrompt(string $transcript): string
    {
        return <<<PROMPT
        Analyze the transcript below and extract product-planning context.

        Transcript:
        {$transcript}
        PROMPT;
    }

    public function buildPrdPrompt(TranscriptSession $transcriptSession): string
    {
        return <<<PROMPT
        Generate the PRD body for this product.

        Output requirements:
        - Focus on functional scope, success metrics, and risks.
        - Use markdown bullets where they improve clarity.
        - Do not write the top-level title.
        - Do not wrap the result in code fences.

        {$this->sharedProjectContext($transcriptSession)}
        PROMPT;
    }

    public function buildUserStoriesPrompt(TranscriptSession $transcriptSession): string
    {
        return <<<PROMPT
        Generate user stories for this product.

        Output requirements:
        - Use the "As a / I want / So that" shape when helpful.
        - Include acceptance criteria for each story.
        - Prefer concise bullets.
        - Do not write the top-level title.
        - Do not wrap the result in code fences.

        {$this->sharedProjectContext($transcriptSession)}
        PROMPT;
    }

    public function buildFunctionalRequirementsPrompt(TranscriptSession $transcriptSession): string
    {
        return <<<PROMPT
        Generate functional requirements for this product.

        Output requirements:
        - Group requirements by module or capability.
        - Prefer explicit requirement statements.
        - Keep the result concise and implementation-relevant.
        - Do not write the top-level title.
        - Do not wrap the result in code fences.

        {$this->sharedProjectContext($transcriptSession)}
        PROMPT;
    }

    public function buildHtmlPagePrompt(TranscriptSession $transcriptSession): string
    {
        return <<<PROMPT
        Generate structured content for a single-page {$transcriptSession->template_family} using the {$transcriptSession->design_system} design system.

        Output requirements:
        - Title: clear and short.
        - Tagline: one or two concise sentences.
        - Sections: 3 to 5 sections.
        - Keep section bodies plain text.
        - Do not output HTML.

        {$this->sharedProjectContext($transcriptSession)}
        PROMPT;
    }

    /**
     * @param  array<int, string>  $items
     */
    protected function implodeList(array $items): string
    {
        return implode(', ', $items);
    }

    protected function sharedProjectContext(TranscriptSession $transcriptSession): string
    {
        return <<<PROMPT
        Project: {$transcriptSession->project_name}
        Summary: {$transcriptSession->project_summary}
        Target users: {$transcriptSession->target_users}
        Goals: {$this->implodeList($transcriptSession->goals ?? [])}
        Key features: {$this->implodeList($transcriptSession->key_features ?? [])}
        Template family: {$transcriptSession->template_family}
        Design system: {$transcriptSession->design_system}
        PROMPT;
    }
}
