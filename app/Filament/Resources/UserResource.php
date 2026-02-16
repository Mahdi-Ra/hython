<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'کاربر';
    protected static ?string $pluralModelLabel = 'کاربران';
    protected static ?string $navigationLabel = 'کاربران';
    protected static ?string $navigationGroup = 'مدیریت سیستم';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات کاربر')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('نام')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('رایانامه')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('role')
                            ->label('نقش')
                            ->options([
                                User::ROLE_ADMIN => 'مدیر سیستم',
                                User::ROLE_MANAGER => 'مدیر',
                                User::ROLE_EMPLOYEE => 'کارمند',
                            ])
                            ->required()
                            ->default(User::ROLE_EMPLOYEE),
                        Forms\Components\Select::make('department_id')
                            ->label('واحد')
                            ->relationship('department', 'name', fn (Builder $q) => $q->active())
                            ->searchable()
                            ->preload()
                            ->placeholder('بدون واحد'),
                        Forms\Components\TextInput::make('password')
                            ->label('رمز عبور')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->minLength(8),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('رایانامه')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('نقش')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'مدیر سیستم',
                        User::ROLE_MANAGER => 'مدیر',
                        User::ROLE_EMPLOYEE => 'کارمند',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_MANAGER => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('واحد')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('نقش')
                    ->options([
                        User::ROLE_ADMIN => 'مدیر سیستم',
                        User::ROLE_MANAGER => 'مدیر',
                        User::ROLE_EMPLOYEE => 'کارمند',
                    ]),
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('واحد')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف انتخاب‌شده‌ها'),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
