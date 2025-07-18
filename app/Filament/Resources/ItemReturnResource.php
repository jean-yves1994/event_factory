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
            ->columns([
                TextColumn::make('stockMovement.item.name')->label('Item Name'),
                TextColumn::make('stockMovement.requisition.event.event_name')->label('Event'),
                TextColumn::make('stockMovement.requisition.event.event_date')->label('Event Date'),
                TextColumn::make('good_condition')->label('Good'),
                TextColumn::make('damaged_quantity')->label('Damaged'),
                TextColumn::make('lost_quantity')->label('Lost'),
                TextColumn::make('created_at')->label('Returned On')->dateTime(),
            ])->headerActions([
                Action::make('Export PDF')
                    ->label('Export PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()->get();
            
                        $pdf = Pdf::loadView('exports.item-returns', [
                            'returns' => $records
                        ]);
            
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'item-returns-report.pdf'
                        );
                    }),
            ])
            ->filters([
                SelectFilter::make('item_id')
                    ->label('Item')
                    ->relationship('item', 'name'),
            
                SelectFilter::make('stock_movement_id')
                    ->label('Event')
                    ->relationship('stockMovement.requisition.event', 'event_name'),
            
                // Date range filter
                Filter::make('Returned Date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            
                // Show only damaged returns
                Filter::make('Only Damaged Items')
                    ->query(fn ($query) => $query->where('damaged_quantity', '>', 0)),
            
                // Show only lost returns
                Filter::make('Only Lost Items')
                    ->query(fn ($query) => $query->where('lost_quantity', '>', 0)),
            ])
            
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemReturns::route('/'),
        ];
    }
}
