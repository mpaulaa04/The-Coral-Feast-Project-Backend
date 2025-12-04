<?php
/**
 * Class FishController
 *
 * Handles API requests related to fish resources.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fish;
use Illuminate\Http\JsonResponse;

class FishController extends Controller
{
    /**
     * Display a listing of fish.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $fishList = Fish::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Fish $fish) => $this->serializeFish($fish))
            ->values();

        return response()->json(['data' => $fishList]);
    }

    /**
     * Display the specified fish.
     *
     * @param Fish $fish
     * @return JsonResponse
     */
    public function show(Fish $fish): JsonResponse
    {
        return response()->json([
            'data' => $this->serializeFish($fish),
        ]);
    }

    /**
     * Serialize a Fish model to an array.
     *
     * @param Fish $fish
     * @return array
     */
    private function serializeFish(Fish $fish): array
    {
        return [
            'id' => $fish->id,
            'name' => $fish->name,
            'price' => $fish->price,
            'egg_image' => $fish->egg_image,
            'adult_image' => $fish->adult_image,
            'egg_dead_image' => $fish->egg_dead_image,
            'adult_dead_image' => $fish->adult_dead_image,
            'oxygen_per_day' => $fish->oxygen_per_day,
            'ph_adjustment_per_day' => $fish->ph_adjustment_per_day,
            'feedings_per_day' => $fish->feedings_per_day,
            'egg_stage_seconds' => $fish->egg_stage_seconds,
            'juvenile_stage_seconds' => $fish->juvenile_stage_seconds,
            'adult_stage_seconds' => $fish->adult_stage_seconds,
            'created_at' => optional($fish->created_at)->toIso8601String(),
            'updated_at' => optional($fish->updated_at)->toIso8601String(),
        ];
    }
}
