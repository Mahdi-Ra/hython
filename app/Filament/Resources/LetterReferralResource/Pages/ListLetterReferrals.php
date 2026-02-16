<?php

namespace App\Filament\Resources\LetterReferralResource\Pages;

use App\Filament\Resources\LetterReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterReferrals extends ListRecords
{
    protected static string $resource = LetterReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ارجاع جدید'),
        ];
    }
}
