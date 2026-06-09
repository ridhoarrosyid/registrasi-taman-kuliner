<?php

namespace App\Filament\Resources\LayoutMaps\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LayoutMapForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image_path')
                    ->label('Gambar Peta Layout')
                    ->image()
                    ->disk('public')
                    ->directory('layout_maps') // Menyimpan di storage/app/public/layout_maps
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktifkan Peta Ini')
                    ->default(true),
            ]);
    }
}
