<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // users.tenant_id -> tenants.id
        Schema::table('users', function (Blueprint $table) {
            if (!app()->runningUnitTests()) {
                try {
                    $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
                } catch (\Throwable $e) { /* ignore if already exists */ }
            }
        });

        // addresses.user_id -> users.id; addresses.tenant_id -> tenants.id
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses','user_id')) {
                try { $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); } catch (\Throwable $e) {}
            }
            try { $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); } catch (\Throwable $e) {}
        });

        // jobs.staff_user_id -> users.id; jobs.tenant_id -> tenants.id
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs','staff_user_id')) {
                try { $table->foreign('staff_user_id')->references('id')->on('users')->nullOnDelete(); } catch (\Throwable $e) {}
            }
            try { $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); } catch (\Throwable $e) {}
        });

        // bookings
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings','user_id')) { try { $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); } catch (\Throwable $e) {} }
                if (Schema::hasColumn('bookings','address_id')) { try { $table->foreign('address_id')->references('id')->on('addresses')->cascadeOnDelete(); } catch (\Throwable $e) {} }
                try { $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); } catch (\Throwable $e) {}
            });
        }

        // subscriptions
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                if (Schema::hasColumn('subscriptions','user_id')) { try { $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); } catch (\Throwable $e) {} }
                if (Schema::hasColumn('subscriptions','address_id')) { try { $table->foreign('address_id')->references('id')->on('addresses')->cascadeOnDelete(); } catch (\Throwable $e) {} }
                try { $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); } catch (\Throwable $e) {}
            });
        }

        // invoices
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices','user_id')) { try { $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); } catch (\Throwable $e) {} }
                try { $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); } catch (\Throwable $e) {}
            });
        }

        // messages
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (Schema::hasColumn('messages','user_id')) { try { $table->foreign('user_id')->references('id')->on('users')->nullOnDelete(); } catch (\Throwable $e) {} }
                try { $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); } catch (\Throwable $e) {}
            });
        }

        // tracking_sessions
        if (Schema::hasTable('tracking_sessions')) {
            Schema::table('tracking_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('tracking_sessions','user_id')) { try { $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); } catch (\Throwable $e) {} }
                if (Schema::hasColumn('tracking_sessions','job_id')) { try { $table->foreign('job_id')->references('id')->on('jobs')->cascadeOnDelete(); } catch (\Throwable $e) {} }
                try { $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); } catch (\Throwable $e) {}
            });
        }
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            try { $table->dropForeign(['staff_user_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['tenant_id']); } catch (\Throwable $e) {}
        });
        Schema::table('addresses', function (Blueprint $table) {
            try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['tenant_id']); } catch (\Throwable $e) {}
        });
        Schema::table('users', function (Blueprint $table) {
            try { $table->dropForeign(['tenant_id']); } catch (\Throwable $e) {}
        });

        // Drop added FKs on other tables
        foreach (['bookings','subscriptions','invoices','messages','tracking_sessions'] as $tbl) {
            if (!Schema::hasTable($tbl)) continue;
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                foreach (['user_id','address_id','tenant_id','job_id'] as $col) {
                    if (Schema::hasColumn($tbl, $col)) {
                        try { $table->dropForeign([$col]); } catch (\Throwable $e) {}
                    }
                }
            });
        }
    }
};
