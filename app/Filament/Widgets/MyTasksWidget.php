<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LetterResource;
use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class MyTasksWidget extends BaseWidget
{
    protected static ?string $heading = 'وظایف من';

    protected static ?string $description = 'وظایفی که به شما محول شده و هنوز انجام نشده‌اند';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->limit(40)
                    ->url(fn (Task $record) => TaskResource::getUrl('view', ['record' => $record])),
                Tables\Columns\TextColumn::make('letter.subject')
                    ->label('نامه')
                    ->limit(30)
                    ->url(fn (Task $record) => LetterResource::getUrl('view', ['record' => $record->letter])),
                Tables\Columns\TextColumn::make('priority')
                    ->label('اولویت')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Task::PRIORITY_URGENT => 'فوری',
                        Task::PRIORITY_HIGH => 'بالا',
                        Task::PRIORITY_NORMAL => 'عادی',
                        Task::PRIORITY_LOW => 'کم',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Task::PRIORITY_URGENT => 'danger',
                        Task::PRIORITY_HIGH => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Task::STATUS_PENDING => 'در انتظار',
                        Task::STATUS_IN_PROGRESS => 'در دست اقدام',
                        default => $state,
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('مهلت')
                    ->date('Y/m/d')
                    ->placeholder('—'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('مشاهده')
                    ->url(fn (Task $record) => TaskResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->striped();
    }

    protected function getTableQuery(): Builder
    {
        return Task::query()
            ->where('assigned_to_user_id', auth()->id())
            ->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])
            ->with('letter')
            ->latest()
            ->limit(20);
    }
}
