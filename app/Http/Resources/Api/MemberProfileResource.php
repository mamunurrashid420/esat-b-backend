<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = rtrim(config('app.url') ?? $request->getSchemeAndHttpHost(), '/');

        return [
            'id' => $this->id,
            'name_bangla' => $this->name_bangla,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'gender' => $this->gender,
            'jsc_year' => $this->jsc_year,
            'ssc_year' => $this->ssc_year,
            'highest_educational_degree' => $this->highest_educational_degree,
            'present_address' => $this->present_address,
            'permanent_address' => $this->permanent_address,
            'profession' => $this->profession,
            'designation' => $this->designation,
            'institute_name' => $this->institute_name,
            't_shirt_size' => $this->t_shirt_size,
            'blood_group' => $this->blood_group,
            'photo' => $this->photo ? $base.'/storage/'.ltrim($this->photo, '/') : null,
            'signature' => $this->signature ? $base.'/storage/'.ltrim($this->signature, '/') : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
