<?php

namespace App\Filament\Resources\LetterResource\RelationManagers;

use App\Models\LetterReferral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'referrals';

    protected static ?string $title = 'ارجاعات';

    protected static ?string $modelLabel = 'ارجاع';
    protected static ?string $pluralModelLabel = 'ارجاعات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('to_user_id')
                    ->label('ارجاع به')
                    ->relationship('toUser', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('assigned_by_user_id')
                    ->label('ارجاع‌دهنده (ثبت توسط)')
                    ->relationship('assignedByUser', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id()),
                Forms\Components\Textarea::make('note')
                    ->label('یادداشت')
                    ->rows(2),
                Forms\Components\Select::make('status')
                    ->label('وضعیت')
                    ->options([
                        LetterReferral::STATUS_PENDING => 'در انتظار',
                        LetterReferral::STATUS_ACCEPTED => 'پذیرفته شده',
                        LetterReferral::STATUS_COMPLETED => 'انجام شده',
                        LetterReferral::STATUS_REJECTED => 'رد شده',
                    ])
                    ->default(LetterReferral::STATUS_PENDING),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fromUser.name')
                    ->label('از'),
                Tables\Columns\TextColumn::make('toUser.name')
                    ->label('به'),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        LetterReferral::STATUS_PENDING => 'در انتظار',
                        LetterReferral::STATUS_ACCEPTED => 'پذیرفته شده',
                        LetterReferral::STATUS_COMPLETED => 'انجام شده',
                        LetterReferral::STATUS_REJECTED => 'رد شده',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        LetterReferral::STATUS_PENDING => 'warning',
                        LetterReferral::STATUS_COMPLETED => 'success',
                        LetterReferral::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('referred_at')
                    ->label('تاریخ ارجاع')
                    ->dateTime('Y/m/d H:i'),
            ])
            ->defaultSort('referred_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('ارجاع جدید')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['from_user_id'] = auth()->id();
                        $data['referred_at'] = now();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف انتخاب‌شده‌ها'),
                ]),
            ]);
    }
}
