<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterResource\Pages;
use App\Filament\Resources\LetterResource\RelationManagers;
use App\Models\Letter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LetterResource extends Resource
{
    protected static ?string $model = Letter::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $modelLabel = 'نامه';
    protected static ?string $pluralModelLabel = 'نامه‌ها';
    protected static ?string $navigationLabel = 'نامه‌ها';
    protected static ?string $navigationGroup = 'مکاتبات';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات نامه')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('ایجادکننده')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->id()),
                        Forms\Components\Select::make('department_id')
                            ->label('واحد')
                            ->relationship('department', 'name', fn (Builder $q) => $q->active())
                            ->searchable()
                            ->preload()
                            ->placeholder('بدون واحد'),
                        Forms\Components\Select::make('type')
                            ->label('نوع')
                            ->options([
                                Letter::TYPE_INTERNAL => 'داخلی',
                                Letter::TYPE_EXTERNAL => 'خارجی',
                            ])
                            ->required()
                            ->default(Letter::TYPE_INTERNAL),
                        Forms\Components\TextInput::make('subject')
                            ->label('موضوع')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('body')
                            ->label('متن')
                            ->rows(5)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('reference_number')
                            ->label('شماره مرجع')
                            ->maxLength(255)
                            ->placeholder('اختیاری'),
                        Forms\Components\Select::make('priority')
                            ->label('اولویت')
                            ->options([
                                Letter::PRIORITY_LOW => 'کم',
                                Letter::PRIORITY_NORMAL => 'عادی',
                                Letter::PRIORITY_HIGH => 'بالا',
                                Letter::PRIORITY_URGENT => 'فوری',
                            ])
                            ->default(Letter::PRIORITY_NORMAL),
                        Forms\Components\Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                Letter::STATUS_DRAFT => 'پیش‌نویس',
                                Letter::STATUS_PENDING => 'در انتظار',
                                Letter::STATUS_IN_PROGRESS => 'در دست اقدام',
                                Letter::STATUS_COMPLETED => 'انجام شده',
                                Letter::STATUS_ARCHIVED => 'بایگانی',
                            ])
                            ->default(Letter::STATUS_DRAFT),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('مهلت')
                            ->placeholder('اختیاری'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('موضوع')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('ایجادکننده')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->formatStateUsing(fn (string $state): string => $state === Letter::TYPE_INTERNAL ? 'داخلی' : 'خارجی')
                    ->badge()
                    ->color(fn (string $state): string => $state === Letter::TYPE_INTERNAL ? 'info' : 'warning'),
                Tables\Columns\TextColumn::make('priority')
                    ->label('اولویت')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Letter::PRIORITY_URGENT => 'فوری',
                        Letter::PRIORITY_HIGH => 'بالا',
                        Letter::PRIORITY_NORMAL => 'عادی',
                        Letter::PRIORITY_LOW => 'کم',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Letter::PRIORITY_URGENT => 'danger',
                        Letter::PRIORITY_HIGH => 'warning',
                        default => 'gray',
                    }),
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
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع')
                    ->options([
                        Letter::TYPE_INTERNAL => 'داخلی',
                        Letter::TYPE_EXTERNAL => 'خارجی',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        Letter::STATUS_DRAFT => 'پیش‌نویس',
                        Letter::STATUS_PENDING => 'در انتظار',
                        Letter::STATUS_IN_PROGRESS => 'در دست اقدام',
                        Letter::STATUS_COMPLETED => 'انجام شده',
                        Letter::STATUS_ARCHIVED => 'بایگانی',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('اولویت')
                    ->options([
                        Letter::PRIORITY_LOW => 'کم',
                        Letter::PRIORITY_NORMAL => 'عادی',
                        Letter::PRIORITY_HIGH => 'بالا',
                        Letter::PRIORITY_URGENT => 'فوری',
                    ]),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttachmentsRelationManager::class,
            RelationManagers\ReferralsRelationManager::class,
            RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLetters::route('/'),
            'create' => Pages\CreateLetter::route('/create'),
            'view' => Pages\ViewLetter::route('/{record}'),
            'edit' => Pages\EditLetter::route('/{record}/edit'),
        ];
    }
}
