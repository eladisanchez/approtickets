<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\CouponResource\Pages;
use ApproTickets\Filament\Resources\CouponResource\RelationManagers;
use ApproTickets\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Codis descompte';
    protected static ?string $modelLabel = 'codi';
    protected static ?string $pluralModelLabel = 'codis';
    protected static ?string $navigationGroup = 'Entrades';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')->label('Codi')->required(),
                Forms\Components\TextInput::make('discount')->numeric()->label('Descompte')->required()->suffix('%'),
                Forms\Components\DateTimePicker::make('validity')->label('Vàlid fins a')->required(),
                Forms\Components\Select::make('product_id')->relationship()->label('Producte')->relationship('product', 'title')->required()->searchable('title'),
                Forms\Components\Select::make('rate_id')->relationship()->label('Tarifa')->relationship('rate', 'title')->required()->searchable('title'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Codi')->sortable()->searchable(isIndividual:true),
                Tables\Columns\TextColumn::make('discount')->label('Descompte')->suffix('%'),
                Tables\Columns\TextColumn::make('validity')->date('d/m/Y H:i')->label('Vàlid fins a'),
                Tables\Columns\TextColumn::make('product.title')->label('Producte')->sortable()->searchable(isIndividual:true),
                Tables\Columns\TextColumn::make('rate.title')->label('Tarifa')->sortable()->searchable(isIndividual:true),
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
            'index' => Pages\ListCoupons::route('/'),
            //'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
