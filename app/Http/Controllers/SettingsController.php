<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
  /**
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(): JsonResponse
  {
    $settings = Setting::all()
      ->pluck("value", "key")
      ->map(function ($value) {
        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
      });

    return $this->success($settings, "Settings retrieved successfully");
  }

  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(Request $request): JsonResponse
  {
    $data = $request->validate([
      "settings" => "required|array",
      "settings.*.key" => "required|string",
      "settings.*.value" => "nullable",
    ]);

    foreach ($data["settings"] as $setting) {
      Setting::updateOrCreate(["key" => $setting["key"]], ["value" => is_array($setting["value"]) ? json_encode($setting["value"]) : $setting["value"]]);
    }

    return $this->success(null, "Settings saved successfully");
  }

  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function uploadLogo(Request $request): JsonResponse
  {
    $request->validate([
      "logo" => "required|image|max:5120", // Max 5MB
    ]);

    $file = $request->file("logo");
    $extension = $file->getClientOriginalExtension() ?: "png";
    $filename = "business-logo." . $extension;

    // Clean up old logo formats if they exist
    $files = \Illuminate\Support\Facades\Storage::disk("public")->files("logos");
    foreach ($files as $oldFile) {
      if (str_starts_with(basename($oldFile), "business-logo.")) {
        \Illuminate\Support\Facades\Storage::disk("public")->delete($oldFile);
      }
    }

    $path = $file->storeAs("logos", $filename, "public");
    $url = asset("storage/" . $path) . "?t=" . time();

    return $this->success(["url" => $url], "Logo uploaded successfully");
  }

  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function updateSecurity(Request $request): JsonResponse
  {
    $user = $request->user();

    $validated = $request->validate([
      "name" => "nullable|string|max:255",
      "username" => "nullable|string|unique:users,username," . $user->id,
      "email" => "nullable|string|email|unique:users,email," . $user->id,
      "password" => "nullable|string|min:4",
    ]);

    if (isset($validated["name"])) {
      $user->name = $validated["name"];
    }

    if (isset($validated["email"])) {
      $user->email = $validated["email"];
    }

    if (isset($validated["username"])) {
      $user->username = $validated["username"];
    }

    if (isset($validated["password"])) {
      $user->password = $validated["password"];
    }

    $user->save();

    return $this->success(null, "Account settings updated");
  }
}
