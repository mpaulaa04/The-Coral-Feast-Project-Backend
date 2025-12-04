<?php
/**
 * Class InventoryItemController
 *
 * Handles API requests related to inventory items.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of inventory items with related data.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $items = InventoryItem::query()
                ->with([
                    'category:id,name,slug',
                    'fish:id,name,price,egg_image,adult_image,egg_dead_image,adult_dead_image,egg_stage_seconds,juvenile_stage_seconds,adult_stage_seconds',
                        'plant:id,name,slug,image_path,oxygen_bonus,ph_bonus,health_regeneration,metadata',
                        'supplement:id,name,slug,image_path,health_boost,hunger_reset,feeding_limit_bonus,metadata',
                ])
                ->orderBy('inventory_item_category_id')
                ->orderBy('name')
                ->get()
                ->map(function (InventoryItem $item) {
                    return [
                        'id' => $item->id,
                        'slug' => $item->slug,
                        'name' => $item->name,
                        'price' => $item->price,
                        'inventory_item_category_id' => $item->inventory_item_category_id,
                            'plant_id' => $item->plant_id,
                            'supplement_id' => $item->supplement_id,
                        'category' => $item->category ? [
                            'id' => $item->category->id,
                            'name' => $item->category->name,
                            'slug' => $item->category->slug,
                        ] : null,
                        'fish_id' => $item->fish_id,
                        'fish' => $item->fish ? [
                            'id' => $item->fish->id,
                            'name' => $item->fish->name,
                            'price' => $item->fish->price,
                            'egg_image' => $item->fish->egg_image,
                            'adult_image' => $item->fish->adult_image,
                            'egg_dead_image' => $item->fish->egg_dead_image,
                            'adult_dead_image' => $item->fish->adult_dead_image,
                            'egg_stage_seconds' => $item->fish->egg_stage_seconds,
                            'juvenile_stage_seconds' => $item->fish->juvenile_stage_seconds,
                            'adult_stage_seconds' => $item->fish->adult_stage_seconds,
                        ] : null,
                        'image_path' => $item->image_path,
                        'pond_egg_image_path' => $item->pond_egg_image_path,
                        'pond_adult_image_path' => $item->pond_adult_image_path,
                        'pond_egg_dead_image_path' => $item->pond_egg_dead_image_path,
                        'pond_adult_dead_image_path' => $item->pond_adult_dead_image_path,
                        'metadata' => (array) $item->metadata,
                            'plant' => $item->plant ? [
                                'id' => $item->plant->id,
                                'name' => $item->plant->name,
                                'slug' => $item->plant->slug,
                                'image_path' => $item->plant->image_path,
                                'oxygen_bonus' => $item->plant->oxygen_bonus,
                                'ph_bonus' => $item->plant->ph_bonus,
                                'health_regeneration' => $item->plant->health_regeneration,
                                'metadata' => (array) $item->plant->metadata,
                            ] : null,
                            'supplement' => $item->supplement ? [
                                'id' => $item->supplement->id,
                                'name' => $item->supplement->name,
                                'slug' => $item->supplement->slug,
                                'image_path' => $item->supplement->image_path,
                                'health_boost' => $item->supplement->health_boost,
                                'hunger_reset' => $item->supplement->hunger_reset,
                                'feeding_limit_bonus' => $item->supplement->feeding_limit_bonus,
                                'metadata' => (array) $item->supplement->metadata,
                            ] : null,
                        'created_at' => optional($item->created_at)->toIso8601String(),
                        'updated_at' => optional($item->updated_at)->toIso8601String(),
                    ];
                });

            return response()->json([
                'data' => $items,
            ]);
        } catch (\Throwable $th) {
            Log::error('Failed to load inventory items catalog', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'No se pudo cargar el catálogo de inventario.',
            ], 500);
        }
    }
}
