<?php

namespace ApproTickets\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use ApproTickets\Models\Refund;
use ApproTickets\Http\Controllers\RefundController;
use Filament\Notifications\Notification;

class RefundsRelationManager extends RelationManager
{
    protected static string $relationship = 'refunds';
    protected static ?string $recordTitleAttribute = 'product.title';
    protected static ?string $pluralLabel = 'Devolucions';
    protected static ?string $title = 'Devolucions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('total')
                    ->label('Quantitat')
                    ->numeric()
                    ->suffix(' €')
                    ->required()
                    ->columnSpanFull()
                    ->default($state->total ?? 0),
            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.title')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Creació')
                    ->date('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('refunded_at')->label('Efectuada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-m-check')
                    ->iconColor('success')
                    ->placeholder('No efectuada'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Quantitat')
                    ->suffix(' €')
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Crear devolució')
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(function ($record) {
                        return $record->refunded_at;
                    }),
                Tables\Actions\Action::make('url')
                    ->label('Enllaç')
                    ->icon('heroicon-o-link')
                    ->url(function (Refund $record) {
                        if (!$record->hash) {
                            return null;
                        }
                        return route('refund', [
                            'hash' => $record->hash
                        ]);
                    })
                    ->openUrlInNewTab()
                    ->hidden(function ($record) {
                        return $record->refunded_at;
                    }),
                Tables\Actions\Action::make('request')
                    ->label('Efectuar devolució')
                    ->icon('heroicon-o-forward')
                    ->action(function (Refund $record) {
                        $refundRequest = RefundController::requestRefund($record);
                        if (isset($refundRequest['error'])) {
                            Notification::make()
                                ->title('Error en la petició de devolució')
                                ->body($refundRequest['error'])
                                ->danger()
                                ->send();
                        } else {
                            $record->update(['refunded_at' => now()]);
                            Notification::make()
                                ->title("Devolució efectuada correctament")
                                ->body("S'ha efectuat la devolució de {$record->total} € per la comanda {$record->order_id}.")
                                ->success()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->color('warning')
                    ->hidden(function ($record) {
                        return $record->refunded_at;
                    }),
            ]);
    }
}
