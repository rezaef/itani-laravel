<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
{
    Schema::create('sensor_notifications', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->index('user_id');
        $table->string('level', 20);     // info|warn|danger
        $table->string('title');
        $table->text('message');
        $table->boolean('is_read')->default(false);
        $table->timestamps();

        $table->index(['user_id', 'is_read', 'created_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('sensor_notifications');
}

};
