<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['requisition.event', 'item']) // eager load relationships
            ->where(function ($query) {
                $query->whereIn('stock_movements.status', ['damaged', 'lost'])
                    ->orWhere(function ($query) {
                        $query->whereNull('stock_movements.action_date')
                            ->whereHas('requisition', function ($q) {
                                $q->whereDate('expected_return_date', '<', now());
                            });
                    });
            });
    }
}
