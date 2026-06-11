<?php

namespace App\Filament\Resources\Rents\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class RentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pemilik')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slot.slotGroup.name')
                    ->label('Blok Tenant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slot.slot_number')
                    ->label('Lapak')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('business_name')
                    ->label('Nama Usaha')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending_payment', 'payment_failed' => 'gray',
                        'pending_verification', 'renewal_pending_verification' => 'warning',
                        'active' => 'success',
                        'rejected', 'expired' => 'danger',
                    }),

                TextColumn::make('end_date')
                    ->label('Berakhir Pada')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->label('Eksport ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename('Laporan_Penyewaan_BPU_' . date('Y-m-d'))
                        ]),
                ]),
            ]);
    }
}
