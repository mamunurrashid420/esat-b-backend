<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHeroSlideRequest;
use App\Http\Requests\Api\UpdateHeroSlideRequest;
use App\Http\Resources\Api\HeroSlideResource;
use App\Models\HeroSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class HeroSlideController extends Controller
{
    /**
     * Display a listing of hero slides (admin: all; used by homepage for active only).
     */
    public function index(Request $request): JsonResponse
    {
        if (! $request->user() || ! $request->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }

        $slides = HeroSlide::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => HeroSlideResource::collection($slides),
        ]);
    }

    /**
     * Store a newly created resource (super_admin only).
     */
    public function store(StoreHeroSlideRequest $request): JsonResponse
    {
        if (! $request->user() || ! $request->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }

        try {
            $data = $request->validated();
            unset($data['image']);
            $data['sort_order'] = $data['sort_order'] ?? 0;
            $data['is_active'] = $data['is_active'] ?? true;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'hero_'.Str::random(20).'.'.$file->getClientOriginalExtension();
                $data['image'] = $file->storeAs('hero_slides', $filename, 'public');
            }

            $slide = HeroSlide::create($data);

            return (new HeroSlideResource($slide))->response()->setStatusCode(201);
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to save the slide. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display the specified resource (super_admin only).
     */
    public function show(HeroSlide $heroSlide): JsonResponse
    {
        if (! request()->user() || ! request()->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }

        return response()->json([
            'data' => new HeroSlideResource($heroSlide),
        ]);
    }

    /**
     * Update the specified resource (super_admin only).
     */
    public function update(UpdateHeroSlideRequest $request, HeroSlide $heroSlide): JsonResponse
    {
        if (! $request->user() || ! $request->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }

        try {
            $data = $request->validated();
            unset($data['image']);

            if ($request->hasFile('image')) {
                if ($heroSlide->image) {
                    Storage::disk('public')->delete($heroSlide->image);
                }
                $file = $request->file('image');
                $filename = 'hero_'.Str::random(20).'.'.$file->getClientOriginalExtension();
                $data['image'] = $file->storeAs('hero_slides', $filename, 'public');
            }

            $heroSlide->update($data);

            return response()->json([
                'data' => new HeroSlideResource($heroSlide->fresh()),
            ]);
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update the slide. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Remove the specified resource (super_admin only).
     */
    public function destroy(HeroSlide $heroSlide): JsonResponse
    {
        if (! request()->user() || ! request()->user()->isSuperAdmin()) {
            abort(403, 'Forbidden.');
        }

        if ($heroSlide->image) {
            Storage::disk('public')->delete($heroSlide->image);
        }
        $heroSlide->delete();

        return response()->json(null, 204);
    }
}
