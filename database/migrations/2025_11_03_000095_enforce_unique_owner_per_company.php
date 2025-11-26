<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = config('database.default');

        if ($driver === 'mysql') {
            // Virtual generated column that is 1 for owners, NULL otherwise (allows multiple NULLs)
            Schema::table('users', function (Blueprint $table) {
                DB::statement("ALTER TABLE `users` ADD COLUMN `is_owner` TINYINT(1) GENERATED ALWAYS AS (CASE WHEN `role` = 'owner' THEN 1 ELSE NULL END) VIRTUAL");
            });
            Schema::table('users', function (Blueprint $table) {
                $table->unique(['tenant_id','is_owner'], 'uniq_one_owner_per_tenant');
            });
        } elseif ($driver === 'pgsql') {
            // Postgres: partial unique index
            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS uniq_one_owner_per_tenant ON users (tenant_id) WHERE role = 'owner'");
        } else {
            // Other drivers: cannot enforce at DB level; skip but keep app-level validation
        }
    }

    public function down(): void
    {
        $driver = config('database.default');
        if ($driver === 'mysql') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('uniq_one_owner_per_tenant');
            });
            Schema::table('users', function (Blueprint $table) {
                DB::statement("ALTER TABLE `users` DROP COLUMN `is_owner`");
            });
        } elseif ($driver === 'pgsql') {
            DB::statement("DROP INDEX IF EXISTS uniq_one_owner_per_tenant");
        }
    }
};

