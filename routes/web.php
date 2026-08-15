<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

if (app()->environment("local")) {
  require_once __DIR__ . "/previews.php";
}

Route::get("/", function () {
  $migrationsRun = Schema::hasTable("users");

  return view("welcome", compact("migrationsRun"));
});

Route::post("/setup/migrate", function () {
  Artisan::call("migrate", ["--force" => true]);

  return response()->json(["message" => "Migrations ran successfully", "output" => Artisan::output()]);
});
