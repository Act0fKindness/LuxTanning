<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // staff_profiles
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('user_id')->index();
            $table->json('right_to_work_json')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->uuid('vehicle_id')->nullable();
            $table->json('availability_json')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // customers
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('marketing_opt_in')->default(false);
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('default_address_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // addresses
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('customer_id')->index();
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postcode')->index();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('access_notes')->nullable();
            $table->string('door_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['tenant_id','postcode']);
        });

        // price_matrices
        Schema::create('price_matrices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->json('rules_json');
            $table->decimal('first_clean_factor', 5, 2)->default(1.00);
            $table->softDeletes();
            $table->timestamps();
        });

        // bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('customer_id')->index();
            $table->uuid('address_id')->index();
            $table->enum('status', ['confirmed','cancelled'])->default('confirmed')->index();
            $table->enum('channel', ['web','admin'])->default('web');
            $table->string('source')->nullable();
            $table->json('quote_json');
            $table->integer('deposit_pence')->default(0);
            $table->timestamp('tcs_accepted_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('customer_id')->index();
            $table->uuid('address_id')->index();
            $table->enum('cadence', ['one_off','four_week','six_week','eight_week','monthly'])->index();
            $table->timestamp('next_due_at')->nullable();
            $table->enum('payment_method', ['card','bacs'])->default('card');
            $table->enum('status', ['active','paused','cancelled'])->default('active')->index();
            $table->decimal('risk_score', 5, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // routes
        Schema::create('routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->date('date')->index();
            $table->string('name')->nullable();
            $table->uuid('vehicle_id')->nullable();
            $table->json('sequence_json')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // jobs
        Schema::create('jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('booking_id')->nullable()->index();
            $table->uuid('subscription_id')->nullable()->index();
            $table->uuid('route_id')->nullable()->index();
            $table->date('date')->index();
            $table->string('eta_window')->nullable();
            $table->enum('status', ['scheduled','en_route','arrived','started','completed','cancelled'])->default('scheduled')->index();
            $table->integer('sequence')->nullable();
            $table->json('checklist_json')->nullable();
            $table->enum('required_photos', ['before','after','both'])->nullable();
            $table->integer('no_access_fee_pence')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // job_photos
        Schema::create('job_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('job_id')->index();
            $table->enum('type', ['before','after','other'])->default('other');
            $table->string('url');
            $table->timestamp('taken_at')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('checksum')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_photos');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('price_matrices');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('staff_profiles');
    }
};
