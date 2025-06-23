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
            'problem_solved' => [
                'image_url' => $this->problem_solved_image ? Storage::disk('uploads')->url($this->problem_solved_image) : null,
                'description' => $this->problem_solved_desc,
            ],
            'target_audience' => [
                'image_url' => $this->target_audience_image ? Storage::disk('uploads')->url($this->target_audience_image) : null,
                'description' => $this->target_audience_desc,
            ],
            'client_benefits' => [
                'image_url' => $this->client_benefits_image ? Storage::disk('uploads')->url($this->client_benefits_image) : null,
                'description' => $this->client_benefits_desc,
            ],
            'features' => $this->features,
            'technologies' => $this->technologies,
            'is_active' => (bool) $this->is_active,
            'order' => (int) $this->order,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
