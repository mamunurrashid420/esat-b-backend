<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroSlideResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = rtrim(config('app.url') ?? $request->getSchemeAndHttpHost(), '/');
        $imageUrl = $this->image
            ? $base.'/storage/'.ltrim($this->image, '/')
            : null;

        return [
            'id' => $this->id,
            'image' => $imageUrl,
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
