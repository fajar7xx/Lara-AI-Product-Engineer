<?php

namespace Database\Factories;

use App\Models\TranscriptSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TranscriptSession>
 */
class TranscriptSessionFactory extends Factory
{
    protected $model = TranscriptSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ulid' => (string) Str::ulid(),
            'source_type' => 'paste',
            'transcript_text' => 'We are building a product that helps product managers turn meeting transcripts into specs. Users need faster documentation, cleaner user stories, and an app shell recommendation for the first release.',
            'status' => 'draft',
            'extracted_context' => null,
            'layout_recommendations' => null,
            'design_system_recommendations' => null,
            'project_name' => null,
            'project_summary' => null,
            'target_users' => null,
            'goals' => null,
            'key_features' => null,
            'template_family' => null,
            'design_system' => null,
        ];
    }
}
