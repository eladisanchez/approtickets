<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\CategoryResource\Pages;
use ApproTickets\Models\Category;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Categories';
    protected static ?string $modelLabel = 'categoria';
    protected static ?string $pluralModelLabel = 'categories';
    protected static ?string $navigationGroup = 'Entrades';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Nom')
                    ->required(),
                Textarea::make('summay')
                    ->label('Resum'),
                Select::make('target')->label('Tipus')
                    ->options(config('tickets.types'))
                    ->required(),
                FileUpload::make('image')
                    ->label('Imatge de fons')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('products')
                    ->columnSpan(6),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Títol'),
                Tables\Columns\TextColumn::make('products_count')->counts('products')->badge()->sortable()
                    ->badge()->label('Productes')
            ])
            ->filters([
                //Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
