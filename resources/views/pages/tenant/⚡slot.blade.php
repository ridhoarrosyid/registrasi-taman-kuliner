<?php

use App\Models\Rent;
use App\Models\Slot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    // State (Variabel) untuk mengontrol UI
    public $activeTab;
    public $showModal = false;

    // State untuk Form Pendaftaran
    public $selectedSlot = null;
    public $businessName = '';

    // Dijalankan pertama kali saat komponen dimuat
    public function mount()
    {
        // Mengatur tab default ke abjad pertama yang tersedia di database
        $firstSlot = Slot::orderBy('slot_number', 'asc')->first();
        if ($firstSlot) {
            $this->activeTab = strtoupper(substr($firstSlot->slot_number, 0, 1));
        }
    }

    // Fungsi saat lapak warna hijau diklik
    public function selectSlot($slotId)
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $slot = Slot::find($slotId);

        // 2. Proteksi: pastikan lapak masih benar-benar available
        if ($slot && $slot->status === 'available') {
            $this->selectedSlot = $slot;
            $this->showModal = true;
        }
    }

    // Fungsi untuk menutup modal dan mereset form
    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedSlot = null;
        $this->businessName = '';
        $this->resetValidation();
    }

    // Fungsi memproses pesanan saat tombol "Pesan" ditekan
    public function submitRent()
    {
        // Validasi input
        $this->validate([
            'businessName' => 'required|min:3|max:255',
        ], [
            'businessName.required' => 'Nama usaha wajib diisi.',
            'businessName.min' => 'Nama usaha minimal 3 karakter.'
        ]);

        // Gunakan DB Transaction agar eksekusi data aman
        DB::transaction(function () {
            // 1. Buat data sewa (Rent)
            Rent::create([
                'user_id' => Auth::id(),
                'slot_id' => $this->selectedSlot->id,
                'business_name' => $this->businessName,
                'status' => 'pending_payment',
                'reserved_until' => now()->addHours(24), // Batas waktu bayar 24 jam
            ]);

            // 2. Ubah status lapak (Slot) menjadi reserved agar tidak bisa diklik orang lain
            $this->selectedSlot->update([
                'status' => 'reserved'
            ]);
        });

        $this->closeModal();
        session()->flash('success', 'Berhasil! Lapak Anda telah diamankan. Silakan segera unggah bukti pembayaran.');
    }

    // Mengirim data ke bagian HTML
    public function with(): array
    {
        $slots = Slot::orderBy('slot_number', 'asc')->get();

        // Mengelompokkan lapak berdasarkan huruf pertama (B, C, D, dst)
        $groupedSlots = $slots->groupBy(function ($slot) {
            return strtoupper(substr($slot->slot_number, 0, 1));
        });

        return [
            'groupedSlots' => $groupedSlots,
            'availableBlocks' => $groupedSlots->keys(),
        ];
    }

    public function render()
    {
        return $this->view()->layout('layouts::app')->title('Pilih Lapak | Taman Kuliner');
    }
};
?>

<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 h-[1000px]" x-data="{ activeTab: '{{ $activeTab }}' }">

    @if (session()->has('success'))
    <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg font-semibold text-center shadow-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900">Denah Lapak Taman Kuliner</h2>
        <p class="text-gray-500 mt-2">Pilih blok area, lalu klik lapak yang tersedia untuk melakukan pendaftaran.</p>
    </div>

    <div class="flex flex-wrap justify-center gap-4 md:gap-8 mb-8 text-sm font-medium bg-white py-3 px-6 rounded-full shadow-sm border border-gray-100">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded-full bg-green-500 shadow-sm"></div> Tersedia
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded-full bg-yellow-400 shadow-sm"></div> Menunggu Pembayaran
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded-full bg-red-500 shadow-sm"></div> Telah Disewa
        </div>
    </div>

    <div class="flex justify-center flex-wrap gap-2 mb-8 border-b border-gray-200 pb-6">
        @foreach($availableBlocks as $block)
        <button
            @click="activeTab = '{{ $block }}'"
            :class="activeTab === '{{ $block }}' ? 'bg-indigo-600 text-white shadow-md transform scale-105' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
            class="px-6 py-2.5 rounded-full font-bold text-sm md:text-base transition-all duration-200">
            Blok {{ $block }}
        </button>
        @endforeach
    </div>

    <div class="relative min-h-[300px]">
        @foreach($groupedSlots as $block => $slotsInBlock)

        <div x-show="activeTab === '{{ $block }}'" style="display: none;" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3 sm:gap-4">
            @foreach($slotsInBlock as $slot)
            <div
                @class([ 'relative p-3 rounded-xl border-4 text-center transition-all duration-200 flex flex-col items-center justify-center aspect-square' , 'border-green-500 bg-green-50 hover:bg-green-500 hover:text-white cursor-pointer shadow-sm hover:shadow-md group'=> $slot->status === 'available',
                'border-yellow-700 bg-yellow-50 opacity-75 cursor-not-allowed' => $slot->status === 'reserved',
                'border-red-700 bg-red-50 opacity-50 cursor-not-allowed' => $slot->status === 'occupied',
                ])
                @if($slot->status === 'available')
                wire:click="selectSlot({{ $slot->id }})"
                @endif
                >
                <span class="block text-xl md:text-2xl font-black {{ $slot->status === 'available' ? 'text-green-700 group-hover:text-white' : 'text-gray-800' }}">
                    {{ $slot->slot_number }}
                </span>
            </div>
            @endforeach
        </div>

        @endforeach
    </div>

    @if($showModal && $selectedSlot)
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-indigo-600 p-5 text-white flex justify-between items-center">
                <h3 class="font-bold text-lg">Konfirmasi Sewa Lapak</h3>
                <button wire:click="closeModal" class="text-indigo-200 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <div class="mb-6 flex justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <span class="text-gray-500 font-medium text-sm uppercase tracking-wide">Nomor Lapak Terpilih</span>
                    <span class="text-4xl font-black text-indigo-600">{{ $selectedSlot->slot_number }}</span>
                </div>

                <form wire:submit.prevent="submitRent">
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Usaha / Bisnis Anda</label>
                        <input
                            type="text"
                            wire:model="businessName"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 focus:outline-none transition-shadow"
                            placeholder="Contoh: Kedai Kopi Senja">
                        @error('businessName') <span class="text-red-500 text-sm mt-2 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-amber-50 text-amber-800 text-sm p-4 rounded-xl mb-6 border border-amber-200">
                        <strong>Perhatian:</strong> Setelah menekan tombol pesan, Anda memiliki waktu <b>24 Jam</b> untuk mengunggah bukti pembayaran di Dasbor Tenant. Lapak akan dilepas kembali jika melewati batas waktu.
                    </div>

                    <div class="flex gap-3">
                        <button type="button" wire:click="closeModal" class="w-1/2 py-3 px-4 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" class="w-1/2 py-3 px-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-colors">Pesan Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>