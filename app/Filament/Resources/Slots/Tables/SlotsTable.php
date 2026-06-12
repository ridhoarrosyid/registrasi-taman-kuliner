<?php

namespace App\Filament\Resources\Slots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slotGroup.name')
                    ->label('Blok / Group')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slot_number')
                    ->label('Nomor Lapak')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'occupied' => 'danger',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('slot_group_id')
                    ->relationship('slotGroup', 'name')
                    ->label('Saring Berdasarkan Blok'),
                SelectFilter::make('status')
                    ->label('Saring Berdasarkan Status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
