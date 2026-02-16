<?php

namespace App\Filament\Resources\LetterReferralResource\Pages;

use App\Filament\Resources\LetterReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLetterReferral extends ViewRecord
{
    protected static string $resource = LetterReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('ویرایش'),
        ];
    }
}
