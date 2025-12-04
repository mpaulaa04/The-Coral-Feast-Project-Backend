<?php
/**
 * Class PondController
 *
 * Handles API requests related to ponds and their management.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\SerializesPonds;
use App\Http\Controllers\Controller;
use App\Models\Pond;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PondController extends Controller
{
    use SerializesPonds;

    /**
     * Display a listing of ponds for a user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->requireUserId($request);

        $query = Pond::query()->with(['slots.fish', 'slots.status'])->orderBy('id');

        $query->where('user_id', $userId);

        $ponds = $query->get()->map(fn (Pond $pond) => $this->serializePond($pond))->values();

        if ($ponds->isEmpty()) {
            $user = User::find($userId);

            if ($user) {
                $autoPond = $user->ensureDefaultPond();

                if ($autoPond) {
                    $ponds = collect([$this->serializePond($autoPond)]);
                }
            }
        }

        return response()->json(['data' => $ponds]);
    }

    /**
     * Store a newly created pond.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'current_day' => ['nullable', 'integer', 'min:1'],
        ]);

        $pond = Pond::create([
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'status' => $data['status'] ?? 'active',
            'current_day' => $data['current_day'] ?? 1,
        ]);

        return response()->json([
            'data' => $this->serializePond($pond),
        ], 201);
    }

    /**
     * Display the specified pond.
     *
     * @param Request $request
     * @param Pond $pond
     * @return JsonResponse
     */
    public function show(Request $request, Pond $pond): JsonResponse
    {
        $this->assertUserAccess($request, $pond);

        return response()->json([
            'data' => $this->serializePond($pond),
        ]);
    }

    /**
     * Update the specified pond.
     *
     * @param Request $request
     * @param Pond $pond
     * @return JsonResponse
     */
    public function update(Request $request, Pond $pond): JsonResponse
    {
        $this->assertUserAccess($request, $pond);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'current_day' => ['sometimes', 'integer', 'min:1'],
        ]);

        $pond->fill($data)->save();

        return response()->json([
            'data' => $this->serializePond($pond),
        ]);
    }

    /**
     * Remove the specified pond.
     *
     * @param Request $request
     * @param Pond $pond
     * @return JsonResponse
     */
    public function destroy(Request $request, Pond $pond): JsonResponse
    {
        $this->assertUserAccess($request, $pond);

        $pond->delete();

        return response()->json([
            'message' => 'Pond removed successfully.',
        ]);
    }

    private function assertUserAccess(Request $request, Pond $pond): void
    {
        $userId = $this->requireUserId($request);

        if ($userId !== $pond->user_id) {
            abort(403, 'The provided user does not own this pond.');
        }
    }

    private function requireUserId(Request $request): int
    {
        $userId = (int) $request->input('user_id');

        if ($userId <= 0) {
            abort(422, 'A valid user_id is required for this request.');
        }

        return $userId;
    }
}
