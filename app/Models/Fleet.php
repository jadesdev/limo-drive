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
        'base_rate',
        'rate_per_km',
        'rate_per_minute',
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
        'base_rate' => 'decimal:2',
        'rate_per_km' => 'decimal:2',
        'rate_per_minute' => 'decimal:2',
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
}
