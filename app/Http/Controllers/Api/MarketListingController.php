<?php
/**
 * Class MarketListingController
 *
 * Handles API requests related to market listings and double offer management.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketListing;
use App\Models\MarketListingStatus;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketListingController extends Controller
{
    /**
     * Display the current double offer listing.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function showDoubleOffer(Request $request): JsonResponse
    {
        $listing = $this->resolveDoubleOfferListing(true);

        return response()->json([
            'data' => $this->serializeListing($listing),
        ]);
    }

    /**
     * Activate a double offer listing with a multiplier and duration.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function activateDoubleOffer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'multiplier' => ['required', 'numeric', 'min:1'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
        ]);

        $listing = $this->resolveDoubleOfferListing();

        if ($listing->isActive() && ! $listing->isExpired()) {
            return response()->json([
                'message' => 'The double offer is already active.',
                'data' => $this->serializeListing($listing),
            ], 409);
        }

        $statusId = $this->statusIdFor('active');
        $startsAt = CarbonImmutable::now();
        $endsAt = $startsAt->addSeconds($data['duration_seconds']);

        $listing->forceFill([
            'market_listing_status_id' => $statusId,
            'multiplier' => (float) $data['multiplier'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'payload' => array_merge($listing->payload ?? [], [
                'duration_seconds' => (int) $data['duration_seconds'],
                'activated_at' => $startsAt->toISOString(),
            ]),
        ])->save();

        return response()->json([
            'message' => 'Double offer activated successfully.',
            'data' => $this->serializeListing($listing->fresh('status')),
        ]);
    }

    /**
     * Deactivate the current double offer listing.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deactivateDoubleOffer(Request $request): JsonResponse
    {
        $listing = $this->resolveDoubleOfferListing();

        if (! $listing->isActive()) {
            return response()->json([
                'message' => 'The double offer is already inactive.',
                'data' => $this->serializeListing($listing),
            ]);
        }

        $statusId = $this->statusIdFor('inactive');
        $endedAt = CarbonImmutable::now();

        $listing->forceFill([
            'market_listing_status_id' => $statusId,
            'multiplier' => 1,
            'ends_at' => $endedAt,
            'payload' => array_merge($listing->payload ?? [], [
                'deactivated_at' => $endedAt->toISOString(),
            ]),
        ])->save();

        return response()->json([
            'message' => 'Double offer deactivated successfully.',
            'data' => $this->serializeListing($listing->fresh('status')),
        ]);
    }

    private function resolveDoubleOfferListing(bool $refreshIfExpired = false): MarketListing
    {
        $listing = MarketListing::query()->type('double_offer')->with('status')->first();

        if (! $listing) {
            $listing = MarketListing::create([
                'type' => 'double_offer',
                'market_listing_status_id' => $this->statusIdFor('inactive'),
                'multiplier' => 1,
                'payload' => [],
            ])->fresh('status');
        }

        if ($refreshIfExpired && $listing->isActive() && $listing->isExpired()) {
            $listing->forceFill([
                'market_listing_status_id' => $this->statusIdFor('inactive'),
                'multiplier' => 1,
            ])->save();

            $listing->refresh();
        }

        return $listing;
    }

    private function statusIdFor(string $slug): int
    {
        $status = MarketListingStatus::query()->where('slug', $slug)->first();

        if (! $status) {
            throw new \RuntimeException("Missing market listing status for slug '{$slug}'.");
        }

        return $status->id;
    }

    private function serializeListing(MarketListing $listing): array
    {
        $listing->loadMissing('status');

        $endsAt = $listing->ends_at;
        $now = CarbonImmutable::now();
        $remainingSeconds = $endsAt ? max(0, $now->diffInSeconds($endsAt, false)) : 0;

        return [
            'id' => $listing->id,
            'type' => $listing->type,
            'status' => $listing->status?->slug,
            'status_label' => $listing->status?->label,
            'multiplier' => $listing->multiplier,
            'starts_at' => $listing->starts_at?->toISOString(),
            'ends_at' => $listing->ends_at?->toISOString(),
            'remaining_seconds' => $remainingSeconds,
            'active' => $listing->isActive() && $remainingSeconds > 0,
        ];
    }
}
