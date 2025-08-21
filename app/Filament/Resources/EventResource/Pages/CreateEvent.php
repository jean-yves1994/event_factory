<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Event;
use App\Models\Item;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequisitionCreatedMail;
use App\Models\User;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    public array $addedItems = [];

    protected function getListeners(): array
    {
        return ['updateItems' => 'updateItemsField'];
    }

    public function updateItemsField($addedItems): void
    {
        $this->addedItems = $addedItems;
    }

    protected function handleRecordCreation(array $data): Event
{
    $event = Event::create($data);

    $requisition = $event->requisition()->create([
        'event_id' => $event->id,
        'expected_pickup_date' => $data['requisition']['expected_pickup_date'] ?? null,
        'expected_return_date' => $data['requisition']['expected_return_date'] ?? null,
        'status' => 'pending',
    ]);

    // Attach items
    if (isset($data['requisition']['items'])) {
        foreach ($data['requisition']['items'] as $itemData) {
            $item = Item::find($itemData['item_id']);
            $requisition->items()->attach($item, [
                'quantity' => $itemData['pivot']['quantity'],
            ]);
        }
    } elseif (!empty($this->addedItems)) {
        foreach ($this->addedItems as $itemData) {
            if (isset($itemData['item_id'], $itemData['quantity'])) {
                $requisition->items()->attach($itemData['item_id'], [
                    'quantity' => $itemData['quantity'],
                ]);
            }
        }
    }

    // ✅ Send email to admins
    try {
        $admins = User::role('admin')->get(); // if using spatie/laravel-permission
        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new RequisitionCreatedMail($event));
        }
    } catch (\Exception $e) {
        \Log::error('Failed to send requisition email: ' . $e->getMessage());
    }

    return $event;
}


    public function mount(): void
    {
        parent::mount();
    }
}
