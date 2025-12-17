<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sensor_notifications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // kalau notif per-user
      $table->string('level', 20);   // warning | danger
      $table->string('title');
      $table->text('message');
      $table->boolean('is_read')->default(false);
      $table->timestamps();

      $table->unsignedInteger('user_id')->nullable();
      $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

    });
  }
  public function down(): void {
    Schema::dropIfExists('sensor_notifications');
  }
};
