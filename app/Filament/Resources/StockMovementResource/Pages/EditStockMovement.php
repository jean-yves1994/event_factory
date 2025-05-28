<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockMovement extends EditRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // app/Filament/Resources/StockMovementResource/Pages/EditStockMovement.php

protected function mutateFormDataBeforeSave(array $data): array
{
    $originalStatus = $this->record->status;
    $originalQty = $this->record->quantity;
    $newStatus = $data['status'];
    $newQty = $data['quantity'];

    $item = $this->record->item;

    // Rollback original quantity impact
    if ($originalStatus === 'returned') {
        $item->decrement('quantity', $originalQty);
    } elseif ($originalStatus === 'lost') {
        $item->increment('quantity', $originalQty);
    }

    // Apply new quantity impact
    if ($newStatus === 'returned') {
        $item->increment('quantity', $newQty);
    } elseif ($newStatus === 'lost') {
        $item->decrement('quantity', $newQty);
    }

    return $data;
}

}
