<?php

namespace App\Filament\Resources\LayoutMaps\Pages;

use App\Filament\Resources\LayoutMaps\LayoutMapResource;
use App\Models\LayoutMap;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLayoutMaps extends ListRecords
{
    protected static string $resource = LayoutMapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->disabled(fn(): bool => LayoutMap::count() >= 5),
        ];
    }
}
