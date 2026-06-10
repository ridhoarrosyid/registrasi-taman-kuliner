<?php

use App\Models\Rent;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    // State form upload (Amount dihapus karena sudah di-hardcode 500rb)
    public $paymentProof;
    public $rentIdToPay = null;

    public function openPaymentForm($rentId)
    {
        $this->rentIdToPay = $rentId;
        $this->resetValidation();
    }

    public function cancelPayment()
    {
        $this->rentIdToPay = null;
        $this->reset(['paymentProof']);
    }

    public function submitPayment()
    {
        // 1. Validasi input (Hanya bukti transfer, amount tidak perlu divalidasi lagi)
        $this->validate([
            'paymentProof' => 'required|image|max:2048',
        ], [
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
                'amount' => 500000, // Otomatis Rp 500.000
                'payment_proof' => $imagePath,
                'status' => 'pending',
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

                    <div class="text-gray-500 font-medium">Nama Bisnis</div>
                    <div class="font-bold text-gray-900 truncate">
                        <span class="mr-2">:</span> {{ $rent->business_name }}
                    </div>

                    <div class="text-gray-500 font-medium">Kode Lapak</div>
                    <div class="font-black text-indigo-600 text-lg">
                        <span class="mr-2 text-gray-900 font-normal text-base">:</span> {{ $rent->slot->slot_number }}
                    </div>

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

                    <div class="text-gray-500 font-medium">Tanggal Mulai</div>
                    <div class="font-semibold text-gray-800">
                        <span class="mr-2 font-normal">:</span>
                        {{ $rent->start_date ? \Carbon\Carbon::parse($rent->start_date)->format('d M Y') : '-' }}
                    </div>

                    <div class="text-gray-500 font-medium">Tanggal Selesai</div>
                    <div class="font-semibold text-gray-800">
                        <span class="mr-2 font-normal">:</span>
                        {{ $rent->end_date ? \Carbon\Carbon::parse($rent->end_date)->format('d M Y') : '-' }}
                    </div>

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
            <form wire:submit.prevent="submitPayment" class="items-start flex flex-col md:flex-row gap-6">

                <div class="flex-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nominal Transfer (Rp)</label>
                    <input
                        type="text"
                        value="500.000"
                        disabled
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 font-bold cursor-not-allowed focus:outline-none select-none">
                    <p class="text-xs text-gray-500 mt-1 font-medium">*Tarif sewa lapak sudah ditetapkan.</p>
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Unggah Bukti Transfer</label>
                    <input
                        type="file"
                        wire:model="paymentProof"
                        accept="image/*"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

                    <div wire:loading wire:target="paymentProof" class="mt-3 text-sm text-indigo-600 font-bold flex items-center gap-2 animate-pulse">
                        <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sedang memproses gambar...
                    </div>

                    @error('paymentProof') <span class="text-red-500 text-sm mt-1 block font-medium">{{ $message }}</span> @enderror

                    <div class="mt-4">
                        @if ($paymentProof)
                        <p class="text-xs text-amber-600 font-semibold mb-2 flex items-center gap-1">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Pratinjau Bukti:
                        </p>
                        <img src="{{ $paymentProof->temporaryUrl() }}" class="h-48 object-contain rounded-xl border-2 border-dashed border-indigo-400 p-1.5 bg-gray-50 shadow-sm">
                        @endif
                    </div>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="paymentProof, submitPayment"
                        class="w-full md:w-auto bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="submitPayment">Kirim Bukti</span>
                        <span wire:loading wire:target="submitPayment">Mengirim...</span>
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