<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HeroSlideResource extends JsonResource
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
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'primary_button_label' => $this->primary_button_label,
            'primary_button_url' => $this->primary_button_url,
            'secondary_button_label' => $this->secondary_button_label,
            'secondary_button_url' => $this->secondary_button_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
