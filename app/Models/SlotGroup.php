<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlotGroup extends Model
{
    protected $fillable = ['name', 'slug'];

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }
}
