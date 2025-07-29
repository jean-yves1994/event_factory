<?php

namespace App\Filament\Resources\ItemReturnResource\Pages;

use App\Filament\Resources\ItemReturnResource; // ✅ Add this
use App\Models\ItemReturn;
use App\Models\Event;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Actions\ButtonAction;


class ViewItemReturnsByEvent extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = ItemReturnResource::class;

    protected static string $view = 'filament.resources.item-return-resource.pages.view-item-returns-by-event';

    public ?Event $event;

    public function mount(Event $event)
{
    $this->event = $event;
}

    protected function getTableQuery()
    {
        return ItemReturn::whereHas('stockMovement.requisition', function ($q) {
            $q->where('event_id', $this->event->id);
        })->with('stockMovement.item');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('stockMovement.item.name')->label('Item Name'),
            TextColumn::make('good_condition')->label('Good'),
            TextColumn::make('damaged_quantity')->label('Damaged'),
            TextColumn::make('lost_quantity')->label('Lost'),
            TextColumn::make('created_at')->label('Returned On')->dateTime(),
        ];
    }
    public function getTitle(): string
    {
        return $this->event->event_name;
    }


    
}
