<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("document_items", function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid("document_id")->constrained()->cascadeOnDelete();
      $table->foreignUuid("service_id")->nullable()->constrained()->nullOnDelete();
      $table->string("name");
      $table->text("description")->nullable();
      $table->string("deliverables_heading")->nullable();
      $table->json("deliverables")->nullable();

      $table->decimal("unit_price", 15, 2)->default(0);
      $table->decimal("discount_flat", 15, 2)->default(0);
      $table->decimal("discount_percentage", 5, 2)->default(0);
      $table->decimal("tax_rate", 5, 2)->default(0);
      $table->decimal("total", 15, 2)->default(0);
      $table->string("pricing_type")->default("standard");
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("document_items");
  }
};
