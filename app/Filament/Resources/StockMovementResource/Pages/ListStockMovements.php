<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\StockMovement;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;
     public function getTitle(): string
    {
        return 'Stock Movement';  // Hide page header title
    }
    

    protected function getTableQuery(): Builder
    {
        return StockMovement::query()
    ->selectRaw('
        MIN(stock_movements.id) as id,
        requisitions.event_id,
        events.event_name AS event_name_alias,
        events.event_date AS event_date_alias,
        COUNT(*) as total_items
    ')
    ->join('requisitions', 'stock_movements.requisition_id', '=', 'requisitions.id')
    ->join('events', 'requisitions.event_id', '=', 'events.id')
    ->groupBy('requisitions.event_id', 'events.event_name', 'events.event_date')
    ->orderBy('events.event_date', 'desc');
    }
}
