<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = rtrim(config('app.url') ?? $request->getSchemeAndHttpHost(), '/');
        $coverUrl = $this->cover_photo ? $base.'/storage/'.ltrim($this->cover_photo, '/') : null;
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'location' => $this->location,
            'event_at' => $this->event_at->toIso8601String(),
            'registration_opens_at' => $this->registration_opens_at->toIso8601String(),
            'registration_closes_at' => $this->registration_closes_at->toIso8601String(),
            'status' => $this->status?->value,
            'cover_photo' => $coverUrl,
            'fee' => $this->fee !== null ? (float) $this->fee : null,
            'photos' => EventPhotoResource::collection($this->whenLoaded('photos')),
            'registration_count' => $this->whenCounted('registrations'),
            'is_registered' => $this->when(isset($this->is_registered), (bool) $this->is_registered),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
