<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Storage;

class Fleet extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail',
        'seats',
        'bags',
        'images',
        'features',
        'specifications',
        'is_active',
        'order',
        'base_fee',
        'rate_per_mile',
        'rate_per_hour',
        'minimum_hours',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'images' => 'array',
        'features' => 'array',
        'specifications' => 'array',
        'is_active' => 'boolean',
        'base_fee' => 'decimal:2',
        'rate_per_mile' => 'decimal:2',
        'rate_per_hour' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail) {
            return Storage::disk('uploads')->url($this->thumbnail);
        }

        return null;
    }

    public function getImageUrlsAttribute(): ?array
    {
        if (is_array($this->images)) {
            return array_map(function ($imagePath) {
                return $imagePath ? Storage::disk('uploads')->url($imagePath) : null;
            }, $this->images);
        }

        return null;
    }

    /**
     * Get the rate per hour.
     *
     * @return float
     */
    public function getRatePerMinAttribute()
    {
        return $this->rate_per_hour / 60;
    }
}
