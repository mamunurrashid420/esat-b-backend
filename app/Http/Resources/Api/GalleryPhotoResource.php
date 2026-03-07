<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryPhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = rtrim(config('app.url') ?? $request->getSchemeAndHttpHost(), '/');
        $url = $this->image ? $base.'/storage/'.ltrim($this->image, '/') : null;
        return [
            'id' => $this->id,
            'url' => $url,
            'category' => $this->category,
            'sort_order' => $this->sort_order,
        ];
    }
}
