<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $guarded = []; // Mengizinkan semua kolom diisi

    public function rents()
    {
        return $this->hasMany(Rent::class);
    }
}
