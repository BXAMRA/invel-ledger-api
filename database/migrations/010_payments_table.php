<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("payments", function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid("document_id")->constrained()->cascadeOnDelete();
      $table->date("payment_date");
      $table->decimal("amount", 15, 2);
      $table->string("payment_method")->nullable();
      $table->string("reference_number")->nullable();
      $table->text("notes")->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("payments");
  }
};
