<?php

namespace App\Services\Generation;

class MarkdownBlueprintService
{
    /**
     * @param  array{
     *     project_name?: string,
     *     project_summary?: string,
     *     target_users?: string,
     *     goals?: array<int, string>,
     *     key_features?: array<int, string>
     * }  $context
     */
    public function prd(array $context, string $body = ''): string
    {
        $projectName = $context['project_name'] ?? 'Untitled Project';
        $projectSummary = $context['project_summary'] ?? 'No summary provided yet.';
        $targetUsers = $context['target_users'] ?? 'No target users provided yet.';
        $goals = $this->bulletList($context['goals'] ?? []);
        $features = $this->bulletList($context['key_features'] ?? []);

        return trim(<<<MARKDOWN
        # Product Requirements Document

        ## Product Overview
        **Project:** {$projectName}

        {$projectSummary}

        ## Target Users
        {$targetUsers}

        ## Goals
        {$goals}

        ## Key Features
        {$features}

        ## Functional Scope
        {$body}

        ## Success Metrics
        - Define measurable success criteria during generation.

        ## Risks
        - Capture generation risks and assumptions.
        MARKDOWN);
    }

    /**
     * @param  array{
     *     project_name?: string,
     *     target_users?: string,
     *     goals?: array<int, string>
     * }  $context
     */
    public function userStories(array $context, string $body = ''): string
    {
        $projectName = $context['project_name'] ?? 'Untitled Project';
        $targetUsers = $context['target_users'] ?? 'No target users provided yet.';
        $goals = $this->bulletList($context['goals'] ?? []);

        return trim(<<<MARKDOWN
        # User Stories

        ## Product Context
        **Project:** {$projectName}

        **Target Users:** {$targetUsers}

        ## User Goals
        {$goals}

        ## Stories
        {$body}

        ## Acceptance Criteria
        - Each story should include concrete acceptance criteria.
        MARKDOWN);
    }

    /**
     * @param  array{
     *     project_name?: string,
     *     key_features?: array<int, string>
     * }  $context
     */
    public function functionalRequirements(array $context, string $body = ''): string
    {
        $projectName = $context['project_name'] ?? 'Untitled Project';
        $features = $this->bulletList($context['key_features'] ?? []);

        return trim(<<<MARKDOWN
        # Functional Requirements

        ## Product Context
        **Project:** {$projectName}

        ## Key Features
        {$features}

        ## Requirements by Module
        {$body}

        ## Constraints
        - Record technical and product constraints clearly.
        MARKDOWN);
    }

    /**
     * @param  array<int, string>  $items
     */
    protected function bulletList(array $items): string
    {
        if ($items === []) {
            return '- None provided yet.';
        }

        return collect($items)
            ->map(fn (string $item): string => '- '.$item)
            ->implode(PHP_EOL);
    }
}
