<?php

namespace App\Filament\Resources\Rents;

use App\Filament\Resources\Rents\Pages\CreateRent;
use App\Filament\Resources\Rents\Pages\EditRent;
use App\Filament\Resources\Rents\Pages\ListRents;
use App\Filament\Resources\Rents\Pages\ViewRent;
use App\Filament\Resources\Rents\Schemas\RentForm;
use App\Filament\Resources\Rents\Schemas\RentInfolist;
use App\Filament\Resources\Rents\Tables\RentsTable;
use App\Models\Rent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RentResource extends Resource
{
    protected static ?string $model = Rent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'business_name';

    public static function form(Schema $schema): Schema
    {
        return RentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentsTable::configure($table);
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
            'index' => ListRents::route('/'),
            'create' => CreateRent::route('/create'),
            'view' => ViewRent::route('/{record}'),
            'edit' => EditRent::route('/{record}/edit'),
        ];
    }
}
