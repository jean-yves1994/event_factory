<?php

namespace App\Filament\Resources\ApprovedEventResource\Pages;

use App\Filament\Resources\ApprovedEventResource;
use App\Models\StockMovement;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Actions\Action as PageAction;

class ViewApprovedEvent extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ApprovedEventResource::class;

    public function getView(): string
    {
        return 'internalPages::view-approved-event';
    }
    
    public function getTitle(): string
    {
    return 'Approved Stock for Event: ' . ($this->record->event_name ?? 'Unknown');
    }

public function getHeaderActions(): array
{
    return [
        \Filament\Actions\Action::make('print_report')
            ->label('Print Gate Pass')
            ->icon('heroicon-o-printer')
            ->url(route('approved-events.report', $this->record))
            ->openUrlInNewTab(),
    ];
}


    public function table(Table $table): Table
    {
        
        return $table
            ->query(
                StockMovement::query()->whereHas('requisition', function ($query) {
                    $query->where('event_id', $this->record->id)
                          ->where('status', 'approved');
                })
            )
            ->columns([
                TextColumn::make('item.name')->label('Item'),
                TextColumn::make('quantity')->label('Requested'),
                TextColumn::make('action_date')->label('Action Date'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'returned',
                        'danger' => ['damaged', 'lost'],
                        'warning' => 'issued',
                        'gray' => 'pending',
                    ])->label('Status'),
            ])
            ->actions([
                Action::make('issue')
                    ->label('Issue items')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $item = $record->item;

                        if ($item->quantity < $record->quantity) {
                            Notification::make()
                                ->title("Not enough stock. Only {$item->quantity} available.")
                                ->danger()
                                ->send();
                            return;
                        }

                        $item->quantity -= $record->quantity;
                        $item->save();

                        $record->status = 'issued';
                        $record->action_date = now();
                        $record->save();

                        Notification::make()
                            ->title('Item issued.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    
}
