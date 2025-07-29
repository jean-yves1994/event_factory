<?php

namespace App\Filament\Resources\ApprovedEventResource\Pages;

use App\Filament\Resources\ApprovedEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApprovedEvent extends EditRecord
{
    protected static string $resource = ApprovedEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
