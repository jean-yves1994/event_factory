<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages\ListPendingIssuanceByRequisition;
use App\Models\Item;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $navigationLabel = 'Stock Movements';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('returned_quantity')
                ->label('Returned Quantity')
                ->numeric()
                ->minValue(0)
                ->visible(fn ($record) => $record && $record->status === 'issued'),

            Forms\Components\Select::make('status')
                ->options([
                    'returned' => 'Returned',
                    'damaged' => 'Damaged',
                    'lost' => 'Lost',
                ])
                ->visible(fn ($record) => $record && $record->status === 'issued'),

            Forms\Components\DatePicker::make('action_date')
                ->label('Action Date')
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')->label('Item Name'),
                TextColumn::make('requisition.event.event_name')->label('Event Name'),
                TextColumn::make('quantity')->label('Issued Quantity'),
                TextColumn::make('returned_quantity')->label('Returned'),
                TextColumn::make('status')->badge()
                    ->colors([
                        'success' => 'returned',
                        'danger' => ['damaged', 'lost'],
                        'warning' => 'issued',
                        'gray' => 'pending',
                    ]),
                TextColumn::make('action_date')->label('Action Date'),
                TextColumn::make('expected_return_date')->label('Expected Return'),
                TextColumn::make('remaining_quantity')
                    ->label('Remaining')
                    ->getStateUsing(fn ($record) => max($record->quantity - $record->returned_quantity, 0)),

                TextColumn::make('overdue')
                    ->label('Overdue')
                    ->getStateUsing(fn ($record) =>
                        now()->greaterThan(Carbon::parse($record->expected_return_date)) && $record->status === 'issued'
                            ? '⚠️ Yes'
                            : 'No'
                    )
                    ->color(fn ($record) =>
                        now()->greaterThan(Carbon::parse($record->expected_return_date)) && $record->status === 'issued'
                            ? 'danger'
                            : 'success'
                    ),
            ])
            ->actions([
                Action::make('issue')
                    ->label('Issue Items')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $item = $record->item;

                        if ($item->quantity < $record->quantity) {
                            Notification::make()
                                ->title("Not enough stock available. Only {$item->quantity} left.")
                                ->danger()
                                ->send();
                            return;
                        }

                        $item->quantity -= $record->quantity;
                        $item->save();

                        $record->status = 'issued';
                        $record->action_date = now();
                        $record->save();

                        Notification::make()
                            ->title("Items issued successfully.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->label('Update Return')
                    ->visible(fn ($record) => $record->status === 'issued')
                    ->mutateFormDataUsing(function (array $data, $record) {
                        if ($data['returned_quantity'] > $record->quantity) {
                            Notification::make()
                                ->title("Returned quantity cannot exceed issued quantity.")
                                ->danger()
                                ->send();
                            $data['returned_quantity'] = $record->returned_quantity; // Reset to previous
                        } else {
                            if (in_array($data['status'], ['returned', 'damaged', 'lost'])) {
                                $item = Item::find($record->item_id);
                                if ($data['status'] === 'returned') {
                                    $item->quantity += $data['returned_quantity'];
                                }
                                $item->save();
                            }
                        }

                        return $data;
                    }),
            ]);
    }

    public static function getPages(): array
{
    return [
        'index' => \App\Filament\Resources\StockMovementResource\Pages\ListStockMovements::route('/'),
    ];
}



}
