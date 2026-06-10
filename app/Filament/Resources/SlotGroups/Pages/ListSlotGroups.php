<?php

namespace App\Filament\Resources\SlotGroups\Pages;

use App\Filament\Resources\SlotGroups\SlotGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSlotGroups extends ListRecords
{
    protected static string $resource = SlotGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
