<?php

namespace App\Services\Transcripts;

class TranscriptValidationService
{
    /**
     * @return array{valid: bool, errors: array<int, string>}
     */
    public function validate(string $transcript): array
    {
        $normalizedTranscript = trim(preg_replace('/\s+/', ' ', $transcript) ?? '');

        $errors = [];

        if ($normalizedTranscript === '') {
            $errors[] = 'Transcript is required.';
        }

        $wordCount = str_word_count($normalizedTranscript);

        if ($wordCount < 30) {
            $errors[] = 'Transcript must contain at least 30 words.';
        }

        $signalGroups = [
            ['product', 'app', 'platform', 'website', 'dashboard', 'landing'],
            ['user', 'customer', 'audience', 'team', 'manager', 'founder'],
            ['feature', 'goal', 'problem', 'requirement', 'workflow', 'page'],
        ];

        $matchedGroups = 0;

        foreach ($signalGroups as $group) {
            foreach ($group as $keyword) {
                if (str_contains(strtolower($normalizedTranscript), $keyword)) {
                    $matchedGroups++;
                    break;
                }
            }
        }

        if ($matchedGroups < 2) {
            $errors[] = 'Transcript must describe a product, user, or feature direction clearly enough for generation.';
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
        ];
    }
}
