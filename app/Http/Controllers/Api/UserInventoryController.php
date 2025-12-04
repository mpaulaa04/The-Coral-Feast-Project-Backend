<?php
/**
 * Class UserInventoryController
 *
 * Handles API requests related to user inventory management.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserInventoryController extends Controller
{
    /**
     * Display a listing of inventory records for a user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function index(Request $request, User $user): JsonResponse
    {
        $this->authorizeUser($request, $user);

        $inventory = $user->inventory()
            ->with(['item.category', 'item.fish'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $inventory->map(fn ($record) => $this->serializeInventoryRecord($record))->values(),
        ]);
    }

    /**
     * Store new inventory records for a user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $this->authorizeUser($request, $user);

        $data = $request->validate([
            'items' => ['required', 'array', 'max:100'],
            'items.*.inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'items.*.is_favorite' => ['sometimes', 'boolean'],
        ]);

        /** @var Collection<int, InventoryItem> $items */
        $items = InventoryItem::query()
            ->whereIn('id', collect($data['items'])->pluck('inventory_item_id'))
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($user, $data, $items): void {
            foreach ($data['items'] as $payload) {
                $itemId = (int) $payload['inventory_item_id'];
                $quantity = (int) $payload['quantity'];
                $isFavorite = (bool) ($payload['is_favorite'] ?? false);

                $user->inventory()->updateOrCreate(
                    ['inventory_item_id' => $itemId],
                    [
                        'quantity' => $quantity,
                        'is_favorite' => $isFavorite,
                    ]
                );
            }
        });

        $user->load('inventory.item.category');

        return response()->json([
            'data' => $user->inventory->map(fn ($record) => $this->serializeInventoryRecord($record))->values(),
        ], 201);
    }

    /**
     * Update an inventory record for a user.
     *
     * @param Request $request
     * @param User $user
     * @param InventoryItem $item
     * @return JsonResponse
     */
    public function update(Request $request, User $user, InventoryItem $item): JsonResponse
    {
        $this->authorizeUser($request, $user);

        $payload = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'is_favorite' => ['sometimes', 'boolean'],
        ]);

        $record = $user->inventory()->where('inventory_item_id', $item->id)->first();

        if (! $record) {
            $record = $user->inventory()->create([
                'inventory_item_id' => $item->id,
                'quantity' => $payload['quantity'] ?? 0,
                'is_favorite' => $payload['is_favorite'] ?? false,
            ]);
        } else {
            $record->fill($payload);
            $record->save();
        }

        return response()->json([
            'data' => $this->serializeInventoryRecord($record->loadMissing('item.category', 'item.fish')),
        ]);
    }

    private function authorizeUser(Request $request, User $user): void
    {
        $requestUser = (int) $request->input('user_id');

        if ($requestUser !== $user->id) {
            abort(403, 'The provided user does not own this inventory.');
        }
    }

    private function serializeInventoryRecord($record): array
    {
        $item = $record->item;

        return [
            'id' => $record->id,
            'inventory_item_id' => $record->inventory_item_id,
            'quantity' => $record->quantity,
            'is_favorite' => $record->is_favorite,
            'item' => $item ? [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'price' => $item->price,
                'image_path' => $item->image_path,
                'pond_egg_image_path' => $item->pond_egg_image_path,
                'pond_adult_image_path' => $item->pond_adult_image_path,
                'pond_egg_dead_image_path' => $item->pond_egg_dead_image_path,
                'pond_adult_dead_image_path' => $item->pond_adult_dead_image_path,
                'metadata' => $item->metadata,
                'category' => $item->category ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                ] : null,
                'fish_id' => $item->fish_id,
            ] : null,
        ];
    }
}
