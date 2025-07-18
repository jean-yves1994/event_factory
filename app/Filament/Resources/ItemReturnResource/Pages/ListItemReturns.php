<?php

namespace App\Filament\Resources\ItemReturnResource\Pages;

use App\Filament\Resources\ItemReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListItemReturns extends ListRecords
{
    protected static string $resource = ItemReturnResource::class;

    public function getTitle(): string
    {
        return 'Items return';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
