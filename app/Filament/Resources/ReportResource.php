<?php

namespace App\Filament\Resources;

use App\Models\StockMovement;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ReportResource\Pages;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationLabel = 'Item Reports';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')->label('Item'),

                TextColumn::make('calculated_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (in_array($record->status, ['damaged', 'lost'])) {
                            return ucfirst($record->status);
                        }

                        if (
                            optional($record->requisition)?->expected_return_date &&
                            now()->gt($record->requisition->expected_return_date)
                        ) {
                            return 'Overdue';
                        }

                        return 'Good';
                    })
                    ->color(fn ($state) => match ($state) {
                        'Lost' => 'danger',
                        'Damaged' => 'warning',
                        'Overdue' => 'gray',
                        default => 'success',
                    }),

                TextColumn::make('quantity'),

                TextColumn::make('requisition.event.event_name')->label('Event'),
                TextColumn::make('requisition.event.event_date')->label('Event Date'),
                TextColumn::make('requisition.event.responsible_person_name')->label('Responsible Person'),
                TextColumn::make('requisition.event.responsible_person_phone')->label('Phone'),

                TextColumn::make('action_date')->label('Action Date')->date(),
            ])
            ->filters([
                SelectFilter::make('custom_status')
                ->label('Filter by Status')
                ->options([
                    'damaged' => 'Damaged',
                    'lost' => 'Lost',
                    'overdue' => 'Overdue',
                ])
                ->query(function (Builder $query, $state) {
                    if ($state === 'overdue') {
                        return $query->whereHas('requisition', function ($q) {
                            $q->whereDate('expected_return_date', '<', now());
                        });
                    }
            
                    return $query->where('status', $state);
                }),  
            

                Filter::make('from_date')
                    ->form([
                        DatePicker::make('from'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('action_date', '>=', $data['from']));
                    }),

                Filter::make('to_date')
                    ->form([
                        DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['to'], fn ($q) => $q->whereDate('action_date', '<=', $data['to']));
                    }),
            ])
            ->defaultSort('action_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
        ];
    }
}
