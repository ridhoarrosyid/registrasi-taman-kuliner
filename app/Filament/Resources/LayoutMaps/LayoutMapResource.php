<?php

namespace App\Filament\Resources\LayoutMaps;

use App\Filament\Resources\LayoutMaps\Pages\CreateLayoutMap;
use App\Filament\Resources\LayoutMaps\Pages\EditLayoutMap;
use App\Filament\Resources\LayoutMaps\Pages\ListLayoutMaps;
use App\Filament\Resources\LayoutMaps\Schemas\LayoutMapForm;
use App\Filament\Resources\LayoutMaps\Tables\LayoutMapsTable;
use App\Models\LayoutMap;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LayoutMapResource extends Resource
{
    protected static ?string $model = LayoutMap::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Peta Tenant';

    public static function form(Schema $schema): Schema
    {
        return LayoutMapForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LayoutMapsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLayoutMaps::route('/'),
            'create' => CreateLayoutMap::route('/create'),
            'edit' => EditLayoutMap::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return LayoutMap::count() < 2;
    }
}
