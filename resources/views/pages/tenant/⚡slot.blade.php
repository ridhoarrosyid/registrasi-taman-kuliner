<?php

use App\Models\LayoutMap;
use App\Models\Rent;
use App\Models\Slot;
use App\Models\SlotGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public $activeTab;
    public $selectedSlot = null;
    public $businessName = '';
    public $layoutMaps;

    // Properti baru untuk mengecek batas limit
    public $userRentCount = 0;
    public $hasReachedLimit = false;

    public function mount()
    {
        $this->layoutMaps = LayoutMap::where('is_active', true)->latest()->take(5)->get();

        $firstGroup = SlotGroup::whereHas('slots')->first();
        if ($firstGroup) {
            $this->activeTab = $firstGroup->id;
        }

        // Hitung lapak yang sudah dipesan oleh tenant (Status: pending_payment, pending_verification, atau active)
        if (Auth::check() && Auth::user()->role === 'tenant') {
            $this->userRentCount = Rent::where('user_id', Auth::id())
                ->whereIn('status', ['pending_payment', 'pending_verification', 'active', 'renewal_pending_verification'])
                ->count();

            // Jika sudah 2 atau lebih, ubah status menjadi true
            $this->hasReachedLimit = $this->userRentCount >= 2;
        }
    }

    public function selectSlot($slotId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'tenant') {
            session()->flash('error', 'Akun Admin tidak dapat digunakan untuk memesan lapak.');
            return;
        }

        $slot = Slot::with('slotGroup')->find($slotId);

        if ($slot && $slot->status === 'available') {
            $this->selectedSlot = $slot;
        }
    }

    public function cancelSelection()
    {
        $this->selectedSlot = null;
        $this->businessName = '';
        $this->resetValidation();
    }

    public function submitRent()
    {
        // Perlindungan ganda di backend agar tidak bisa di-bypass
        if ($this->hasReachedLimit) {
            session()->flash('error', 'Anda telah mencapai batas maksimal penyewaan (2 lapak).');
            return;
        }

        $this->validate([
            'businessName' => 'required|min:3|max:255',
        ], [
            'businessName.required' => 'Nama usaha wajib diisi.',
            'businessName.min' => 'Nama usaha minimal 3 karakter.'
        ]);

        DB::transaction(function () {
            Rent::create([
                'user_id' => Auth::id(),
                'slot_id' => $this->selectedSlot->id,
                'business_name' => $this->businessName,
                'status' => 'pending_payment',
                'reserved_until' => now()->addHours(24),
            ]);

            $this->selectedSlot->update(['status' => 'reserved']);
        });

        // Tambahkan hitungan secara real-time setelah sukses memesan
        $this->userRentCount++;
        $this->hasReachedLimit = $this->userRentCount >= 2;

        $this->cancelSelection();
        session()->flash('success', 'Berhasil! Lapak Anda telah diamankan. Silakan segera unggah bukti pembayaran di Dasbor Tenant.');
    }

    public function with(): array
    {
        $groups = SlotGroup::with(['slots' => function ($query) {
            $query->orderBy('slot_number', 'asc');
        }])->get();

        return [
            'slotGroups' => $groups,
        ];
    }

    public function render()
    {
        return $this->view()->layout('layouts::app')->title('Pilih Tenant');
    }
};
?>

<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8" x-data="{ activeTab: '{{ $activeTab }}' }">

    @if (session()->has('success'))
    <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg font-semibold text-center shadow-sm">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-800 rounded-xl font-semibold shadow-sm">
        {{ session('error') }}
    </div>
    @endif

    @if($hasReachedLimit)
    <div class="mb-6 p-4 bg-amber-100 border border-amber-200 text-amber-800 rounded-xl font-bold flex flex-col md:flex-row items-center justify-center gap-3 shadow-sm animate-fade-in">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <span class="text-center md:text-left">Peringatan: Setiap pengguna hanya boleh menyewa maksimal 2 lapak. Anda telah mencapai batas tersebut.</span>
    </div>
    @endif

    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900">Denah Lapak Taman Kuliner</h2>
        <p class="text-gray-500 mt-2">Perhatikan peta di bawah, lalu pilih lapak melalui pilihan kotak di bawahnya.</p>
    </div>

    @if($layoutMaps->count() > 0)
    <div class="mb-10 flex justify-center">
        <div
            x-data="{ 
                    activeSlide: 0, 
                    totalSlides: {{ $layoutMaps->count() }},
                    next() { this.activeSlide = this.activeSlide === this.totalSlides - 1 ? 0 : this.activeSlide + 1 },
                    prev() { this.activeSlide = this.activeSlide === 0 ? this.totalSlides - 1 : this.activeSlide - 1 }
                }"
            class="relative w-full max-w-4xl bg-white p-4 rounded-2xl shadow-sm border-2 border-gray-100">
            <span class="absolute top-4 left-4 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full z-20">Peta Resmi</span>

            <div class="relative w-full h-[300px] md:h-[500px] overflow-hidden rounded-xl">
                @foreach($layoutMaps as $index => $map)
                <div
                    x-show="activeSlide === {{ $index }}"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 transform translate-x-8"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="absolute inset-0 flex items-center justify-center bg-gray-50">
                    <img src="{{ Storage::url($map->image_path) }}" alt="Peta Taman Kuliner {{ $index + 1 }}" class="w-full h-full object-contain">
                </div>
                @endforeach
            </div>

            @if($layoutMaps->count() > 1)
            <button @click="prev" class="absolute left-6 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-indigo-600 p-2.5 rounded-full shadow-lg z-20 transition transform hover:scale-110 border border-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button @click="next" class="absolute right-6 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-indigo-600 p-2.5 rounded-full shadow-lg z-20 transition transform hover:scale-110 border border-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <div class="absolute bottom-8 left-0 right-0 flex justify-center gap-2 z-20">
                @foreach($layoutMaps as $index => $map)
                <button
                    @click="activeSlide = {{ $index }}"
                    :class="activeSlide === {{ $index }} ? 'bg-indigo-600 w-8' : 'bg-gray-300 hover:bg-indigo-400 w-2.5'"
                    class="h-2.5 rounded-full transition-all duration-300 shadow-sm"></button>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

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
        @foreach($slotGroups as $group)
        <button
            @click="activeTab = '{{ $group->id }}'"
            :class="activeTab === '{{ $group->id }}' ? 'bg-indigo-600 text-white shadow-md transform scale-105' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
            class="px-6 py-2.5 rounded-full font-bold text-sm md:text-base transition-all duration-200">
            {{ $group->name }}
        </button>
        @endforeach
    </div>

    <div class="relative min-h-[300px]">
        @foreach($slotGroups as $group)
        <div x-show="activeTab === '{{ $group->id }}'" style="display: none;" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3 sm:gap-4 animate-fade-in">
            @foreach($group->slots->sortBy('slot_number', SORT_NATURAL) as $slot)
            <div
                @class([ 'relative p-3 rounded-xl border-2 text-center transition-all duration-200 flex flex-col items-center justify-center aspect-square' , 'border-green-500 bg-green-50 hover:bg-green-500 hover:text-white cursor-pointer shadow-sm hover:shadow-md group'=> $slot->status === 'available',
                'border-yellow-400 bg-yellow-50 opacity-75 cursor-not-allowed' => $slot->status === 'reserved',
                'border-red-500 bg-red-50 opacity-50 cursor-not-allowed' => $slot->status === 'occupied',
                'ring-4 ring-indigo-300 transform scale-105 bg-green-500 text-white' => $selectedSlot && $selectedSlot->id === $slot->id,
                ])
                @if($slot->status === 'available')
                wire:click="selectSlot({{ $slot->id }})"
                @endif
                >
                <span class="block text-xl md:text-2xl font-black {{ ($slot->status === 'available' && (!$selectedSlot || $selectedSlot->id !== $slot->id)) ? 'text-green-700 group-hover:text-white' : ($selectedSlot && $selectedSlot->id === $slot->id ? 'text-white' : 'text-gray-800') }}">
                    {{ $slot->slot_number }}
                </span>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    @if($selectedSlot)
    <div class="mt-12 bg-white rounded-2xl shadow-xl border border-indigo-100 p-6 md:p-8 max-w-3xl mx-auto animate-fade-in">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
            <div class="bg-indigo-100 p-4 rounded-xl text-indigo-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-gray-500 font-bold uppercase tracking-wider text-sm">Lapak Terpilih</h3>
                <div class="text-4xl font-black text-indigo-600">{{ $selectedSlot->slot_number }}</div>
            </div>
        </div>

        <form wire:submit.prevent="submitRent">
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Usaha / Bisnis Anda</label>
                <input
                    type="text"
                    wire:model="businessName"
                    @if($hasReachedLimit) disabled @endif
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 focus:outline-none transition-shadow disabled:bg-gray-100 disabled:cursor-not-allowed"
                    placeholder="Contoh: Kedai Kopi Senja">
                @error('businessName') <span class="text-red-500 text-sm mt-2 block font-medium">{{ $message }}</span> @enderror
            </div>

            @if(!$hasReachedLimit)
            <div class="bg-amber-50 text-amber-800 text-sm p-4 rounded-xl mb-6 border border-amber-200">
                <strong>Perhatian:</strong> Anda memiliki waktu <b>24 Jam</b> untuk mengunggah bukti pembayaran di Dasbor setelah menekan tombol pesan.
            </div>
            @endif

            <div class="flex gap-4">
                <button type="button" wire:click="cancelSelection" class="w-1/3 py-3 px-4 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>

                @if($hasReachedLimit)
                <button type="button" disabled class="w-2/3 py-3 px-4 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed transition-colors">
                    Maksimal 2 Lapak
                </button>
                @else
                <button type="submit" class="w-2/3 py-3 px-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-colors">
                    Konfirmasi Pesanan
                </button>
                @endif
            </div>
        </form>
    </div>
    @endif
</div>