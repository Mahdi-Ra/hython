<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LetterReferralResource;
use App\Filament\Resources\LetterResource;
use App\Models\LetterReferral;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingReferralsWidget extends BaseWidget
{
    protected static ?string $heading = 'ارجاعات در انتظار';

    protected static ?string $description = 'ارجاعاتی که برای شما ارسال شده و هنوز پاسخ داده نشده‌اند';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('letter.subject')
                    ->label('نامه')
                    ->limit(40)
                    ->url(fn (LetterReferral $record) => LetterResource::getUrl('view', ['record' => $record->letter])),
                Tables\Columns\TextColumn::make('fromUser.name')
                    ->label('ارجاع‌دهنده'),
                Tables\Columns\TextColumn::make('referred_at')
                    ->label('تاریخ ارجاع')
                    ->dateTime('Y/m/d H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_letter')
                    ->label('مشاهده نامه')
                    ->url(fn (LetterReferral $record) => LetterResource::getUrl('view', ['record' => $record->letter])),
                Tables\Actions\Action::make('edit_referral')
                    ->label('ویرایش ارجاع')
                    ->url(fn (LetterReferral $record) => LetterReferralResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->striped();
    }

    protected function getTableQuery(): Builder
    {
        return LetterReferral::query()
            ->where('to_user_id', auth()->id())
            ->where('status', LetterReferral::STATUS_PENDING)
            ->with(['letter', 'fromUser'])
            ->latest('referred_at')
            ->limit(20);
    }
}
