<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ServiceBundleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/ping", function () {
  return response()->json(["status" => "ok", "version" => "0.35.8"]);
});
Route::post("/login", [AuthController::class, "login"]);
Route::post("/cleanup-token", [AuthController::class, "cleanupToken"]);

// Setup & Onboarding (Unprotected)
Route::get("/setup/status", [SetupController::class, "status"]);
Route::post("/setup/fresh", [SetupController::class, "fresh"]);
Route::post("/setup/restore", [SetupController::class, "restore"]);

Route::middleware("auth:sanctum")->group(function () {
  Route::get("/user", function (Request $request) {
    return $request->user();
  });
  Route::post("/logout", [AuthController::class, "logout"]);

  Route::get("/dashboard", [DashboardController::class, "index"]);

  Route::apiResource("customers", CustomerController::class);
  Route::apiResource("services", ServiceController::class);
  Route::apiResource("bundles", ServiceBundleController::class);
  Route::post("documents/{document}/upload-attachment", [DocumentController::class, "uploadAttachment"]);
  Route::get("documents/{document}/attachments/{index}/download", [DocumentController::class, "downloadAttachment"]);
  Route::delete("documents/{document}/attachments/{index}", [DocumentController::class, "deleteAttachment"]);
  Route::apiResource("documents", DocumentController::class);
  Route::apiResource("payments", PaymentController::class);
  Route::post("users/{user}/reset-password", [UserController::class, "resetPassword"]);
  Route::apiResource("users", UserController::class);

  // Backup
  Route::get("/backup", [BackupController::class, "export"]);
  Route::post("/backup/import", [BackupController::class, "import"]);

  // Settings
  Route::get("/settings", [SettingsController::class, "index"]);
  Route::post("/settings", [SettingsController::class, "store"]);
  Route::post("/settings/logo", [SettingsController::class, "uploadLogo"]);
  Route::post("/settings/security", [SettingsController::class, "updateSecurity"]);
});
