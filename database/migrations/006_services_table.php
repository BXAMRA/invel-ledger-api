<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("services", function (Blueprint $table) {
      $table->id();
      $table->string("name");
      $table->text("description")->nullable();
      $table->decimal("base_price", 15, 2)->default(0);
      $table->decimal("tax_rate", 5, 2)->default(0);
      $table->string("unit")->default("fixed");
      $table->string("pricing_type")->default("fixed");
      $table->text("default_deliverables")->nullable();
      $table->string("deliverables_heading")->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("services");
  }
};
