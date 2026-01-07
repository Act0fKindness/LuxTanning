<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('trading_name')->nullable()->after('name');
            $table->string('legal_name')->nullable()->after('trading_name');
            $table->string('company_reg_number')->nullable()->after('legal_name');
            $table->string('vat_number')->nullable()->after('company_reg_number');
            $table->string('hq_address_line1')->nullable()->after('vat_number');
            $table->string('hq_address_line2')->nullable()->after('hq_address_line1');
            $table->string('hq_city')->nullable()->after('hq_address_line2');
            $table->string('hq_county')->nullable()->after('hq_city');
            $table->string('hq_postcode')->nullable()->after('hq_county');
            $table->string('hq_country_code', 2)->default('GB')->after('hq_postcode');
            $table->string('contact_email')->nullable()->after('hq_country_code');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->string('support_email')->nullable()->after('contact_phone');
            $table->string('support_phone')->nullable()->after('support_email');
            $table->string('website_url')->nullable()->after('support_phone');
            $table->json('social_links_json')->nullable()->after('website_url');
            $table->string('default_currency', 3)->default('GBP')->after('social_links_json');
            $table->string('default_timezone')->default('Europe/London')->after('default_currency');
            $table->string('default_language', 5)->default('en-GB')->after('default_timezone');
            $table->json('brand_json')->nullable()->after('default_language');
            $table->unsignedBigInteger('logo_media_id')->nullable()->after('brand_json');
            $table->unsignedBigInteger('favicon_media_id')->nullable()->after('logo_media_id');
            $table->unsignedBigInteger('receipt_logo_media_id')->nullable()->after('favicon_media_id');
            $table->string('plan_key')->default('trial')->after('receipt_logo_media_id');
            $table->enum('plan_status', ['trial','active','past_due','cancelled'])->default('trial')->after('plan_key');
            $table->timestamp('trial_ends_at')->nullable()->after('plan_status');
            $table->string('billing_provider')->nullable()->after('trial_ends_at');
            $table->string('billing_customer_id')->nullable()->after('billing_provider');
            $table->unsignedTinyInteger('onboarding_step')->default(0)->after('billing_customer_id');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_step');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('logo_media_id')->references('id')->on('glint_media')->nullOnDelete();
            $table->foreign('favicon_media_id')->references('id')->on('glint_media')->nullOnDelete();
            $table->foreign('receipt_logo_media_id')->references('id')->on('glint_media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['logo_media_id']);
            $table->dropForeign(['favicon_media_id']);
            $table->dropForeign(['receipt_logo_media_id']);

            $table->dropColumn([
                'trading_name',
                'legal_name',
                'company_reg_number',
                'vat_number',
                'hq_address_line1',
                'hq_address_line2',
                'hq_city',
                'hq_county',
                'hq_postcode',
                'hq_country_code',
                'contact_email',
                'contact_phone',
                'support_email',
                'support_phone',
                'website_url',
                'social_links_json',
                'default_currency',
                'default_timezone',
                'default_language',
                'brand_json',
                'logo_media_id',
                'favicon_media_id',
                'receipt_logo_media_id',
                'plan_key',
                'plan_status',
                'trial_ends_at',
                'billing_provider',
                'billing_customer_id',
                'onboarding_step',
                'onboarding_completed_at',
            ]);
        });
    }
};
