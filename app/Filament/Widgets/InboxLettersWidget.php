<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LetterResource;
use App\Models\Letter;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class InboxLettersWidget extends BaseWidget
{
    protected static ?string $heading = 'نامه‌های من';

    protected static ?string $description = 'آخرین نامه‌های ایجادشده توسط شما (غیر از پیش‌نویس)';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('موضوع')
                    ->limit(45)
                    ->searchable(false)
                    ->url(fn (Letter $record) => LetterResource::getUrl('view', ['record' => $record])),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->formatStateUsing(fn (string $state): string => $state === Letter::TYPE_INTERNAL ? 'داخلی' : 'خارجی')
                    ->badge()
                    ->color(fn (string $state): string => $state === Letter::TYPE_INTERNAL ? 'info' : 'warning'),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Letter::STATUS_DRAFT => 'پیش‌نویس',
                        Letter::STATUS_PENDING => 'در انتظار',
                        Letter::STATUS_IN_PROGRESS => 'در دست اقدام',
                        Letter::STATUS_COMPLETED => 'انجام شده',
                        Letter::STATUS_ARCHIVED => 'بایگانی',
                        default => $state,
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('مهلت')
                    ->date('Y/m/d')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('مشاهده')
                    ->url(fn (Letter $record) => LetterResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->striped();
    }

    protected function getTableQuery(): Builder
    {
        return Letter::query()
            ->where('user_id', auth()->id())
            ->where('status', '!=', Letter::STATUS_DRAFT)
            ->with('user')
            ->latest()
            ->limit(20);
    }
}
