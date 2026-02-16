<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterReferralResource\Pages;
use App\Filament\Resources\LetterResource;
use App\Models\LetterReferral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterReferralResource extends Resource
{
    protected static ?string $model = LetterReferral::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $modelLabel = 'ارجاع';
    protected static ?string $pluralModelLabel = 'ارجاعات';
    protected static ?string $navigationLabel = 'ارجاعات';
    protected static ?string $navigationGroup = 'مکاتبات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات ارجاع')
                    ->schema([
                        Forms\Components\Select::make('letter_id')
                            ->label('نامه')
                            ->relationship('letter', 'subject')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('from_user_id')
                            ->label('از کاربر')
                            ->relationship('fromUser', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->id()),
                        Forms\Components\Select::make('to_user_id')
                            ->label('به کاربر')
                            ->relationship('toUser', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('assigned_by_user_id')
                            ->label('ثبت توسط')
                            ->relationship('assignedByUser', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id()),
                        Forms\Components\Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                LetterReferral::STATUS_PENDING => 'در انتظار',
                                LetterReferral::STATUS_ACCEPTED => 'پذیرفته شده',
                                LetterReferral::STATUS_COMPLETED => 'انجام شده',
                                LetterReferral::STATUS_REJECTED => 'رد شده',
                            ])
                            ->default(LetterReferral::STATUS_PENDING),
                        Forms\Components\Textarea::make('note')
                            ->label('یادداشت')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('referred_at')
                            ->label('تاریخ ارجاع')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('responded_at')
                            ->label('تاریخ پاسخ')
                            ->placeholder('اختیاری'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('letter.subject')
                    ->label('نامه')
                    ->limit(35)
                    ->searchable()
                    ->sortable()
                    ->url(fn (LetterReferral $record) => LetterResource::getUrl('edit', ['record' => $record->letter])),
                Tables\Columns\TextColumn::make('fromUser.name')
                    ->label('از'),
                Tables\Columns\TextColumn::make('toUser.name')
                    ->label('به')
                    ->searchable(),
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
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        LetterReferral::STATUS_PENDING => 'در انتظار',
                        LetterReferral::STATUS_ACCEPTED => 'پذیرفته شده',
                        LetterReferral::STATUS_COMPLETED => 'انجام شده',
                        LetterReferral::STATUS_REJECTED => 'رد شده',
                    ]),
                Tables\Filters\SelectFilter::make('to_user_id')
                    ->label('ارجاع به')
                    ->relationship('toUser', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('مشاهده'),
                Tables\Actions\EditAction::make()->label('ویرایش'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف انتخاب‌شده‌ها'),
                ]),
            ])
            ->defaultSort('referred_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLetterReferrals::route('/'),
            'create' => Pages\CreateLetterReferral::route('/create'),
            'view' => Pages\ViewLetterReferral::route('/{record}'),
            'edit' => Pages\EditLetterReferral::route('/{record}/edit'),
        ];
    }
}
