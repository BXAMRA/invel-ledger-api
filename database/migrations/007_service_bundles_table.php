<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("service_bundles", function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string("name");
      $table->text("description")->nullable();
      $table->timestamps();
    });

    Schema::create("service_bundle_services", function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid("service_bundle_id")->constrained()->cascadeOnDelete();
      $table->foreignUuid("service_id")->constrained()->cascadeOnDelete();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("service_bundle_services");
    Schema::dropIfExists("service_bundles");
  }
};
