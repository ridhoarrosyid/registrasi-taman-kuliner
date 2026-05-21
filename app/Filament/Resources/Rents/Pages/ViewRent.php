<?php

namespace App\Filament\Resources\Rents\Pages;

use App\Filament\Resources\Rents\RentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRent extends ViewRecord
{
    protected static string $resource = RentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
