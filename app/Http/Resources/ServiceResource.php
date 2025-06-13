<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'banner_image_url' => $this->banner_image ? Storage::disk('uploads')->url($this->banner_image) : null,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'attributes' => $this->formatAttributes($this->attributes),
            'features' => $this->features,
            'technologies' => $this->technologies,
            'is_active' => (bool) $this->is_active,
            'order' => (int) $this->order,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    public function formatAttributes(array|null $attributes):array
    {
        if (empty($attributes)) {
            return [];
        }   

        return array_map(function ($attribute) {
            return [
                'image_path' => (! empty($attribute['image_path'])) ? Storage::disk('uploads')->url($attribute['image_path']) : null,
                'title' => $attribute['title'] ?? null,
                'description' => $attribute['description'] ?? null,
            ];
        }, $attributes);
    }
}
