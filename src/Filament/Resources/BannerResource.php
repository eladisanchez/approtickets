<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\BannerResource\Pages;
use ApproTickets\Models\Banner;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class BannerResource extends Resource
{

    protected static ?string $model = Banner::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Destacats';
    protected static ?string $modelLabel = 'destacat';
    protected static ?string $pluralModelLabel = 'destacats';
    protected static string|\UnitEnum|null $navigationGroup = 'Entrades';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Títol')
                    ->required()
                    ->columnSpan(2),
                Select::make('product_id')
                    ->label('Producte')
                    ->relationship(
                        name: 'product',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn(Builder $query) => $query->active()->orderBy('order', 'asc'),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(4),
                DateTimePicker::make('date_start')
                    ->label('Inici')
                    ->columnSpan(3),
                DateTimePicker::make('date_end')
                    ->label('Fi')
                    ->columnSpan(3),
            ])->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Títol')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.title')->badge()->sortable()
                    ->label('Producte'),
                Tables\Columns\TextColumn::make('date_start')->date('d/m/Y H:i')->label('Inici'),
                Tables\Columns\TextColumn::make('date_end')->date('d/m/Y H:i')->label('Fi'),
            ])
            ->filters([
                //Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order')
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
