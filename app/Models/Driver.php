<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'language',
        'profile_image',
        'orders',
        'dob',
        'gender',
        'status',
        'hire_date',
        'termination_date',
        'notes',
        'is_available',
        'last_online_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dob' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'is_available' => 'boolean',
        'last_online_at' => 'datetime',
    ];
}
