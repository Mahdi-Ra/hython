<?php

namespace App\Filament\Resources\LetterResource\RelationManagers;

use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'وظایف';

    protected static ?string $modelLabel = 'وظیفه';
    protected static ?string $pluralModelLabel = 'وظایف';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('توضیحات')
                    ->rows(3),
                Forms\Components\Select::make('assigned_to_user_id')
                    ->label('مسئول')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('تعیین نشده'),
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
                    ->label('مهلت'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->limit(35)
                    ->searchable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('مسئول')
                    ->placeholder('—'),
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
                    ->badge(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('مهلت')
                    ->date('Y/m/d')
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('وظیفه جدید')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by_user_id'] = auth()->id();
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
