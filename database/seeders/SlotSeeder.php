<?php

namespace Database\Seeders;

use App\Models\Slot;
use App\Models\SlotGroup;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definisi Struktur Lapak berdasarkan Blok
        $struktur = [
            'Blok A' => [
                // Anda tidak menyebutkan rentang untuk Blok A, jadi sementara saya kosongkan.
                // Jika ada, Anda bisa menambahkannya seperti format di bawah ini.
            ],
            'Blok B' => [
                ['prefix' => 'b.', 'start' => 1, 'end' => 10],
                ['prefix' => '', 'start' => 1, 'end' => 30],
            ],
            'Blok C' => [
                ['prefix' => 'c.', 'start' => 1, 'end' => 10],
                ['prefix' => '', 'start' => 1, 'end' => 31],
            ],
            'Blok D' => [
                ['prefix' => 'd.', 'start' => 1, 'end' => 10],
                ['prefix' => '', 'start' => 1, 'end' => 34],
            ],
            'Blok E' => [
                ['prefix' => 'e.', 'start' => 1, 'end' => 10],
                ['prefix' => '', 'start' => 1, 'end' => 34],
            ],
            'Blok F' => [
                ['prefix' => 'f.', 'start' => 1, 'end' => 16],
                ['prefix' => '', 'start' => 1, 'end' => 37],
            ],
        ];

        foreach ($struktur as $namaBlok => $konfigurasi) {
            // 1. Buat atau ambil Grup Slot
            $group = SlotGroup::firstOrCreate(
                ['slug' => Str::slug($namaBlok)],
                ['name' => $namaBlok]
            );

            // 2. Siapkan Array untuk Insert massal agar jauh lebih cepat
            $slotsToInsert = [];

            foreach ($konfigurasi as $rentang) {
                for ($i = $rentang['start']; $i <= $rentang['end']; $i++) {
                    $slotsToInsert[] = [
                        'slot_group_id' => $group->id,
                        'slot_number'   => $rentang['prefix'] . $i,
                        'status'        => 'available',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }

            // 3. Masukkan data lapak ke database
            if (!empty($slotsToInsert)) {
                Slot::insert($slotsToInsert);
            }
        }

        $this->command->info('Berhasil! Semua grup dan lapak telah berhasil dibuat.');
    }
}
