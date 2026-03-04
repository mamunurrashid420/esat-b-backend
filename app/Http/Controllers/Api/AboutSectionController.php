<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateAboutSectionRequest;
use App\Models\AboutSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AboutSectionController extends Controller
{
    /**
     * Get about section (for admin).
     */
    public function show(): JsonResponse
    {
        if (! request()->user() || ! request()->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }

        $row = AboutSection::query()->first();
        $data = [
            'main_image' => $row && $row->main_image
                ? Storage::disk('public')->url($row->main_image)
                : null,
            'overlapping_image' => $row && $row->overlapping_image
                ? Storage::disk('public')->url($row->overlapping_image)
                : null,
        ];
        return response()->json(['data' => $data]);
    }

    /**
     * Update about section images (super_admin only).
     */
    public function update(UpdateAboutSectionRequest $request): JsonResponse
    {
        if (! $request->user() || ! $request->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }

        try {
            $row = AboutSection::query()->firstOrCreate([], []);

            if ($request->hasFile('main_image')) {
                if ($row->main_image) {
                    Storage::disk('public')->delete($row->main_image);
                }
                $file = $request->file('main_image');
                $row->main_image = $file->storeAs('about_section', 'main_'.Str::random(12).'.'.$file->getClientOriginalExtension(), 'public');
            }
            if ($request->hasFile('overlapping_image')) {
                if ($row->overlapping_image) {
                    Storage::disk('public')->delete($row->overlapping_image);
                }
                $file = $request->file('overlapping_image');
                $row->overlapping_image = $file->storeAs('about_section', 'overlap_'.Str::random(12).'.'.$file->getClientOriginalExtension(), 'public');
            }

            $row->save();

            return response()->json([
                'data' => [
                    'main_image' => $row->main_image ? Storage::disk('public')->url($row->main_image) : null,
                    'overlapping_image' => $row->overlapping_image ? Storage::disk('public')->url($row->overlapping_image) : null,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update about section.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
