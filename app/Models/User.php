<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            // Jika user dibuat tanpa menyebutkan role, otomatis jadikan admin
            if (empty($user->role)) {
                $user->role = 'admin';
            }
        });
    }

    // 2. Proteksi Dasbor Filament
    public function canAccessPanel(Panel $panel): bool
    {
        /// 1. Jika yang mencoba masuk adalah tenant, langsung "lempar" paksa ke dashboard
        if ($this->role === 'tenant') {
            throw new HttpResponseException(redirect('/dashboard'));
        }

        // 2. Jika bukan tenant, pastikan hanya admin yang bisa mendapatkan akses (True)
        return $this->role === 'admin';
    }
}
