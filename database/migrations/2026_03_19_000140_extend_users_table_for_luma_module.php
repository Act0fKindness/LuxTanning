<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['invited','active','disabled'])->default('invited')->after('role');
            $table->uuid('primary_shop_id')->nullable()->after('tenant_id');
            $table->enum('shop_access_mode', ['all','selected','single'])->default('single')->after('primary_shop_id');
            $table->uuid('invited_by_user_id')->nullable()->after('shop_access_mode');
            $table->string('invite_token_hash', 64)->nullable()->unique()->after('invited_by_user_id');
            $table->timestamp('invite_expires_at')->nullable()->after('invite_token_hash');
            $table->timestamp('invite_accepted_at')->nullable()->after('invite_expires_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->boolean('mfa_enabled')->default(false)->after('password');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('primary_shop_id')->references('id')->on('shops')->nullOnDelete();
            $table->foreign('invited_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        DB::table('users')->update(['status' => 'active']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('platform_admin','owner','manager','cleaner','accountant','customer','glint_super_admin','org_owner','shop_manager','staff') NOT NULL DEFAULT 'customer'");

        DB::table('users')->where('role', 'platform_admin')->update(['role' => 'glint_super_admin']);
        DB::table('users')->where('role', 'owner')->update(['role' => 'org_owner']);
        DB::table('users')->where('role', 'manager')->update(['role' => 'shop_manager']);
        DB::table('users')->whereIn('role', ['cleaner', 'accountant'])->update(['role' => 'staff']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('glint_super_admin','org_owner','shop_manager','staff','customer') NOT NULL DEFAULT 'staff'");

        Schema::table('users', function (Blueprint $table) {
            $table->index(['tenant_id', 'role']);
            $table->index(['tenant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['primary_shop_id']);
            $table->dropForeign(['invited_by_user_id']);
            $table->dropIndex(['users_tenant_id_role_index']);
            $table->dropIndex(['users_tenant_id_email_index']);
            $table->dropColumn([
                'status',
                'primary_shop_id',
                'shop_access_mode',
                'invited_by_user_id',
                'invite_token_hash',
                'invite_expires_at',
                'invite_accepted_at',
                'last_login_ip',
                'mfa_enabled',
            ]);
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('platform_admin','owner','manager','cleaner','accountant','customer','glint_super_admin','org_owner','shop_manager','staff') NOT NULL DEFAULT 'customer'");

        DB::table('users')->where('role', 'glint_super_admin')->update(['role' => 'platform_admin']);
        DB::table('users')->where('role', 'org_owner')->update(['role' => 'owner']);
        DB::table('users')->where('role', 'shop_manager')->update(['role' => 'manager']);
        DB::table('users')->where('role', 'staff')->update(['role' => 'cleaner']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('platform_admin','owner','manager','cleaner','accountant','customer') NOT NULL DEFAULT 'customer'");
    }
};
