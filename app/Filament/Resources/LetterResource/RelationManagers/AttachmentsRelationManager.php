<?php

namespace App\Filament\Resources\LetterResource\RelationManagers;

use App\Models\Attachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'پیوست‌ها';

    protected static ?string $modelLabel = 'پیوست';
    protected static ?string $pluralModelLabel = 'پیوست‌ها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('path')
                    ->label('فایل')
                    ->required()
                    ->disk('public')
                    ->directory('letter-attachments')
                    ->visibility('private')
                    ->storeFileNamesIn('name')
                    ->openable(),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام فایل')
                    ->limit(40),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('آپلودکننده'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime('Y/m/d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('افزودن پیوست'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('دانلود')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Attachment $record): string => Storage::disk('public')->url($record->path))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف انتخاب‌شده‌ها'),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        if (empty($data['name']) && ! empty($data['path'])) {
            $data['name'] = basename($data['path']);
        }
        return $data;
    }
}
