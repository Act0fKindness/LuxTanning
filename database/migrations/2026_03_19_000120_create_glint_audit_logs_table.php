<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('glint_audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('tenant_id')->index();
            $table->uuid('shop_id')->nullable()->index();
            $table->enum('actor_type', ['user','device','system']);
            $table->uuid('actor_id')->nullable();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->uuid('entity_id')->nullable();
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['shop_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glint_audit_logs');
    }
};
