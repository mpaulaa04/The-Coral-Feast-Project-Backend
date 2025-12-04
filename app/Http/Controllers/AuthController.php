<?php
/**
 * Class AuthController
 *
 * Handles user authentication and session initialization processes.
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
     /**
     * Authenticate a user and initialize required game resources.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 422);
        }

        $user->ensureWalletExists();
        $user->ensureDefaultPond();
        $user->recordDailySession();

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
        ]);
    }
}
