<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // payments
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('job_id')->nullable()->index();
            $table->uuid('invoice_id')->nullable()->index();
            $table->enum('method', ['card','bacs','cash'])->default('card');
            $table->integer('amount_pence');
            $table->integer('application_fee_pence')->default(0);
            $table->integer('processor_fee_pence')->default(0);
            $table->string('stripe_charge_id')->nullable();
            $table->enum('status', ['pending','succeeded','failed','refunded','disputed'])->default('pending')->index();
            $table->integer('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('customer_id')->index();
            $table->string('number');
            $table->json('totals_json');
            $table->string('pdf_url')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['draft','issued','paid','void'])->default('draft')->index();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id','number']);
        });

        // payouts
        Schema::create('payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('stripe_payout_id')->index();
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->integer('amount_pence');
            $table->integer('fee_pence')->default(0);
            $table->enum('status', ['paid','failed','in_transit'])->default('in_transit')->index();
            $table->string('report_url')->nullable();
            $table->timestamps();
        });

        // disputes
        Schema::create('disputes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('payment_id')->index();
            $table->string('reason')->nullable();
            $table->json('evidence_json')->nullable();
            $table->enum('status', ['open','submitted','won','lost'])->default('open')->index();
            $table->integer('amount_pence')->default(0);
            $table->string('outcome')->nullable();
            $table->timestamps();
        });

        // messages (provider-agnostic)
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('customer_id')->nullable()->index();
            $table->enum('channel', ['email','sms'])->index();
            $table->string('template_key')->nullable();
            $table->string('to_addr');
            $table->text('body');
            $table->enum('status', ['queued','sent','failed'])->default('queued')->index();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // Composite index: driver-specific syntax
        $driver = config('database.default');
        if ($driver === 'pgsql') {
            DB::statement("CREATE INDEX IF NOT EXISTS payments_status_created_at_idx ON payments (status, created_at)");
        } else {
            Schema::table('payments', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'payments_status_created_at_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
    }
};
