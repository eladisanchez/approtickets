<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\OptionResource\Pages;
use ApproTickets\Filament\Resources\OptionResource\RelationManagers;
use ApproTickets\Models\Option;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\Layout\Stack;
use ApproTickets\Enums\OptionType;

class OptionResource extends Resource
{
    protected static ?string $model = Option::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Opcions';
    protected static ?string $modelLabel = 'opció';
    protected static ?string $pluralModelLabel = 'opcions';
    protected static ?int $navigationSort = 9;

    public static function form(Schema $form): Schema
    {

        $fields = [
            Forms\Components\TextInput::make('key')
                ->label('Opció')
                ->required()
                ->visible(auth()->user()->isSuperadmin())
                ->columnSpan(2),
            Forms\Components\TextInput::make('name')
                ->label('Nom')
                ->required()
                ->visible(auth()->user()->isSuperadmin())
                ->columnSpan(2),
            Forms\Components\Select::make('type')
                ->label('Tipus')
                ->options(OptionType::class)
                ->visible(auth()->user()->isSuperadmin())
                ->columnSpan(2),
            Forms\Components\TextInput::make('description')
                ->label('Descripció')
                ->visible(auth()->user()->isSuperadmin())
                ->columnSpan('full'),
        ];

        if (gettype($form->model) == 'string' || $form->model->type == OptionType::Text) {
            $fields[] = RichEditor::make('value')
                ->label(fn(Get $get) => $get('name') ?? 'Valor')
                ->helperText(fn(Get $get) => $get('description'))
                ->columnSpan('full');
        } else {
            $fields[] = Forms\Components\DateTimePicker::make('value')
                ->label(fn(Get $get) => $get('name'))
                ->helperText(fn(Get $get) => $get('description'))
                ->columnSpan('full');
        }

        return $form
            ->schema($fields)->columns(6);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Tables\Columns\TextColumn::make('name')->label('Opció'),
                ]),

            ])->contentGrid([
                    'md' => 2,
                    'xl' => 3,
                ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Actions\BulkActionGroup::make([
                //     Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListOptions::route('/'),
            'create' => Pages\CreateOption::route('/create'),
            'edit' => Pages\EditOption::route('/{record}/edit'),
        ];
    }
}
