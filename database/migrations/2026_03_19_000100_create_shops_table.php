<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postcode')->nullable()->index();
            $table->string('country_code', 2)->default('GB');
            $table->string('timezone')->default('Europe/London');
            $table->json('opening_hours_json')->nullable();
            $table->text('emergency_note')->nullable();
            $table->json('shop_brand_override_json')->nullable();
            $table->json('policy_override_json')->nullable();
            $table->json('gallery_media_json')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
