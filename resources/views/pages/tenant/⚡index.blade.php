<?php

use App\Models\Rent;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads; // Trait sakti Livewire untuk upload file

    // State form upload
    public $paymentProof;
    public $amount;
    public $rentIdToPay = null;

    // Fungsi membuka form bayar untuk lapak tertentu
    public function openPaymentForm($rentId)
    {
        $this->rentIdToPay = $rentId;
        $this->resetValidation();
    }

    // Fungsi menutup form bayar
    public function cancelPayment()
    {
        $this->rentIdToPay = null;
        $this->reset(['paymentProof', 'amount']);
    }

    // Fungsi utama: memproses unggahan bukti bayar
    public function submitPayment()
    {
        // 1. Validasi input
        $this->validate([
            'amount' => 'required|numeric|min:10000',
            'paymentProof' => 'required|image|max:2048', // Maksimal 2MB, format gambar
        ], [
            'amount.required' => 'Nominal transfer wajib diisi.',
            'paymentProof.required' => 'Bukti pembayaran wajib diunggah.',
            'paymentProof.image' => 'File harus berupa gambar (JPG/PNG).',
            'paymentProof.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        // 2. Pastikan data sewa valid dan milik user yang sedang login
        $rent = Rent::where('id', $this->rentIdToPay)->where('user_id', Auth::id())->first();

        if (!$rent || $rent->status !== 'pending_payment') {
            session()->flash('error', 'Pesanan tidak valid atau masa tenggang sudah habis.');
            return;
        }

        // 3. Simpan gambar ke folder storage/app/public/payment_proofs
        $imagePath = $this->paymentProof->store('payment_proofs', 'public');

        // 4. Eksekusi penyimpanan ke database secara aman
        DB::transaction(function () use ($rent, $imagePath) {
            // Buat record Transaksi
            Transaction::create([
                'rent_id' => $rent->id,
                'amount' => $this->amount,
                'payment_proof' => $imagePath,
                'status' => 'pending', // Status menunggu validasi Admin BPU
            ]);

            // Ubah status Sewa
            $rent->update([
                'status' => 'pending_verification'
            ]);
        });

        // 5. Bersihkan form dan berikan pesan sukses
        $this->cancelPayment();
        session()->flash('success', 'Bukti pembayaran berhasil diunggah! Mohon tunggu verifikasi dari Admin Taman Kuliner.');
    }

    public function with(): array
    {
        // Mengambil semua riwayat sewa milik tenant ini, urutkan dari yang terbaru
        $rents = Rent::where('user_id', Auth::id())
            ->with('slot')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'rents' => $rents
        ];
    }

    public function render()
    {
        return $this->view()->layout('layouts::app')->title('Dasbor Tenant - Wajah Digital untuk Bisnis Profesional');
    }
};
?>

<div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900">Dasbor Tenant</h2>
        <p class="text-gray-500 mt-2">Kelola lapak Taman Kuliner Anda di sini.</p>
    </div>

    @if (session()->has('success'))
    <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-800 rounded-xl font-semibold shadow-sm">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-800 rounded-xl font-semibold shadow-sm">
        {{ session('error') }}
    </div>
    @endif

    <div class="space-y-6">
        @forelse($rents as $rent)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-500 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">

            <div class="flex-1">
                <div class="grid grid-cols-[140px_auto] sm:grid-cols-[160px_auto] gap-y-3 text-sm md:text-base items-center">

                    <!-- Nama Bisnis -->
                    <div class="text-gray-500 font-medium">Nama Bisnis</div>
                    <div class="font-bold text-gray-900 truncate">
                        <span class="mr-2">:</span> {{ $rent->business_name }}
                    </div>

                    <!-- Kode Tenant / Lapak -->
                    <div class="text-gray-500 font-medium">Kode Lapak</div>
                    <div class="font-black text-indigo-600 text-lg">
                        <span class="mr-2 text-gray-900 font-normal text-base">:</span> {{ $rent->slot->slot_number }}
                    </div>

                    <!-- Status -->
                    <div class="text-gray-500 font-medium">Status</div>
                    <div class="font-bold flex items-center">
                        <span class="mr-2 font-normal">:</span>
                        @if($rent->status === 'pending_payment')
                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-md text-xs uppercase tracking-wider">Menunggu Pembayaran</span>
                        @elseif($rent->status === 'pending_verification')
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-md text-xs uppercase tracking-wider">Verifikasi Admin</span>
                        @elseif($rent->status === 'active')
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-md text-xs uppercase tracking-wider">Aktif Tersewa</span>
                        @else
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-md text-xs uppercase tracking-wider">Gagal / Ditolak</span>
                        @endif
                    </div>

                    <!-- Tanggal Mulai -->
                    <div class="text-gray-500 font-medium">Tanggal Mulai</div>
                    <div class="font-semibold text-gray-800">
                        <span class="mr-2 font-normal">:</span>
                        {{ $rent->start_date ? \Carbon\Carbon::parse($rent->start_date)->format('d M Y') : '-' }}
                    </div>

                    <!-- Tanggal Selesai -->
                    <div class="text-gray-500 font-medium">Tanggal Selesai</div>
                    <div class="font-semibold text-gray-800">
                        <span class="mr-2 font-normal">:</span>
                        {{ $rent->end_date ? \Carbon\Carbon::parse($rent->end_date)->format('d M Y') : '-' }}
                    </div>

                    <!-- Batas Bayar (Hanya Muncul Jika Belum Bayar) -->
                    @if($rent->status === 'pending_payment')
                    <div class="text-red-500 font-medium">Batas Bayar</div>
                    <div class="font-bold text-red-600">
                        <span class="mr-2 font-normal text-gray-900">:</span>
                        {{ \Carbon\Carbon::parse($rent->reserved_until)->format('d M Y, H:i') }} WIB
                    </div>
                    @endif

                </div>
            </div>

            <div>
                @if($rent->status === 'pending_payment' && $rentIdToPay !== $rent->id)
                <button wire:click="openPaymentForm({{ $rent->id }})" class="bg-gray-900 text-black px-6 py-2.5 rounded-xl font-bold hover:bg-gray-800 transition shadow-md">
                    Bayar Sekarang
                </button>
                @elseif($rentIdToPay === $rent->id)
                <button wire:click="cancelPayment" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-xl font-bold hover:bg-gray-300 transition">
                    Batal
                </button>
                @endif
            </div>
        </div>

        @if($rentIdToPay === $rent->id)
        <div class="bg-gray-50 rounded-2xl border-2 border-indigo-100 p-6 mt-[-10px] shadow-inner mb-6 animate-fade-in">
            <form wire:submit.prevent="submitPayment" class="flex flex-col md:flex-row gap-6">

                <div class="flex-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nominal Transfer (Rp)</label>
                    <input type="number" wire:model="amount" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:outline-none" placeholder="Contoh: 150000">
                    @error('amount') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Unggah Struk / Bukti Transfer</label>
                    <input type="file" wire:model="paymentProof" accept="image/*" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('paymentProof') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror

                    @if ($paymentProof)
                    <div class="mt-3">
                        <img src="{{ $paymentProof->temporaryUrl() }}" class="h-32 object-contain rounded-lg border shadow-sm">
                    </div>
                    @endif
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full md:w-auto bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-md">
                        Kirim Bukti
                    </button>
                </div>
            </form>
        </div>
        @endif

        @empty
        <div class="text-center py-12 bg-white rounded-2xl border border-dashed border-gray-300">
            <p class="text-gray-500 mb-4">Anda belum memiliki riwayat penyewaan lapak.</p>
            <a href="/pilih-lapak" class="text-indigo-600 font-bold hover:underline">Mulai Cari Lapak</a>
        </div>
        @endforelse
    </div>
</div>