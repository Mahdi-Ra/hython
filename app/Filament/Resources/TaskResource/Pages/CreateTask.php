<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = $data['created_by_user_id'] ?? auth()->id();
        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'وظیفه با موفقیت ایجاد شد.';
    }
}
