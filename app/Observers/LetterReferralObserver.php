<?php

namespace App\Observers;

use App\Models\LetterReferral;
use App\Notifications\LetterReferredNotification;

class LetterReferralObserver
{
    public function created(LetterReferral $referral): void
    {
        $referral->load(['letter', 'fromUser']);
        $referral->toUser->notify(new LetterReferredNotification($referral));
    }
}
