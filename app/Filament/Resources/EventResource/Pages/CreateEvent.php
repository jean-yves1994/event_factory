<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Event;
use App\Models\Item;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function handleRecordCreation(array $data): Event
    {
        // Create the event
        $event = Event::create($data);

        // Create the requisition linked to the event
        $requisition = $event->requisition()->create([
            'event_id' => $event->id,
            'expected_pickup_date' => $data['requisition']['expected_pickup_date'],
            'expected_return_date' => $data['requisition']['expected_return_date'],
            'status' => 'pending',
        ]);

        // Loop through selected items and quantities from the repeater field
        if (isset($data['requisition']['items'])) {
            foreach ($data['requisition']['items'] as $itemData) {
                $item = Item::find($itemData['item_id']);
                $requisition->items()->attach($item, [
                    'quantity' => $itemData['pivot']['quantity'], // Store the quantity in the pivot table
                ]);
            }
        }

        return $event;
    }
}
