<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("customers", function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string("company_name");
      $table->string("slug")->nullable();
      $table->string("contact_person")->nullable();
      $table->string("contact_email")->nullable();
      $table->string("contact_phone")->nullable();
      $table->string("email")->nullable();
      $table->string("phone")->nullable();
      $table->string("gst")->nullable();
      $table->string("pan")->nullable();
      $table->string("address_line1")->nullable();
      $table->string("address_line2")->nullable();
      $table->string("city")->nullable();
      $table->string("state")->nullable();
      $table->string("country")->nullable();
      $table->string("pincode")->nullable();
      $table->string("website")->nullable();
      $table->text("notes")->nullable();
      $table->softDeletes();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("customers");
  }
};
