<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Menangani kembalian dari Google
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Cek apakah email ini sudah ada di database, jika belum, buatkan akun baru
            $user = User::updateOrCreate([
                'email' => $googleUser->getEmail(),
            ], [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(), // Menyimpan ID Google
                'password' => bcrypt(str()->random(16)), // Beri password acak karena login pakai Google
                'role' => 'tenant', // Secara otomatis jadikan dia tenant
            ]);

            // Login-kan user tersebut ke dalam sistem Laravel
            Auth::login($user);

            // Arahkan kembali ke halaman pilih lapak yang tadi ingin dia akses
            return redirect()->intended('/pilih-lapak');
        } catch (Exception $e) {
            // Jika batal login atau error
            return redirect('/login')->with('error', 'Gagal login dengan Google. Silakan coba lagi.');
        }
    }
}
