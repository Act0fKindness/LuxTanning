<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('user_id')->index();
            $table->enum('role', ['owner','manager','cleaner','accountant','customer'])->index();
            $table->enum('status', ['active','invited','disabled'])->default('active')->index();
            $table->timestamps();
            $table->unique(['tenant_id','user_id']);
        });

        // Enforce 1 owner per tenant
        $driver = config('database.default');
        if ($driver === 'mysql') {
            // Generated column for owner uniqueness
            DB::statement("ALTER TABLE `tenant_user` ADD COLUMN `is_owner` TINYINT(1) GENERATED ALWAYS AS (CASE WHEN `role` = 'owner' THEN 1 ELSE NULL END) VIRTUAL");
            Schema::table('tenant_user', function (Blueprint $table) {
                $table->unique(['tenant_id','is_owner'], 'uniq_one_owner_per_tenant');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS uniq_one_owner_per_tenant ON tenant_user (tenant_id) WHERE role = 'owner'");
        }
    }

    public function down(): void
    {
        $driver = config('database.default');
        if ($driver === 'mysql') {
            Schema::table('tenant_user', function (Blueprint $table) {
                try { $table->dropUnique('uniq_one_owner_per_tenant'); } catch (\Throwable $e) {}
            });
            try { DB::statement("ALTER TABLE `tenant_user` DROP COLUMN `is_owner`"); } catch (\Throwable $e) {}
        } elseif ($driver === 'pgsql') {
            DB::statement("DROP INDEX IF EXISTS uniq_one_owner_per_tenant");
        }
        Schema::dropIfExists('tenant_user');
    }
};

