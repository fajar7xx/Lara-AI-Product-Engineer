<?php

namespace App\Policies;

use App\Models\TranscriptSession;
use App\Models\User;

class TranscriptSessionPolicy
{
    public function view(User $user, TranscriptSession $transcriptSession): bool
    {
        return $user->is($transcriptSession->user);
    }

    public function update(User $user, TranscriptSession $transcriptSession): bool
    {
        return $user->is($transcriptSession->user);
    }
}
