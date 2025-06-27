<?php

namespace App\Http\Resources;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FleetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = Auth::user()?->isAdmin() ?? false;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'seats' => (int) $this->seats,
            'bags' => (int) $this->bags,
            'image_gallery_urls' => $this->image_urls,
            'features' => $this->features,
            'specifications' => $this->specifications,
            'is_active' => (bool) $this->is_active,
            'order' => (int) $this->order,
            'base_fee' => $this->when($isAdmin, (float) $this->base_fee),
            'rate_per_mile' => $this->when($isAdmin, (float) $this->rate_per_mile),
            'rate_per_hour' => $this->when($isAdmin, (float) $this->rate_per_hour),
            'minimum_hours' => $this->when($isAdmin, (int) $this->minimum_hours),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
