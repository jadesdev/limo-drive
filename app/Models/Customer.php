<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasUuids;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'language',
        'last_active',
        'bookings_count',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
