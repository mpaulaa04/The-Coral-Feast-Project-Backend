<?php
/**
 * Class UserController
 *
 * Handles CRUD operations for user accounts, including creation,
 * retrieval, updates, and deletion of user resources.
 */
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a list of all users ordered by creation date.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(
            User::latest()->get()
        );
    }

     /**
     * Show the form for creating a new user resource. (Not used in API)
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created user in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'farm_name' => ['required', 'string', 'max:255'],
            'farm_type' => ['required', 'string', 'in:saltwater,freshwater'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'farm_name' => $validated['farm_name'],
            'farm_type' => $validated['farm_type'],
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user resource.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Show the form for editing the specified user. (Not used in API)
     *
     * @param string $id
     * @return void
     */
    public function edit(string $id)
    {
        //
    }

     /**
     * Update the specified user resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:8', 'max:255', 'confirmed'],
            'farm_name' => ['sometimes', 'string', 'max:255'],
            'farm_type' => ['sometimes', 'string', 'in:saltwater,freshwater'],
        ]);

        if (array_key_exists('password', $validated)) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->fill($validated)->save();

        return response()->json($user->refresh());
    }

    /**
     * Delete the specified user resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent();
    }
}
