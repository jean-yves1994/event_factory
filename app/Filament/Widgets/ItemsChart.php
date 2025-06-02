<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ItemsChart extends ChartWidget
{
    protected static ?string $heading = 'Items Created per Month';

    protected function getData(): array
    {
        // Query to get count of items grouped by month
        $items = Item::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Generate data for all 12 months
        $data = [];
        $labels = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];

        for ($i = 1; $i <= 12; $i++) {
            $data[] = $items->get($i, 0); // If no data for the month, use 0
        }

        return [
            'datasets' => [
                [
                    'label' => 'Items Created',
                    'data' => $data,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#0369a1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // You can also use 'bar', 'pie', etc.
    }
}
