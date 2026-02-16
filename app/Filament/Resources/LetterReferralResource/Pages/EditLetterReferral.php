<?php

namespace App\Filament\Resources\LetterReferralResource\Pages;

use App\Filament\Resources\LetterReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLetterReferral extends EditRecord
{
    protected static string $resource = LetterReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('مشاهده'),
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'ارجاع به‌روزرسانی شد.';
    }
}
