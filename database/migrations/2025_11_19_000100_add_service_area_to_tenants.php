<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('service_area_label')->nullable()->after('vat_scheme');
            $table->string('service_area_place_id')->nullable()->after('service_area_label');
            $table->decimal('service_area_center_lat', 10, 7)->nullable()->after('service_area_place_id');
            $table->decimal('service_area_center_lng', 10, 7)->nullable()->after('service_area_center_lat');
            $table->decimal('service_area_radius_km', 6, 2)->nullable()->after('service_area_center_lng');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'service_area_label',
                'service_area_place_id',
                'service_area_center_lat',
                'service_area_center_lng',
                'service_area_radius_km',
            ]);
        });
    }
};
