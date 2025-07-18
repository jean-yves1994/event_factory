<?php

namespace App\Filament\Resources\ApprovedEventResource\Pages;

use App\Filament\Resources\ApprovedEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApprovedEvents extends ListRecords
{
    protected static string $resource = ApprovedEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
