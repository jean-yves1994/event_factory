<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use App\Models\Requisition;
use Filament\Resources\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;


class RequisitionStockMovements extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = StockMovementResource::class;
    protected static string $view = 'filament.resources.stock-movement-resource.pages.requisition-stock-movements';

    public Requisition $requisition;

    public function mount($requisition): void
    {
        $this->requisition = Requisition::with('event')->findOrFail($requisition);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->requisition->stockMovements()->with('item'))
            ->columns([
                TextColumn::make('item.name')->label('Item'),
                TextColumn::make('quantity'),
                TextColumn::make('returned_quantity'),
                TextColumn::make('status')->badge(),
                TextColumn::make('expected_return_date'),
                TextColumn::make('action_date'),
                TextColumn::make('remaining_quantity')
                    ->getStateUsing(fn ($record) => max($record->quantity - $record->returned_quantity, 0)),
            ]);
    }

    public function getTitle(): string
    {
        return "Items for Event: {$this->requisition->event->name}";
    }
}
