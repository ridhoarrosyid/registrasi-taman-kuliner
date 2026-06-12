<?php

use App\Models\Rent;
use App\Models\Setting;
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

        $cs = Setting::first();
        $currentPrice = $cs ? $cs->rental_price : 500000;

        // 4. Eksekusi penyimpanan ke database secara aman
        DB::transaction(function () use ($rent, $imagePath, $currentPrice) {
            // Buat record Transaksi
            Transaction::create([
                'rent_id' => $rent->id,
                'amount' => $currentPrice, // Otomatis Rp 500.000
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
            // Ikut ambil data slot dan riwayat transaksi, urutkan transaksi dari yang terbaru
            ->with(['slot', 'transactions' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        $cs = Setting::first();
        $waNumber = $cs ? $cs->whatsapp_number : '6280000000000';
        $rentalPrice = $cs ? $cs->rental_price : 500000;

        return [
            'rents' => $rents,
            'waNumber' => $waNumber,
            'rentalPrice' => $rentalPrice // Kirim ke blade
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

                    <div class="text-gray-500 font-medium">Grup Lapak</div>
                    <div class="font-black text-indigo-600 text-lg">
                        <span class="mr-2 text-gray-900 font-normal text-base">:</span> {{ $rent->slot->slotGroup->name }}
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
                @if($rent->transactions->isNotEmpty())
                <div class="mt-6 pt-6 border-t border-gray-100 w-full">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Riwayat Pembayaran
                    </h4>
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($rent->transactions as $trx)
                        <a href="{{ route('tenant.transaction.detail', $trx->id) }}" class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl border border-gray-200 hover:border-indigo-400 hover:bg-indigo-50 transition-all duration-200 group shadow-sm">
                            <div class="flex items-center gap-4 mb-3 sm:mb-0">
                                <div class="p-2.5 bg-gray-100 rounded-lg text-gray-500 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 group-hover:text-indigo-700 transition-colors">
                                        {{ \Carbon\Carbon::parse($trx->created_at)->format('d M Y, H:i') }} WIB
                                    </p>
                                    <p class="text-xs text-gray-500 font-medium mt-0.5">Nominal: Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between sm:justify-end gap-4">
                                @if($trx->status === 'pending')
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider">Diproses</span>
                                @elseif($trx->status === 'success')
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider">Diterima</span>
                                @else
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider">Ditolak</span>
                                @endif

                                <span class="text-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div>
                @if($rent->status === 'pending_payment' && $rentIdToPay !== $rent->id)
                <button wire:click="openPaymentForm({{ $rent->id }})" class="bg-gray-200 text-gray-900 px-6 py-2.5 rounded-xl font-bold hover:bg-gray-300 transition shadow-md">
                    Bayar Sekarang
                </button>
                @elseif($rentIdToPay === $rent->id)
                <button wire:click="cancelPayment" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-xl font-bold hover:bg-gray-300 transition">
                    Batal
                </button>
                @endif
            </div>
            <div class="mt-4 sm:mt-0 sm:text-right flex flex-col items-end gap-2">

                @if(in_array($rent->status, ['pending_verification', 'renewal_pending_verification']))
                @php

                $pesan = "Halo Admin BPU Unila, saya ingin konfirmasi pembayaran untuk sewa lapak (Kode Lapak: {$rent->slot->slot_number}) atas nama bisnis {$rent->business_name}. Mohon segera diverifikasi. Terima kasih.";
                $waLink = "https://wa.me/" . $waNumber . "?text=" . urlencode($pesan);
                @endphp
                <a href="{{ $waLink }}" target="_blank" class="bg-green-500 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-green-600 transition shadow-md flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564c.173.087.289.129.332.202.043.073.043.423-.101.827z"></path>
                    </svg>
                    Chat Admin BPU
                </a>
                <p class="text-xs text-gray-500 font-medium max-w-[200px]">Hubungi Admin jika verifikasi memakan waktu lebih dari 1x24 Jam.</p>
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
                        value="{{ number_format($rentalPrice, 0, ',', '.') }}"
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