<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterResource;
use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $modelLabel = 'وظیفه';
    protected static ?string $pluralModelLabel = 'وظایف';
    protected static ?string $navigationLabel = 'وظایف';
    protected static ?string $navigationGroup = 'مکاتبات';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات وظیفه')
                    ->schema([
                        Forms\Components\Select::make('letter_id')
                            ->label('نامه')
                            ->relationship('letter', 'subject')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('توضیحات')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('assigned_to_user_id')
                            ->label('مسئول')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('تعیین نشده'),
                        Forms\Components\Select::make('created_by_user_id')
                            ->label('ایجادکننده')
                            ->relationship('createdBy', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->id()),
                        Forms\Components\Select::make('priority')
                            ->label('اولویت')
                            ->options([
                                Task::PRIORITY_LOW => 'کم',
                                Task::PRIORITY_NORMAL => 'عادی',
                                Task::PRIORITY_HIGH => 'بالا',
                                Task::PRIORITY_URGENT => 'فوری',
                            ])
                            ->default(Task::PRIORITY_NORMAL),
                        Forms\Components\Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                Task::STATUS_PENDING => 'در انتظار',
                                Task::STATUS_IN_PROGRESS => 'در دست اقدام',
                                Task::STATUS_COMPLETED => 'انجام شده',
                                Task::STATUS_CANCELLED => 'لغو شده',
                            ])
                            ->default(Task::STATUS_PENDING),
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
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('letter.subject')
                    ->label('نامه')
                    ->limit(30)
                    ->searchable()
                    ->url(fn (Task $record) => LetterResource::getUrl('edit', ['record' => $record->letter])),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('مسئول')
                    ->placeholder('—')
                    ->searchable(),
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
                        Task::STATUS_COMPLETED => 'انجام شده',
                        Task::STATUS_CANCELLED => 'لغو شده',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Task::STATUS_COMPLETED => 'success',
                        Task::STATUS_CANCELLED => 'danger',
                        Task::STATUS_IN_PROGRESS => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('مهلت')
                    ->date('Y/m/d')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        Task::STATUS_PENDING => 'در انتظار',
                        Task::STATUS_IN_PROGRESS => 'در دست اقدام',
                        Task::STATUS_COMPLETED => 'انجام شده',
                        Task::STATUS_CANCELLED => 'لغو شده',
                    ]),
                Tables\Filters\SelectFilter::make('assigned_to_user_id')
                    ->label('مسئول')
                    ->relationship('assignedTo', 'name')
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
