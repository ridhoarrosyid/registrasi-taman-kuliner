<?php

use App\Models\Rent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public $rent;
    public $isAdmin = false;
    public $isValid = false;

    public function mount($id)
    {
        // Ambil data penyewaan beserta relasinya (ditambah transactions khusus untuk admin)
        $this->rent = Rent::with(['user', 'slot', 'transactions' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        // Cek apakah masa berlaku masih aktif
        $now = now();
        if ($this->rent->status === 'active' && $this->rent->start_date <= $now && $this->rent->end_date >= $now) {
            $this->isValid = true;
        }

        // Deteksi jika yang memindai QR adalah Admin BPU yang sedang login
        if (Auth::check() && Auth::user()->role === 'admin') {
            $this->isAdmin = true;
        }
    }

    public function render()
    {
        return $this->view()
            ->layout('layouts::app') // Sesuaikan jika layout utama Anda berbeda
            ->title('Verifikasi Keaslian Lapak');
    }
};
?>

<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-black text-indigo-900 tracking-tight">BPU UNIVERSITAS LAMPUNG</h1>
            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mt-1">Sistem Verifikasi Lapak</p>
        </div>

        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden mb-6 relative">
            <div class="{{ $isValid ? 'bg-green-500' : 'bg-red-500' }} text-white text-center py-4 px-6 flex flex-col items-center justify-center gap-2">
                @if($isValid)
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-xl font-black uppercase tracking-wider">Lapak Sah & Aktif</h2>
                @else
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-xl font-black uppercase tracking-wider">Tidak Valid / Kedaluwarsa</h2>
                @endif
            </div>

            <div class="p-6 md:p-8 space-y-6">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Usaha / Booth</p>
                    <p class="text-2xl font-black text-gray-900">{{ strtoupper($rent->business_name) }}</p>
                </div>

                <div class="grid grid-cols-2 gap-6 pb-6 border-b border-gray-100">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kode Lapak</p>
                        <p class="text-xl font-bold text-indigo-600">{{ $rent->id . '-' . $rent->slot->slot_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Blok Area</p>
                        <p class="text-lg font-bold text-gray-800">{{ $rent->slot->slotGroup->name ?? '-' }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Penyewa Terdaftar</p>
                    <p class="text-lg font-bold text-gray-800">{{ $rent->user->name }}</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 text-center">Masa Berlaku Izin</p>
                    <p class="text-center font-bold text-gray-900">
                        {{ $rent->start_date ? \Carbon\Carbon::parse($rent->start_date)->format('d M Y') : '-' }}
                        <span class="text-gray-400 mx-2">s.d.</span>
                        {{ $rent->end_date ? \Carbon\Carbon::parse($rent->end_date)->format('d M Y') : '-' }}
                    </p>
                </div>
            </div>
        </div>

        @if($isAdmin)
        <div class="bg-indigo-900 rounded-3xl shadow-lg overflow-hidden border border-indigo-800 animate-fade-in mb-8">
            <div class="bg-indigo-800 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Akses Khusus Admin
                </h3>
            </div>

            <div class="p-6 space-y-5">
                <div>
                    <p class="text-indigo-300 text-xs font-bold uppercase mb-1">Kontak Email Tenant</p>
                    <p class="text-white font-medium">{{ $rent->user->email }}</p>
                </div>

                <div>
                    <p class="text-indigo-300 text-xs font-bold uppercase mb-1">Status di Database</p>
                    <span class="inline-block bg-white text-indigo-900 px-3 py-1 rounded-md text-xs font-bold uppercase mt-1">
                        {{ $rent->status }}
                    </span>
                </div>

                <div>
                    <p class="text-indigo-300 text-xs font-bold uppercase mb-3 border-b border-indigo-700 pb-2">Riwayat Pembayaran</p>
                    @forelse($rent->transactions as $trx)
                    <div class="mb-3 bg-indigo-800/50 p-3 rounded-lg border border-indigo-700/50">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-white text-sm font-bold">Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded {{ $trx->status === 'approved' ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white' }}">
                                {{ strtoupper($trx->status) }}
                            </span>
                        </div>
                        <span class="text-indigo-300 text-xs">{{ \Carbon\Carbon::parse($trx->created_at)->format('d M Y, H:i') }}</span>
                    </div>
                    @empty
                    <p class="text-indigo-300 text-sm italic">Belum ada transaksi tercatat.</p>
                    @endforelse
                </div>

                <div class="pt-4 mt-2 border-t border-indigo-700">
                    <a href="{{ url('/admin/rents/' . $rent->id . '/edit') }}" class="block w-full text-center bg-white text-indigo-900 font-bold py-3 rounded-xl hover:bg-indigo-50 transition">
                        Buka di Panel Admin
                    </a>
                </div>
            </div>
        </div>
        @endif

        <div class="text-center mt-8 pb-8">
            <p class="text-xs text-gray-400 font-medium">Wajah Digital untuk Bisnis Profesional</p>
        </div>
    </div>
</div>