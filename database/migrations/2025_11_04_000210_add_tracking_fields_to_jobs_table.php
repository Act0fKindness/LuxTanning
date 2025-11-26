<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('jobs', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('jobs', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('jobs', 'actual_minutes')) {
                $table->integer('actual_minutes')->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('jobs', 'last_lat')) {
                $table->decimal('last_lat', 10, 7)->nullable()->after('actual_minutes');
            }
            if (!Schema::hasColumn('jobs', 'last_lng')) {
                $table->decimal('last_lng', 10, 7)->nullable()->after('last_lat');
            }
            if (!Schema::hasColumn('jobs', 'last_location_at')) {
                $table->timestamp('last_location_at')->nullable()->after('last_lng');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs', 'last_location_at')) {
                $table->dropColumn('last_location_at');
            }
            if (Schema::hasColumn('jobs', 'last_lng')) {
                $table->dropColumn('last_lng');
            }
            if (Schema::hasColumn('jobs', 'last_lat')) {
                $table->dropColumn('last_lat');
            }
            if (Schema::hasColumn('jobs', 'actual_minutes')) {
                $table->dropColumn('actual_minutes');
            }
            if (Schema::hasColumn('jobs', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
            if (Schema::hasColumn('jobs', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            if (Schema::hasColumn('jobs', 'started_at')) {
                $table->dropColumn('started_at');
            }
        });
    }
};
