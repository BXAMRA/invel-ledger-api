<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("documents", function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid("customer_id")->constrained()->cascadeOnDelete();
      $table->string("type");
      $table->string("document_number")->unique();
      $table->date("issue_date");
      $table->date("due_date")->nullable();
      $table->string("status")->default("draft");
      $table->json("attachments")->nullable();
      $table->decimal("subtotal", 15, 2)->default(0);
      $table->decimal("discount_flat", 15, 2)->default(0);
      $table->decimal("discount_percentage", 5, 2)->default(0);
      $table->decimal("tax_total", 15, 2)->default(0);
      $table->decimal("grand_total", 15, 2)->default(0);
      $table->decimal("paid_total", 15, 2)->default(0);
      $table->decimal("balance", 15, 2)->default(0);
      $table->text("notes")->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("documents");
  }
};
