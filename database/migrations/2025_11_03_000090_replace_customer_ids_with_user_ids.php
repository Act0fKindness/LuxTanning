<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // addresses: add user_id, drop customer_id
        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'user_id')) {
                $table->uuid('user_id')->nullable()->index()->after('tenant_id');
            }
        });
        // If desired, drop old customer_id column
        if (Schema::hasColumn('addresses', 'customer_id')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->dropColumn('customer_id');
            });
        }

        // bookings: rename customer_id -> user_id
        if (Schema::hasColumn('bookings', 'customer_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->renameColumn('customer_id', 'user_id');
            });
        }

        // subscriptions: rename customer_id -> user_id
        if (Schema::hasColumn('subscriptions', 'customer_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->renameColumn('customer_id', 'user_id');
            });
        }

        // invoices: rename customer_id -> user_id
        if (Schema::hasColumn('invoices', 'customer_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->renameColumn('customer_id', 'user_id');
            });
        }

        // messages: rename customer_id -> user_id
        if (Schema::hasColumn('messages', 'customer_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->renameColumn('customer_id', 'user_id');
            });
        }

        // tracking_sessions: rename customer_id -> user_id
        if (Schema::hasColumn('tracking_sessions', 'customer_id')) {
            Schema::table('tracking_sessions', function (Blueprint $table) {
                $table->renameColumn('customer_id', 'user_id');
            });
        }
    }

    public function down(): void
    {
        // addresses: restore customer_id and drop user_id
        if (!Schema::hasColumn('addresses', 'customer_id')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->uuid('customer_id')->nullable()->index()->after('tenant_id');
            });
        }
        if (Schema::hasColumn('addresses', 'user_id')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }

        // Revert renames
        if (Schema::hasColumn('bookings', 'user_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->renameColumn('user_id', 'customer_id');
            });
        }
        if (Schema::hasColumn('subscriptions', 'user_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->renameColumn('user_id', 'customer_id');
            });
        }
        if (Schema::hasColumn('invoices', 'user_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->renameColumn('user_id', 'customer_id');
            });
        }
        if (Schema::hasColumn('messages', 'user_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->renameColumn('user_id', 'customer_id');
            });
        }
        if (Schema::hasColumn('tracking_sessions', 'user_id')) {
            Schema::table('tracking_sessions', function (Blueprint $table) {
                $table->renameColumn('user_id', 'customer_id');
            });
        }
    }
};

