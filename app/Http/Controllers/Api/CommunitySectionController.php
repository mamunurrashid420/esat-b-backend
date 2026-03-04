<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateCommunitySectionRequest;
use App\Models\CommunitySection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CommunitySectionController extends Controller
{
    public function show(): JsonResponse
    {
        if (! request()->user() || ! request()->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }

        $row = CommunitySection::query()->first();
        $data = [
            'image' => $row && $row->image
                ? Storage::disk('public')->url($row->image)
                : null,
        ];
        return response()->json(['data' => $data]);
    }

    public function update(UpdateCommunitySectionRequest $request): JsonResponse
    {
        if (! $request->user() || ! $request->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }

        try {
            $row = CommunitySection::query()->firstOrCreate([], []);

            if ($request->hasFile('image')) {
                if ($row->image) {
                    Storage::disk('public')->delete($row->image);
                }
                $file = $request->file('image');
                $row->image = $file->storeAs('community_section', 'image_'.Str::random(12).'.'.$file->getClientOriginalExtension(), 'public');
            }

            $row->save();

            return response()->json([
                'data' => [
                    'image' => $row->image ? Storage::disk('public')->url($row->image) : null,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update community section.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
