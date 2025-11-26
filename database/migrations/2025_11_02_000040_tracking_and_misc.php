<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // tracking_sessions
        Schema::create('tracking_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('job_id')->index();
            $table->uuid('customer_id')->index();
            $table->string('share_token')->unique();
            $table->enum('status', ['active','ended','expired'])->default('active')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('last_eta_minutes')->nullable();
            $table->integer('stops_ahead')->nullable();
            $table->timestamps();
        });

        // location_pings
        Schema::create('location_pings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tracking_session_id')->index();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('speed', 6, 2)->nullable();
            $table->decimal('heading', 6, 2)->nullable();
            $table->decimal('accuracy', 6, 2)->nullable();
            $table->timestampTz('ts');
            $table->index(['tracking_session_id','ts']);
        });

        // webhook_events
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('source', ['stripe','bacs','internal'])->index();
            $table->string('event_type');
            $table->json('payload_json');
            $table->enum('status', ['received','processed','failed'])->default('received')->index();
            $table->integer('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        // exports
        Schema::create('exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->enum('type', ['xero','qbo','csv'])->index();
            $table->timestamp('range_start')->nullable();
            $table->timestamp('range_end')->nullable();
            $table->string('file_url')->nullable();
            $table->enum('status', ['pending','ready','failed'])->default('pending')->index();
            $table->timestamps();
        });

        // areas
        Schema::create('areas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->json('geojson')->nullable();
            $table->json('postcodes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('label');
            $table->string('plate')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('color')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // fees_config
        Schema::create('fees_config', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->unique();
            $table->decimal('app_fee_percent', 5, 2)->default(0);
            $table->integer('app_fee_fixed_pence')->default(0);
            $table->boolean('pass_card_fee_to_customer')->default(false);
            $table->boolean('instant_payout_enabled')->default(false);
            $table->timestamps();
        });

        // settings
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('key')->index();
            $table->json('value_json')->nullable();
            $table->timestamps();
        });

        // audit_logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('actor_user_id')->nullable()->index();
            $table->string('action');
            $table->string('entity');
            $table->uuid('entity_id')->nullable();
            $table->json('diff_json')->nullable();
            $table->string('ip')->nullable();
            $table->string('ua')->nullable();
            $table->timestampTz('ts')->useCurrent();
        });

        // Partial index (Postgres only). Skip on MySQL.
        $driver = config('database.default');
        if ($driver === 'pgsql') {
            DB::statement("CREATE INDEX IF NOT EXISTS jobs_date_active_status_idx ON jobs (date) WHERE status IN ('scheduled','en_route','arrived')");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('fees_config');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('location_pings');
        Schema::dropIfExists('tracking_sessions');
    }
};
