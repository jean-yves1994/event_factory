<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Item;
use App\Models\StockMovement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsWidgets extends BaseWidget
{
    
    protected function getStats(): array
    {
        return [
            //Total Events
            Stat::make('All Events',Event::count())
            ->description('Total number of events created')
            ->descriptionIcon('heroicon-o-calendar')
            ->color('primary')
            ->color('success')
            ->icon('heroicon-o-calendar')
            ->chart([1,3,5,10,20,40])
            ,
            //Total Items where status is available
            Stat::make('Available Items', Item::where('status', 'available')->count())
            ->description('Total number of items available')
            ->descriptionIcon('heroicon-o-arrow-trending-up')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->chart([2,4,6,8,10,30,50,100]),


            //Total Items where status is damaged
            Stat::make('Damaged Items', Item::where('status', 'damaged')->count())
            ->description('Total number of items damaged')
            ->descriptionIcon('heroicon-o-shield-exclamation')
            ->color('warning')
            ->icon('heroicon-o-x-circle')
            ->chart([1,2,3,4,5,6,10,20,30,40,100]),


            //Total Items where status is lost
            Stat::make('Lost Items', Item::where('status', 'lost')->count())
            ->description('Total number of items lost')
            ->descriptionIcon('heroicon-o-exclamation-triangle')
            ->color('danger')
            ->icon('heroicon-o-trash')
            ->chart([1,2,3,4,5,6,10,20,30,40,100]),

            //Total Items where status is in overdue
            Stat::make('Overdue Items', StockMovement::whereIn('status', ['issued', 'partially_returned'])
            ->whereHas('requisition', function ($query) {
            $query->whereDate('expected_return_date', '<', now());
            })->count())
            ->description('Total number of overdue items')
            ->descriptionIcon('heroicon-o-clock')
            ->color('danger')
            ->icon('heroicon-o-clock')
            ->chart([1, 2, 3, 4, 5, 6, 10, 20, 30, 40, 100]),

            //Total Items that are in good condition
            /* Stat::make('Returned in Good Condition', StockMovement::sum('good_condition'))
            ->description('Total items returned in good condition')
            ->descriptionIcon('heroicon-o-check-circle')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->chart([10, 20, 30, 40, 50, 60]), */
            
        ];
    }
}
