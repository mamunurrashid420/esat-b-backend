<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateAuthPageSectionRequest;
use App\Models\AuthPageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AuthPageSectionController extends Controller
{
    public function showPublic(): JsonResponse
    {
        $row = AuthPageSection::query()->first();
        $base = rtrim(config('app.url') ?? request()->getSchemeAndHttpHost(), '/');
        $url = $row && $row->background_image
            ? $base.'/storage/'.ltrim($row->background_image, '/')
            : null;
        return response()->json(['data' => ['background_image' => $url]]);
    }

    public function show(): JsonResponse
    {
        if (! request()->user() || ! request()->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }
        $row = AuthPageSection::query()->first();
        $data = [
            'background_image' => $row && $row->background_image
                ? Storage::disk('public')->url($row->background_image)
                : null,
        ];
        return response()->json(['data' => $data]);
    }

    public function update(UpdateAuthPageSectionRequest $request): JsonResponse
    {
        if (! $request->user() || ! $request->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }
        try {
            $row = AuthPageSection::query()->firstOrCreate([], []);
            if ($request->hasFile('image')) {
                if ($row->background_image) {
                    Storage::disk('public')->delete($row->background_image);
                }
                $file = $request->file('image');
                $row->background_image = $file->storeAs(
                    'auth_page_section',
                    'bg_'.Str::random(12).'.'.$file->getClientOriginalExtension(),
                    'public'
                );
            }
            $row->save();
            return response()->json([
                'data' => [
                    'background_image' => $row->background_image
                        ? Storage::disk('public')->url($row->background_image)
                        : null,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update auth page image.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
