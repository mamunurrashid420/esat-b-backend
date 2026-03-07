<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventPhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = rtrim(config('app.url') ?? $request->getSchemeAndHttpHost(), '/');
        $url = $base.'/storage/'.ltrim($this->path, '/');
        return [
            'id' => $this->id,
            'url' => $url,
            'sort_order' => $this->sort_order,
        ];
    }
}
