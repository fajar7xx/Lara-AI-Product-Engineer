<?php

namespace App\Jobs;

use App\Models\TranscriptSession;
use App\Services\Generation\SessionGenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateSessionOutputs implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    /**
     * @param  list<string>|null  $targetTypes
     */
    public function __construct(
        public int $transcriptSessionId,
        public ?array $targetTypes = null,
    ) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(SessionGenerationService $sessionGenerationService): void
    {
        $transcriptSession = TranscriptSession::query()->findOrFail($this->transcriptSessionId);

        $sessionGenerationService->generate($transcriptSession, $this->targetTypes);
    }

    public function failed(Throwable $throwable): void
    {
        $transcriptSession = TranscriptSession::query()->find($this->transcriptSessionId);

        if (! $transcriptSession instanceof TranscriptSession) {
            return;
        }

        $transcriptSession->forceFill([
            'status' => TranscriptSession::STATUS_FAILED,
        ])->save();
    }
}
