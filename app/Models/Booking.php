<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Str;

class Booking extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'fleet_id',
        'driver_id',
        'customer_id',
        'service_type',
        'is_accessible',
        'is_return_service',
        'duration_hours',
        'pickup_datetime',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_address',
        'dropoff_latitude',
        'dropoff_longitude',
        'passenger_count',
        'bag_count',
        'price',
        'payment_method',
        'payment_status',
        'notes',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pickup_datetime' => 'datetime',
        'is_accessible' => 'boolean',
        'is_return_service' => 'boolean',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['customer'];

    public function getNameAttribute()
    {
        return $this->customer_first_name . ' ' . $this->customer_last_name;
    }

    /**
     * Get the fleet associated with the booking.
     */
    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    /**
     * Get the customer associated with the booking.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the service associated with the booking.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the driver assigned to the booking.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the payments associated with the booking.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest payment associated with the booking.
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Generate a booking code.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->code = $model->code ?? self::generateBookingCode();
        });
    }

    /**
     * Generate a booking code.
     */
    public static function generateBookingCode()
    {
        return 'BK-' . strtoupper(Str::random(9));
    }

    /**
     * Scopes
     */
    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending_payment');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('pickup_datetime', '>', now());
    }

    /**
     * Status Helpers
     */
    public function isPendingPayment()
    {
        return $this->status === 'pending_payment';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }
}
