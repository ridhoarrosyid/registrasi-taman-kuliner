<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $name;
    public $ktp_number;
    public $ktp_image; // Untuk menampung file unggahan baru sementara
    public $existing_ktp_image; // Untuk memuat pratinjau gambar dari database

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->ktp_number = $user->ktp_number;
        $this->existing_ktp_image = $user->ktp_image;
    }

    public function updateProfile()
    {
        $user = Auth::user();

        // Validasi data identitas resmi (NIK wajib 16 digit angka unik)
        $this->validate([
            'name' => 'required|min:3|max:255',
            'ktp_number' => 'required|numeric|digits:16|unique:users,ktp_number,' . $user->id,
            'ktp_image' => 'nullable|image|max:2048', // Batas ukuran berkas foto maksimal 2MB
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'ktp_number.required' => 'Nomor NIK wajib diisi.',
            'ktp_number.numeric' => 'Format NIK harus berupa angka.',
            'ktp_number.digits' => 'Jumlah digit NIK harus pas 16 digit.',
            'ktp_number.unique' => 'Nomor NIK ini sudah terdaftar pada akun lain.',
            'ktp_image.image' => 'Berkas identitas wajib berupa dokumen gambar.',
            'ktp_image.max' => 'Ukuran gambar dokumen tidak boleh melebihi 2MB.',
        ]);

        $payload = [
            'name' => $this->name,
            'ktp_number' => $this->ktp_number,
        ];

        // Jika user mengunggah berkas foto KTP baru
        if ($this->ktp_image) {
            // Bersihkan file KTP lama di storage agar kapasitas penyimpanan hemat
            if ($user->ktp_image) {
                Storage::disk('public')->delete($user->ktp_image);
            }

            // Simpan gambar baru ke disk publik di bawah direktori 'ktp_attachments'
            $storedPath = $this->ktp_image->store('ktp_attachments', 'public');
            $payload['ktp_image'] = $storedPath;
            $this->existing_ktp_image = $storedPath;
        }

        $user->update($payload);

        // Reset properti file input agar bersih kembali setelah proses upload selesai
        $this->reset('ktp_image');

        session()->flash('success_profile', 'Data kelengkapan profil dan identitas KTP Anda berhasil disimpan!');
    }


    public function render()
    {
        return $this->view()->layout('layouts::app')->title('Profile');
    }
};
?>

<div class="bg-gray-50 min-h-screen pb-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900">Verifikasi Identitas</h1>
            <p class="text-gray-500 mt-2">Lengkapi dokumen NIK dan berkas KTP fisik Anda untuk validasi data penyewaan tempat.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Berkas Kependudukan Tenant
            </h2>

            @if (session()->has('success_profile'))
            <div class="mb-6 p-4 bg-green-50 text-green-700 border border-green-200 rounded-xl font-semibold text-sm animate-fade-in">
                {{ session('success_profile') }}
            </div>
            @endif

            <form wire:submit.prevent="updateProfile" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:outline-none transition-shadow">
                    @error('name') <span class="text-red-500 text-sm mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" wire:model="ktp_number" maxlength="16" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:outline-none transition-shadow" placeholder="Contoh: 1871xxxxxxxxxxxx">
                    @error('ktp_number') <span class="text-red-500 text-sm mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Unggah Gambar KTP Asli</label>
                    <input type="file" wire:model="ktp_image" accept="image/*" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

                    <div wire:loading wire:target="ktp_image" class="mt-3 text-sm text-indigo-600 font-bold flex items-center gap-2 animate-pulse">
                        <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sedang memproses gambar...
                    </div>

                    @error('ktp_image') <span class="text-red-500 text-sm mt-1 block font-medium">{{ $message }}</span> @enderror

                    <div class="mt-4">
                        @if ($ktp_image)
                        <p class="text-xs text-amber-600 font-semibold mb-2 flex items-center gap-1">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Pratinjau Foto Baru (Belum Tersimpan):
                        </p>
                        <img src="{{ $ktp_image->temporaryUrl() }}" class="object-contain rounded-xl border-2 border-dashed border-indigo-400 p-1.5 bg-gray-50 shadow-sm">
                        @elseif ($existing_ktp_image)
                        <p class="text-xs text-gray-500 mb-2 font-medium">Dokumen Gambar KTP Saat Ini:</p>
                        <img src="{{ Storage::url($existing_ktp_image) }}" class="object-contain rounded-xl border border-gray-200 p-1.5 bg-white shadow-sm">
                        @endif
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="ktp_image, updateProfile"
                        class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition shadow-md shadow-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">

                        <span wire:loading.remove wire:target="updateProfile">Simpan Perubahan Identitas</span>
                        <span wire:loading wire:target="updateProfile">Menyimpan...</span>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>