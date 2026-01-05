<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Many existing databases were created manually (no Laravel users migration).
        // In some cases, `users.role` is an ENUM that only allows 'admin', causing inserts
        // with 'petani' to fail (500). We normalize by converting it to VARCHAR.
        //
        // This is safe for dev/demo and keeps role validation in the controller.

        try {
            // Use NULLable to avoid failing when legacy rows contain NULL roles.
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NULL DEFAULT 'petani'");
        } catch (\Throwable $e) {
            // If the table/column doesn't exist or DB differs, don't block migrations.
        }
    }

    public function down(): void
    {
        // We intentionally do not revert back to ENUM because the original type is unknown.
    }
};
