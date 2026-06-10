<?php

namespace App\Filament\Resources\SlotGroups\Schemas;

use App\Models\SlotGroup;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SlotGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Blok / Grup')
                            ->placeholder('Contoh: Blok A, Area Barat, dsb.')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            // Otomatis membuat slug ketika admin selesai mengetik nama
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->disabled() // Dimatikan agar tidak diubah manual secara sembarangan
                            ->dehydrated() // Tetap dikirim ke database saat disimpan
                            ->unique(SlotGroup::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2)
            ]);
    }
}
