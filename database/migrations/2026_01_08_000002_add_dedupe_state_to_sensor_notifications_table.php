<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sensor_notifications', function (Blueprint $table) {
            // Kunci untuk 1 notifikasi per jenis (misal: humi/temp/ph/ec/n/p/k)
            $table->string('dedupe_key', 50)->nullable()->after('user_id');

            // normal | warning_low | warning_high | danger_low | danger_high
            $table->string('state', 20)->default('normal')->after('message');

            // Berapa kali status berubah (misal warning -> danger) sebagai "kejadian" baru
            $table->unsignedInteger('occurrences')->default(1)->after('state');

            // Terakhir kali nilai sensor terlihat (untuk update isi pesan tanpa spam)
            $table->timestamp('last_seen_at')->nullable()->after('occurrences');

            // Terakhir kali dianggap kejadian baru (dipakai untuk toast di UI)
            $table->timestamp('last_triggered_at')->nullable()->after('last_seen_at');

            $table->index(['dedupe_key', 'state']);
            $table->index(['dedupe_key', 'is_read']);
        });

        // Data lama (sebelum sistem dedupe) kita anggap legacy: supaya badge tidak meledak.
        DB::table('sensor_notifications')
            ->whereNull('dedupe_key')
            ->update(['is_read' => true]);
    }

    public function down(): void
    {
        Schema::table('sensor_notifications', function (Blueprint $table) {
            $table->dropIndex(['dedupe_key', 'state']);
            $table->dropIndex(['dedupe_key', 'is_read']);

            $table->dropColumn(['dedupe_key', 'state', 'occurrences', 'last_seen_at', 'last_triggered_at']);
        });
    }
};
