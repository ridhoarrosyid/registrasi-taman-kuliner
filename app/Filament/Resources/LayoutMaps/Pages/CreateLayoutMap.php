<?php

namespace App\Filament\Resources\LayoutMaps\Pages;

use App\Filament\Resources\LayoutMaps\LayoutMapResource;
use App\Models\LayoutMap;
use Filament\Resources\Pages\CreateRecord;

class CreateLayoutMap extends CreateRecord
{
    protected static string $resource = LayoutMapResource::class;
}
