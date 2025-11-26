<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tracking_sessions')) {
            Schema::table('tracking_sessions', function (Blueprint $table) {
                if (!Schema::hasColumn('tracking_sessions', 'cleaner_id')) {
                    $table->uuid('cleaner_id')->nullable()->after('job_id')->index();
                }
                if (!Schema::hasColumn('tracking_sessions', 'phase')) {
                    $table->string('phase', 20)->default('enroute')->after('cleaner_id')->index();
                }
                if (!Schema::hasColumn('tracking_sessions', 'ended_at')) {
                    $table->timestamp('ended_at')->nullable()->after('started_at')->index();
                }
                if (!Schema::hasColumn('tracking_sessions', 'meta')) {
                    $table->json('meta')->nullable()->after('ended_at');
                }
            });
        }

        if (Schema::hasTable('location_pings')) {
            Schema::table('location_pings', function (Blueprint $table) {
                if (!Schema::hasColumn('location_pings', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tracking_sessions')) {
            Schema::table('tracking_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('tracking_sessions', 'meta')) {
                    $table->dropColumn('meta');
                }
                if (Schema::hasColumn('tracking_sessions', 'ended_at')) {
                    $table->dropColumn('ended_at');
                }
                if (Schema::hasColumn('tracking_sessions', 'phase')) {
                    $table->dropColumn('phase');
                }
                if (Schema::hasColumn('tracking_sessions', 'cleaner_id')) {
                    $table->dropColumn('cleaner_id');
                }
            });
        }

        if (Schema::hasTable('location_pings')) {
            Schema::table('location_pings', function (Blueprint $table) {
                if (Schema::hasColumn('location_pings', 'created_at')) {
                    $table->dropColumn(['created_at', 'updated_at']);
                }
            });
        }
    }
};
