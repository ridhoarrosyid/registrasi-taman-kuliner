<?php

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public $transaction;

    public function mount($id)
    {
        $this->transaction = Transaction::with('rent.slot')->findOrFail($id);

        // Keamanan ketat: Blokir jika Tenant mencoba mengintip transaksi milik orang lain dari URL
        if ($this->transaction->rent->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }
    }

    public function render()
    {
        return $this->view()
            ->layout('layouts::app')
            ->title('Detail Pembayaran - BPU Unila');
    }
};
?>

<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
    <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-2 text-indigo-600 font-bold mb-6 hover:text-indigo-800 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali ke Dasbor
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 md:p-8 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900">Detail Transaksi #{{ $transaction->id }}</h2>
                <p class="text-gray-500 mt-1 font-medium">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y, H:i') }} WIB</p>
            </div>

            @if($transaction->status === 'pending')
            <span class="bg-yellow-100 text-yellow-800 px-4 py-1.5 rounded-lg text-sm font-bold uppercase tracking-wider border border-yellow-200 shadow-sm">Menunggu Validasi</span>
            @elseif($transaction->status === 'approved')
            <span class="bg-green-100 text-green-800 px-4 py-1.5 rounded-lg text-sm font-bold uppercase tracking-wider border border-green-200 shadow-sm">Pembayaran Diterima</span>
            @else
            <span class="bg-red-100 text-red-800 px-4 py-1.5 rounded-lg text-sm font-bold uppercase tracking-wider border border-red-200 shadow-sm">Pembayaran Ditolak {{$transaction->status}}</span>
            @endif
        </div>

        @if($transaction->status === 'failed' && $transaction->reject_reason)
        <div class="mx-6 md:mx-8 mt-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl font-medium text-sm animate-fade-in flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>
                <strong class="block font-bold text-red-900 mb-1">Alasan Penolakan dari Admin:</strong>
                <p class="text-gray-700 leading-relaxed">{{ $transaction->reject_reason }}</p>
            </div>
        </div>
        @endif

        <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-6">
                <div>
                    <p class="text-sm font-bold text-gray-500 mb-1">Nama Bisnis</p>
                    <p class="text-lg font-bold text-gray-900">{{ $transaction->rent->business_name }}</p>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-500 mb-1">Kode Lapak</p>
                    <p class="text-xl font-black text-indigo-600">{{ $transaction->rent->slot->slot_number }}</p>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-500 mb-1">Nominal Terbayar</p>
                    <p class="text-lg font-bold text-gray-900">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                </div>
            </div>

            <div>
                <p class="text-sm font-bold text-gray-500 mb-3">Dokumen Bukti Transfer</p>
                <div class="bg-gray-100 p-2 rounded-xl border-2 border-dashed border-gray-300">
                    <img src="{{ Storage::url($transaction->payment_proof) }}" alt="Bukti Transfer" class="w-full h-auto object-contain max-h-[400px] rounded-lg shadow-sm">
                </div>
                <div class="mt-4 text-center">
                    <a href="{{ Storage::url($transaction->payment_proof) }}" target="_blank" class="text-sm font-bold text-indigo-600 hover:underline">
                        Lihat Gambar Ukuran Penuh &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>