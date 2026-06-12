<?php

namespace Database\Factories;

use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GenerationOutput>
 */
class GenerationOutputFactory extends Factory
{
    protected $model = GenerationOutput::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transcript_session_id' => TranscriptSession::factory(),
            'type' => 'prd',
            'status' => 'pending',
            'content' => null,
            'error_message' => null,
            'revision_number' => 1,
            'clarification_snapshot' => null,
        ];
    }
}
