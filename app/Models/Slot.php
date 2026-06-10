<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slot extends Model
{
    protected $fillable = [
        'slot_group_id', // <--- Tambahkan ini
        'slot_number',
        'status',
    ];

    public function slotGroup(): BelongsTo
    {
        return $this->belongsTo(SlotGroup::class, 'slot_group_id');
    }

    public function rents()
    {
        return $this->hasMany(Rent::class);
    }
}
