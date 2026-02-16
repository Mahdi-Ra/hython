<?php

namespace App\Filament\Resources\LetterReferralResource\Pages;

use App\Filament\Resources\LetterReferralResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLetterReferral extends CreateRecord
{
    protected static string $resource = LetterReferralResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'ارجاع با موفقیت ثبت شد.';
    }
}
