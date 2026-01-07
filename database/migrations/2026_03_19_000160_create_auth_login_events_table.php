<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auth_login_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_id')->nullable()->index();
            $table->string('email');
            $table->boolean('success')->default(false);
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_login_events');
    }
};
