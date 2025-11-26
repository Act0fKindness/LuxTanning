<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = config('database.default');
        if ($driver === 'mysql' && Schema::hasTable('users')) {
            // Drop unique (tenant_id, is_owner) and the generated column if it exists
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique('uniq_one_owner_per_tenant');
                });
            } catch (\Throwable $e) {}
            try {
                \DB::statement("ALTER TABLE `users` DROP COLUMN `is_owner`");
            } catch (\Throwable $e) {}
        } elseif ($driver === 'pgsql') {
            try { \DB::statement("DROP INDEX IF EXISTS uniq_one_owner_per_tenant"); } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // No-op: uniqueness is now enforced on tenant_user
    }
};

