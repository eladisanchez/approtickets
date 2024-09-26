<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\UserResource\Pages;
use ApproTickets\Filament\Resources\UserResource\RelationManagers;
use ApproTickets\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Usuaris';
    protected static ?string $modelLabel = 'usuari';
    protected static ?string $pluralModelLabel = 'usuaris';
    protected static ?string $navigationGroup = 'Usuaris';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('Nom')->required(),
                Forms\Components\TextInput::make('email')->label('Correu electrònic')->required(),
                Forms\Components\Select::make('roles')->label('Rols')
                    ->relationship('roles', 'display_name')
                    ->preload()
                    ->multiple(),
                Forms\Components\TextInput::make('password')->password()->label('Contrasenya')->required()->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Correu electrònic')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('roles.display_name')->label('Rols'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
