<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed admin minimal
        if (Schema::hasTable('users')) {
            // ambil daftar kolom users agar aman kalau schema beda-beda
            $cols = array_map(fn($o) => $o->Field, DB::select("SHOW COLUMNS FROM users"));

            $payload = [
                'name'          => 'Admin',
                'username'      => 'admin',
                'role'          => 'admin',
                'password_hash' => Hash::make('admin123'),
            ];

            $now = now();
            if (in_array('created_at', $cols, true)) $payload['created_at'] = $now;
            if (in_array('updated_at', $cols, true)) $payload['updated_at'] = $now;

            DB::table('users')->updateOrInsert(['username' => 'admin'], $payload);
        }

        // Seed stok contoh (opsional tapi enak buat demo)
        if (Schema::hasTable('seed_stock') && DB::table('seed_stock')->count() === 0) {
            DB::table('seed_stock')->insert([
                [
                    'seed_name' => 'Okra Merah',
                    'seed_type' => 'Benih',
                    'stock_in' => 100, 'stock_out' => 0, 'stock_remaining' => 100,
                    'updated_at' => now(),
                ],
            ]);
        }

        if (Schema::hasTable('fertilizer_stock') && DB::table('fertilizer_stock')->count() === 0) {
            DB::table('fertilizer_stock')->insert([
                [
                    'fert_name' => 'NPK 16-16-16',
                    'fert_type' => 'Pupuk',
                    'stock_in_kg' => 50, 'stock_out_kg' => 0, 'stock_remaining_kg' => 50,
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}

