<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\NewUserPasswordMail;
use App\Mail\ResetUserPasswordMail;
use App\Support\PasswordGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
  /**
   * Return a list of all users.
   *
   * @return \Illuminate\Database\Eloquent\Collection<int, User>
   */
  public function index()
  {
    return User::all();
  }

  /**
   * Create a new user, generate a random password, and e-mail it to them.
   *
   * Admin only. Validates name, username, email, and role before persisting.
   *
   * @param  Request  $request
   * @return \Illuminate\Http\JsonResponse  201 with the created user, or 403 if not admin
   */
  public function store(Request $request)
  {
    if (Auth::user()?->role !== "admin") {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $validated = $request->validate([
      "name" => "required|string|max:255",
      "username" => "required|string|max:255|unique:users",
      "email" => "required|string|email|max:255|unique:users",
      "role" => ["required", Rule::in(["admin", "user"])],
    ]);

    $password = PasswordGenerator::generate();

    $user = User::query()->create([
      "name" => $validated["name"],
      "username" => $validated["username"],
      "email" => $validated["email"],
      "role" => $validated["role"],
      "password" => Hash::make($password),
    ]);

    Mail::to($user->email)->send(new NewUserPasswordMail($user, $password));

    return response()->json($user, 201);
  }

  /**
   * Return a single user by their model-bound route parameter.
   *
   * @param  User  $user
   * @return User
   */
  public function show(User $user)
  {
    return $user;
  }

  /**
   * Update an existing user's profile fields (name, username, email, role).
   *
   * Admin only. All fields are optional; unique constraints ignore the current user.
   *
   * @param  Request  $request
   * @param  User     $user
   * @return \Illuminate\Http\JsonResponse  200 with the updated user, or 403 if not admin
   */
  public function update(Request $request, User $user)
  {
    if (Auth::user()?->role !== "admin") {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    // Not requested yet but good to have a basic skeleton
    $validated = $request->validate([
      "name" => "sometimes|string|max:255",
      "username" => ["sometimes", "string", "max:255", Rule::unique("users")->ignore($user->id)],
      "email" => ["sometimes", "string", "email", "max:255", Rule::unique("users")->ignore($user->id)],
      "role" => ["sometimes", Rule::in(["admin", "user"])],
    ]);

    $user->update($validated);

    return response()->json($user);
  }

  /**
   * Delete a user.
   *
   * Admin only. Guards prevent deleting: the original admin (id=1),
   * the last remaining user, or the currently authenticated user.
   *
   * @param  User  $user
   * @return \Illuminate\Http\JsonResponse  204 on success, 400/403 on guard failure
   */
  public function destroy(User $user)
  {
    if (Auth::user()?->role !== "admin") {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    if ($user->id === 1) {
      return response()->json(["message" => "Cannot delete the original admin."], 400);
    }

    if (User::query()->count() === 1) {
      return response()->json(["message" => "Cannot delete the last user."], 400);
    }

    if ($user->id === Auth::id()) {
      return response()->json(["message" => "Cannot delete your own account."], 400);
    }

    $user->delete();
    return response()->json(null, 204);
  }

  /**
   * Generate a new random password for the given user and e-mail it to them.
   *
   * Admin only. Prevents an admin from resetting their own password via this endpoint.
   *
   * @param  User  $user
   * @return \Illuminate\Http\JsonResponse  200 on success, 400/403 on guard failure
   */
  public function resetPassword(User $user)
  {
    if (Auth::user()?->role !== "admin") {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    if ($user->id === Auth::id()) {
      return response()->json(["message" => "Cannot reset your own password this way."], 400);
    }

    $password = PasswordGenerator::generate();
    $user->update(["password" => Hash::make($password)]);

    Mail::to($user->email)->send(new ResetUserPasswordMail($user, $password));

    return response()->json(["message" => "Password reset successfully."]);
  }
}
