<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('luma_kiosk_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('device_id')->index();
            $table->uuid('tenant_id')->index();
            $table->uuid('shop_id')->nullable()->index();
            $table->json('events_json');
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();

            $table->foreign('device_id')->references('id')->on('devices')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('luma_kiosk_sessions');
    }
};
