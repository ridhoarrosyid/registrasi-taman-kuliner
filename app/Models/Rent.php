<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    protected $guarded = [];

    // Casts digunakan agar format tanggal otomatis dikelola dengan baik
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reserved_until' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
