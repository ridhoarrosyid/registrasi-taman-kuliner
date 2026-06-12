<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\ManageSettings;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $recordTitleAttribute = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $modelLabel = 'Pengaturan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('whatsapp_number')
                    ->label('Nomor WhatsApp (Awali dengan 62)')
                    ->placeholder('Contoh: 6281234567890')
                    ->required()
                    ->numeric()
                    ->prefix('+'),
                TextInput::make('rental_price')
                    ->label('Harga Sewa Lapak (Flat)')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Pengaturan')
            ->columns([
                TextColumn::make('whatsapp_number')
                    ->label('Nomor WhatsApp Admin BPU')
                    ->searchable(),
                TextColumn::make('rental_price')
                    ->label('Harga Sewa Lapak')
                    ->money('IDR', locale: 'id'),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y, H:i'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSettings::route('/'),
        ];
    }
}
