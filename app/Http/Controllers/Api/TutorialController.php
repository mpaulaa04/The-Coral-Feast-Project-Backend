<?php
/**
 * Class TutorialController
 *
 * Handles API requests related to user tutorial progress.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    /**
     * Display the tutorial progress for a user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function show(Request $request, User $user): JsonResponse
    {
        return response()->json([
            'tutorial_completed' => (bool) $user->tutorial_completed,
            'tutorial_step' => $user->tutorial_step,
        ]);
    }

    /**
     * Update the tutorial progress for a user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'tutorial_step' => 'nullable|string|max:64',
            'tutorial_completed' => 'nullable|boolean',
        ]);

        if (array_key_exists('tutorial_step', $validated)) {
            $user->tutorial_step = $validated['tutorial_step'] ?: null;
        }

        if (array_key_exists('tutorial_completed', $validated)) {
            $user->tutorial_completed = (bool) $validated['tutorial_completed'];
        }

        $user->save();

        return response()->json([
            'tutorial_completed' => (bool) $user->tutorial_completed,
            'tutorial_step' => $user->tutorial_step,
        ]);
    }
}
