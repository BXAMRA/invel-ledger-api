<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
  /**
   * @param \App\Http\Requests\LoginRequest $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function login(LoginRequest $request): JsonResponse
  {
    $validated = $request->validated();

    $user = User::query()->where("username", $validated["username"])->first();

    if (!$user) {
      throw ValidationException::withMessages([
        "username" => ["The provided credentials are incorrect."],
      ]);
    }

    if (preg_match('/^[a-f0-9]{64}$/i', $user->password)) {
      $expectedHash = hash("sha256", $validated["password"] . "invel_ledger_secure_salt_value_2026");
      if ($user->password === $expectedHash) {
        $user->password = $validated["password"];
        $user->save();
      } else {
        throw ValidationException::withMessages([
          "username" => ["The provided credentials are incorrect."],
        ]);
      }
    } else {
      if (!Hash::check($validated["password"], $user->password)) {
        throw ValidationException::withMessages([
          "username" => ["The provided credentials are incorrect."],
        ]);
      }
    }

    $tokenName = $validated["client_type"] . ":" . $validated["token_type"];

    // Remove all previous tokens for this user EXCEPT those marked 'remember'
    $user->tokens()->where("name", "NOT LIKE", "%:remember")->delete();

    // Also remove any tokens (even remember ones) that haven't been used for 90 days
    $user->tokens()->where("last_used_at", "<", now()->subDays(90))->delete();

    // Token will never expire because of config/sanctum.php
    $token = $user->createToken($tokenName)->plainTextToken;

    return $this->success(
      [
        "token" => $token,
        "user" => $user,
      ],
      "Login successful",
    );
  }

  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function logout(Request $request): JsonResponse
  {
    $request->user()->currentAccessToken()->delete();

    return $this->success(null, "Logged out successfully");
  }

  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function cleanupToken(Request $request): JsonResponse
  {
    $request->validate(["token" => "required|string"]);

    // If we have a plain text token (e.g. from frontend storage)
    $token = PersonalAccessToken::findToken($request->token);
    $token?->delete();

    return response()->json(["message" => "Token cleaned up successfully"]);
  }
}
