<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\BannerResource\Pages;
use ApproTickets\Models\Banner;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Concerns\Translatable;

class BannerResource extends Resource
{

    use Translatable;
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Destacats';
    protected static ?string $modelLabel = 'destacat';
    protected static ?string $pluralModelLabel = 'destacats';
    protected static ?string $navigationGroup = 'Entrades';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Títol')
                    ->required()
                    ->columnSpan(6),
                Select::make('product_id')
                    ->label('Producte')
                    ->relationship('product', 'title')
                    ->columnSpanFull(),
                DateTimePicker::make('date_start')
                    ->label('Inici')
                    ->columnSpan(3),
                DateTimePicker::make('date_start')
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
