<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemReturnResource\Pages;
use App\Models\ItemReturn;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;

class ItemReturnResource extends Resource
{
    protected static ?string $model = ItemReturn::class;

    protected static ?string $navigationLabel = 'Item Returns Report';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Reports';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(function () {
                // Group returns by event and join events table to get event details
                return ItemReturn::selectRaw('
                    stock_movements.requisition_id,
                    requisitions.event_id,
                    events.event_name,
                    events.event_date,
                    SUM(item_returns.good_condition) as total_good,
                    SUM(item_returns.damaged_quantity) as total_damaged,
                    SUM(item_returns.lost_quantity) as total_lost,
                    MAX(item_returns.created_at) as returned_at
                ')
                ->join('stock_movements', 'item_returns.stock_movement_id', '=', 'stock_movements.id')
                ->join('requisitions', 'stock_movements.requisition_id', '=', 'requisitions.id')
                ->join('events', 'requisitions.event_id', '=', 'events.id')
                ->groupBy('requisitions.event_id', 'stock_movements.requisition_id', 'events.event_name', 'events.event_date');
            })
            ->columns([
                TextColumn::make('event_name')->label('Event Name'),
                TextColumn::make('event_date')->label('Event Date')->date(),
                TextColumn::make('total_good')->label('Good'),
                TextColumn::make('total_damaged')->label('Damaged'),
                TextColumn::make('total_lost')->label('Lost'),
                TextColumn::make('returned_at')->label('Last Returned')->dateTime(),
            ])
            ->actions([
                Action::make('view')
                    ->label('View Items')
                    ->url(fn ($record) => static::getUrl('view', ['event' => $record->event_id]))
            ])
            ->defaultSort('returned_at', 'desc');
    }

public static function getPages(): array
{
    return [
        'index' => Pages\ListItemReturns::route('/'),
        'view' => Pages\ViewItemReturnsByEvent::route('/view/{event}'),
    ];
}
}
