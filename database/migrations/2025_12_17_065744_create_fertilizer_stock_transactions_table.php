<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('fertilizer_stock_transactions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('fertilizer_id');
      $table->unsignedBigInteger('periode_id')->nullable();
      $table->unsignedBigInteger('user_id')->nullable();

      $table->enum('jenis_transaksi', ['IN', 'OUT']);
      $table->unsignedInteger('jumlah');
      $table->dateTime('tanggal_transaksi');
      $table->string('catatan', 255)->nullable();

      $table->timestamps();

      $table->index(['fertilizer_id', 'tanggal_transaksi']);
      $table->index(['periode_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('fertilizer_stock_transactions');
  }
};
