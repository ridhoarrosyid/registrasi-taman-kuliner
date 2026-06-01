<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Berdasarkan peta, kita generate beberapa data awal untuk testing
        $blocks = [
            'B' => 15, // Generate B1 sampai B15
            'C' => 15, // Generate C1 sampai C15
            'D' => 10,
            'E' => 10,
            'F' => 10,
        ];

        foreach ($blocks as $blockName => $count) {
            for ($i = 1; $i <= $count; $i++) {
                Slot::create([
                    'slot_number' => $blockName . $i, // Menghasilkan "B1", "B2", dst.
                    'status' => 'available',
                ]);
            }
        }

        // Tambahkan beberapa lapak dengan status berbeda untuk melihat warna UI
        Slot::create(['slot_number' => 'B16', 'status' => 'reserved']);
        Slot::create(['slot_number' => 'C16', 'status' => 'occupied']);
    }
}
