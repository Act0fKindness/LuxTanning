<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Safety: only drop when explicitly allowed
        $allow = env('GLINT_DROP_LEGACY_CUSTOMERS', false);
        if ($allow && Schema::hasTable('customers')) {
            Schema::drop('customers');
        }
    }

    public function down(): void
    {
        // Not recreating legacy table; superseded by users + user_profiles
    }
};
