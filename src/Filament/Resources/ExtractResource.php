<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\ExtractResource\Pages;
use ApproTickets\Models\Extract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ExtractResource extends Resource
{
    protected static ?string $model = Extract::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Extractes';
    protected static ?string $modelLabel = 'extracte';
    protected static ?string $pluralModelLabel = 'extractes';
    protected static ?string $navigationGroup = 'Vendes';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')->label('Producte')
                    ->relationship('product', 'title')->searchable('title'),
                Forms\Components\Select::make('user_id')->label('Organitzador')
                    ->relationship('user', 'name')->searchable('name'),
                Forms\Components\DatePicker::make('date_start')->label('Data inici')->required(),
                Forms\Components\DatePicker::make('date_end')->label('Data fi')->required(),
                Forms\Components\Placeholder::make('sale')->label('Vendes')
                    ->visible(fn($record) => $record !== null)
                    ->content(function ($record) {
                        if (!$record)
                            return '';

                        $content = '<div class="overflow-x-auto"><table class="border border-gray-300 divide-y divide-gray-300" style="width:100%">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-300">Producte</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-300">Tarifa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-300">Vendes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-300">Devolucions</th>
                            </tr>
                        </thead><tbody class="bg-white divide-y divide-gray-200">';
                        foreach ($record->sales as $sale):
                            $refund = $sale['refund'] ?? 0;
                            $content .= "<tr>
                            <td class='text-left px-6 py-4 whitespace-nowrap border-b border-gray-300'>{$sale['product']}</td>
                            <td class='text-left px-6 py-4 whitespace-nowrap border-b border-gray-300'>{$sale['rate']}</td>
                            <td class='text-left px-6 py-4 whitespace-nowrap border-b border-gray-300'>{$sale["settle"]} €</td>
                            <td class='text-left px-6 py-4 whitespace-nowrap border-b border-gray-300'>{$refund} €</td>
                            </tr>";
                        endforeach;
                        $content .= '<tr>
                            <td colspan="3" class="text-left px-6 py-4 whitespace-nowrap border-b border-gray-300"><strong>Total</strong></td>
                            <td class="text-left px-6 py-4 whitespace-nowrap border-b border-gray-300"><strong>' . $record->total . ' €</strong></td>
                        </tr>';
                        $content .= '</tbody></table></div>';
                        return new HtmlString($content);
                    })->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Organitzador')->badge()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('product.title')->label('Producte')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('date_start')->dateTime('d/m/Y')->label('Data inici')->sortable(),
                Tables\Columns\TextColumn::make('date_end')->dateTime('d/m/Y')->label('Data fi')->sortable(),
                Tables\Columns\TextColumn::make('total')->label('Entrades')->sortable(),
                Tables\Columns\ToggleColumn::make('paid')->label('Pagat')->sortable(),
                Tables\Columns\TextColumn::make('total')->label('Total')->suffix('€'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadExcel')
                    ->label('Excel')
                    ->icon('heroicon-o-document')
                    ->url(function ($record) {
                        return route('admin.extract.excel', [
                            'id' => $record->id,
                        ]);
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListExtracts::route('/'),
            'create' => Pages\CreateExtract::route('/create'),
            'edit' => Pages\EditExtract::route('/{record}/edit'),
        ];
    }
}
