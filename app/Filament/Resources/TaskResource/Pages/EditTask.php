<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('مشاهده'),
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'وظیفه به‌روزرسانی شد.';
    }
}
