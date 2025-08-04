<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use App\Models\Event;
use App\Models\ItemReturn;
use App\Models\StockMovement;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\TextInput;


class ViewEventStockMovements extends Page implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = StockMovementResource::class;
    protected static string $view = 'filament.resources.stock-movement-resource.pages.view-event-stock-movements';
    
    public ?Event $event;

    public function mount($record): void
    {
        $this->event = Event::findOrFail($record);
    }
     public function getTitle(): string
    {
        return '';  // Hide page header title
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockMovement::query()->whereHas('requisition', fn($q) =>
                    $q->where('event_id', $this->event->id)
                )
            )
            ->columns([
                TextColumn::make('item.name')->label('Item'),
                TextColumn::make('quantity'),
                TextColumn::make('status'),
            ])
            ->actions([
                Action::make('return')
                    ->label('Return')
                    ->visible(fn($record) => in_array($record->status, ['issued', 'partially_returned']))
                    ->form([
                        TextInput::make('good_condition')->numeric()->required(),
                        TextInput::make('damaged_quantity')->numeric()->required(),
                        TextInput::make('lost_quantity')->numeric()->required(),
                    ])
                    ->action(function (array $data, $record) {
                        ItemReturn::create([
                            'stock_movement_id' => $record->id,
                            'good_condition' => $data['good_condition'],
                            'damaged_quantity' => $data['damaged_quantity'],
                            'lost_quantity' => $data['lost_quantity'],
                        ]);

                        $record->item->increment('quantity', $data['good_condition'] + $data['damaged_quantity']);

                        $totalReturned = ItemReturn::where('stock_movement_id', $record->id)
                            ->sum(DB::raw('good_condition + damaged_quantity + lost_quantity'));

                        $totalLost = ItemReturn::where('stock_movement_id', $record->id)->sum('lost_quantity');

                        if ($totalReturned >= $record->quantity) {
                            $record->status = $totalLost === $record->quantity ? 'lost' : ($totalLost ? 'partially_returned' : 'returned');
                        } else {
                            $record->status = 'partially_returned';
                        }

                        $record->save();
                    }),
            ]);
    }
}
