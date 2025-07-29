<?php

namespace App\Filament\Resources\ItemReturnResource\Pages;

use App\Filament\Resources\ItemReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemReturn extends EditRecord
{
    protected static string $resource = ItemReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
