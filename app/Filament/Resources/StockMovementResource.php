<?php

namespace App\Filament\Resources;

use App\Models\Item;
use App\Models\StockMovement;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $navigationLabel = 'Stock Movements';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Stock Management';


    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('status')
                ->options([
                    'returned' => 'Returned',
                    'damaged' => 'Damaged',
                    'lost' => 'Lost',
                ])
                ->visible(fn($record) => $record && $record->status === 'issued'),

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
                    ->getStateUsing(fn($record) => $record->good_condition + $record->damaged_quantity),

                TextColumn::make("remaining_quantity")
                    ->label("Remaining")
                    ->getStateUsing(function ($record) {
                        $remaining = $record->quantity
                            - ($record->good_condition + $record->damaged_quantity + $record->lost_quantity);

                        return $remaining < 0 ? '⚠️ ' . $remaining : $remaining;
                    })
                    ->color(function ($record) {
                        $remaining = $record->quantity
                            - ($record->good_condition + $record->damaged_quantity + $record->lost_quantity);
                        return $remaining < 0 ? 'danger' : null;
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'returned',
                        'danger' => ['damaged', 'lost'],
                        'warning' => 'issued',
                        'gray' => 'pending',
                        'info' => 'partially_returned',
                    ])
                    ->formatStateUsing(function ($state, $record) {
                        $totalReturned = $record->good_condition + $record->damaged_quantity;
                        $totalLost = $record->lost_quantity;
                        $totalHandled = $totalReturned + $totalLost;
                        $issued = $record->quantity;
                    
                        // If nothing has been handled yet, keep it as 'Pending'
                        if ($totalHandled === 0) {
                            return 'Pending';
                        }
                    
                        if ($totalLost === $issued) {
                            return 'Lost';
                        } elseif ($totalHandled === $issued && $totalLost === 0) {
                            return 'Returned';
                        } elseif ($totalHandled < $issued) {
                            return 'Partially Returned';
                        } else {
                            return ucfirst($state);
                        }
                    }),
                    

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
                Tables\Actions\EditAction::make()
                    ->label('Update Return')
                    ->visible(fn($record) =>
                        in_array($record->status, ['issued', 'partially_returned']) &&
                        ($record->quantity > ($record->good_condition + $record->damaged_quantity + $record->lost_quantity))
                    )
                    ->form([
                        TextInput::make('good_condition')
                            ->label('Returned (Good)')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn($record) => $record->good_condition)
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, Closure $fail) use ($get) {
                                        $damaged = $get('damaged_quantity') ?? 0;
                                        $lost = $get('lost_quantity') ?? 0;
                                        $total = $value + $damaged + $lost;
                                        $issuedQuantity = $get('quantity') ?? null;

                                        if ($issuedQuantity !== null && $total > $issuedQuantity) {
                                            $fail('Returned + damaged + lost items cannot exceed issued quantity.');
                                        }
                                    };
                                },
                            ]),

                        TextInput::make('damaged_quantity')
                            ->label('Returned (Damaged)')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn($record) => $record->damaged_quantity)
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, Closure $fail) use ($get) {
                                        $good = $get('good_condition') ?? 0;
                                        $lost = $get('lost_quantity') ?? 0;
                                        $total = $good + $value + $lost;
                                        $issuedQuantity = $get('quantity') ?? null;

                                        if ($issuedQuantity !== null && $total > $issuedQuantity) {
                                            $fail('Returned + damaged + lost items cannot exceed issued quantity.');
                                        }
                                    };
                                },
                            ]),

                        TextInput::make('lost_quantity')
                            ->label('Lost')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn($record) => $record->lost_quantity)
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, Closure $fail) use ($get) {
                                        $good = $get('good_condition') ?? 0;
                                        $damaged = $get('damaged_quantity') ?? 0;
                                        $total = $good + $damaged + $value;
                                        $issuedQuantity = $get('quantity') ?? null;

                                        if ($issuedQuantity !== null && $total > $issuedQuantity) {
                                            $fail('Returned + damaged + lost items cannot exceed issued quantity.');
                                        }
                                    };
                                },
                            ]),

                        TextInput::make('quantity')
                            ->hidden()
                            ->default(fn($record) => $record->quantity),
                    ])
                    ->mutateFormDataUsing(function (array $data, $record) {
                        $record->item->quantity += ($data['good_condition'] - $record->good_condition)
                                        + ($data['damaged_quantity'] - $record->damaged_quantity);
                        $record->item->save();
                    
                        $totalHandled = $data['good_condition'] + $data['damaged_quantity'] + $data['lost_quantity'];
                    
                        // Only update status if the item was issued
                        if ($record->status === 'issued' || $record->status === 'partially_returned') {
                            if ($data['lost_quantity'] === $record->quantity) {
                                $data['status'] = 'lost';
                            } elseif ($totalHandled === $record->quantity && $data['lost_quantity'] === 0) {
                                $data['status'] = 'returned';
                            } elseif ($totalHandled > 0 && $totalHandled < $record->quantity) {
                                $data['status'] = 'partially_returned';
                            }
                        }
                    
                        return $data;
                    })
                    
                    ->after(function ($record) {
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
