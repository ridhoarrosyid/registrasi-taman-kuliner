<?php

namespace App\Filament\Resources\SlotGroups;

use App\Filament\Resources\SlotGroups\Pages\CreateSlotGroup;
use App\Filament\Resources\SlotGroups\Pages\EditSlotGroup;
use App\Filament\Resources\SlotGroups\Pages\ListSlotGroups;
use App\Filament\Resources\SlotGroups\Schemas\SlotGroupForm;
use App\Filament\Resources\SlotGroups\Tables\SlotGroupsTable;
use App\Models\SlotGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SlotGroupResource extends Resource
{
    protected static ?string $model = SlotGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Grup Tenant';

    public static function form(Schema $schema): Schema
    {
        return SlotGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SlotGroupsTable::configure($table);
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
            'index' => ListSlotGroups::route('/'),
            'create' => CreateSlotGroup::route('/create'),
            'edit' => EditSlotGroup::route('/{record}/edit'),
        ];
    }
}
