<?php

namespace App\Filament\Resources;

use App\Models\ItemReturn;
use App\Models\StockMovement;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $navigationLabel = 'Stock Movements';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Stock Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('action_date')
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

                TextColumn::make('returned_total')
                    ->label('Returned')
                    ->getStateUsing(fn($record) =>
                        ItemReturn::where('stock_movement_id', $record->id)->sum('good_condition') +
                        ItemReturn::where('stock_movement_id', $record->id)->sum('damaged_quantity')
                    ),

                    TextColumn::make('remaining_quantity')
                    ->label('Remaining')
                    ->getStateUsing(function ($record) {
                        $returned = ItemReturn::where('stock_movement_id', $record->id)
                            ->sum(DB::raw('good_condition + damaged_quantity'));
                        $remaining = $record->quantity - $returned;
                        return $remaining < 0 ? '⚠️ ' . $remaining : $remaining;
                    })
                    ->color(function ($record) {
                        $returned = ItemReturn::where('stock_movement_id', $record->id)
                            ->sum(DB::raw('good_condition + damaged_quantity'));
                        return $record->quantity > $returned ? 'danger' : 'success';
                    }),
                

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'returned',
                        'danger' => ['damaged', 'lost'],
                        'warning' => 'issued',
                        'gray' => 'pending',
                        'info' => 'partially_returned',
                    ]),

                TextColumn::make('action_date')->label('Action Date'),

                TextColumn::make('requisition.expected_return_date')
                    ->label('Expected Return')
                    ->date(),

                TextColumn::make('overdue')
                    ->label('Overdue')
                    ->getStateUsing(fn($record) =>
                        now()->greaterThan(Carbon::parse($record->requisition->expected_return_date ?? '')) &&
                        in_array($record->status, ['issued', 'partially_returned']) ? '⚠️ Yes' : 'No'
                    )
                    ->color(fn($record) =>
                        now()->greaterThan(Carbon::parse($record->requisition->expected_return_date ?? '')) &&
                        in_array($record->status, ['issued', 'partially_returned']) ? 'danger' : 'success'
                    ),
            ])
            ->actions([
                EditAction::make()
                    ->label('Update Return')
                    ->visible(fn($record) =>
                        in_array($record->status, ['issued', 'partially_returned'])
                    )
                    ->form([
                        Placeholder::make('issued_quantity')
                            ->label('Issued Quantity')
                            ->content(fn($record) => $record->quantity),
                    
                        Placeholder::make('remaining_quantity')
                            ->label('Remaining Quantity (Not returned)')
                            ->content(function ($record) {
                                $returned = \App\Models\ItemReturn::where('stock_movement_id', $record->id)
                                    ->sum(DB::raw('good_condition + damaged_quantity'));
                                $remaining = $record->quantity - $returned;
                                return $remaining < 0 ? '⚠️ ' . $remaining : $remaining;
                            }),
                    
                        TextInput::make('good_condition')
                            ->label('Returned (Good)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    
                        TextInput::make('damaged_quantity')
                            ->label('Returned (Damaged)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    
                        TextInput::make('lost_quantity')
                            ->label('Lost')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])
                    
                    ->action(function (array $data, $record) {
                        DB::transaction(function () use ($data, $record) {
                            // Insert new return record
                            ItemReturn::create([
                                'stock_movement_id' => $record->id,
                                'good_condition' => $data['good_condition'],
                                'damaged_quantity' => $data['damaged_quantity'],
                                'lost_quantity' => $data['lost_quantity'],
                            ]);

                            // Adjust item quantity
                            $record->item->increment('quantity', $data['good_condition'] + $data['damaged_quantity']);

                            // Calculate total returned
                            $totalReturned = ItemReturn::where('stock_movement_id', $record->id)->sum(DB::raw('good_condition + damaged_quantity + lost_quantity'));
                            $totalLost = ItemReturn::where('stock_movement_id', $record->id)->sum('lost_quantity');

                            // Determine status
                            if ($totalReturned >= $record->quantity) {
                                if ($totalLost === $record->quantity) {
                                    $record->update(['status' => 'lost']);
                                } elseif ($totalLost === 0) {
                                    $record->update(['status' => 'returned']);
                                } else {
                                    $record->update(['status' => 'partially_returned']);
                                }
                            } else {
                                $record->update(['status' => 'partially_returned']);
                            }
                        });

                        Notification::make()
                            ->title('Return updated successfully.')
                            ->success()
                            ->send();
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
