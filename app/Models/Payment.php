<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'code',
        'payment_intent_id',
        'amount',
        'currency',
        'customer_name',
        'customer_email',
        'gateway_name',
        'gateway_ref',
        'payment_method',
        'status',
        'gateway_payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_payload' => 'array',
    ];

    /**
     * Get the booking that this payment belongs to.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    } 
    /**
     * Status Helpers
     */
    public function isSuccessful()
    {
        return in_array($this->status, ['succeeded', 'completed']);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }
    /**
     * Scopes
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['succeeded', 'completed']);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->code = $model->code ?? self::generatePaymentCode();
        });
    }

    public static function generatePaymentCode()
    {
        return 'PAY-' . strtoupper(substr(md5(uniqid()), 0, 9));
    }
}
