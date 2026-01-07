<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('glint_media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('tenant_id')->index();
            $table->uuid('shop_id')->nullable()->index();
            $table->string('purpose');
            $table->string('disk')->default('public');
            $table->string('storage_path');
            $table->string('original_filename')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->uuid('created_by_user_id')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'purpose']);
            $table->index(['shop_id', 'purpose']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glint_media');
    }
};
