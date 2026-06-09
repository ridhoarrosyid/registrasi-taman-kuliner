<?php

namespace App\Filament\Resources\LayoutMaps\Pages;

use App\Filament\Resources\LayoutMaps\LayoutMapResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLayoutMap extends EditRecord
{
    protected static string $resource = LayoutMapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
