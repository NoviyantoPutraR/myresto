<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\PasswordInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Facades\Filament;



class UserResource extends Resource
{
    protected static ?string $model = User::class;


    public static function shouldRegisterNavigation(): bool
    {
        // $user = Filament::auth()->user();

        // // Sembunyikan jika role-nya kasir
        // return !($user && $user->roles->contains('name', 'kasir'));
        return false;
    }


    protected static ?string $navigationIcon = 'heroicon-o-user';
    public static function getModelLabel(): string
    {
        return 'Pengguna';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pengguna';
    }


    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nama')
                ->required(),

            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                ->required(fn(string $context) => $context === 'create')
                ->maxLength(255),

            Select::make('roles')
                ->label('Role')
                ->multiple()
                ->preload()
                ->relationship('roles', 'name'), // penting: harus ada trait HasRoles
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->sortable()->searchable(),
                TextColumn::make('email')->sortable()->searchable(),
                TextColumn::make('roles.name')->label('Role')->badge()->color('primary'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => UserResource\Pages\ListUsers::route('/'),
            'create' => UserResource\Pages\CreateUser::route('/create'),
            'edit' => UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
