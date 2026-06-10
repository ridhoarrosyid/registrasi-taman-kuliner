<?php

namespace App\Filament\Resources\SlotGroups\Pages;

use App\Filament\Resources\SlotGroups\SlotGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSlotGroup extends EditRecord
{
    protected static string $resource = SlotGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
