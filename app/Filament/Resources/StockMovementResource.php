<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationLabel = 'Stock Movements';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Stock Management';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('event_name_alias')
    ->label('Event Name')
    ->sortable()
    ->getStateUsing(fn ($record) => $record->event_name_alias),

                TextColumn::make('event_date')
                    ->label('Event Date')
                    ->sortable()
                    ->getStateUsing(fn ($record) => Carbon::parse($record->event_date)->format('M d, Y')),

                TextColumn::make('total_items')
                    ->label('Total Items')
                    ->getStateUsing(fn ($record) => $record->total_items),
            ])
            ->actions([
                Tables\Actions\Action::make('viewItems')
                    ->label('View Items')
                    ->url(fn ($record) => route('filament.admin.resources.stock-movements.view-event-stock-movements', ['record' => $record->event_id]))
                    ->icon('heroicon-o-eye'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
            'view-event-stock-movements' => Pages\ViewEventStockMovements::route('/view-event-stock-movements/{record}'),
        ];
    }
}
