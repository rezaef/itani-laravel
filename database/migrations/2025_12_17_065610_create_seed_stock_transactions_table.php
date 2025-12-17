<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('seed_stock_transactions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('bibit_id');      // id_bibit (SRS)
      $table->unsignedBigInteger('periode_id')->nullable();
      $table->unsignedBigInteger('user_id')->nullable();

      $table->enum('jenis_transaksi', ['IN', 'OUT']); // SRS
      $table->unsignedInteger('jumlah_pakai');        // SRS
      $table->dateTime('tanggal_transaksi');          // SRS
      $table->string('catatan', 255)->nullable();

      $table->timestamps();

      $table->index(['bibit_id', 'tanggal_transaksi']);
      $table->index(['periode_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('seed_stock_transactions');
  }
};
