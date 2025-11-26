<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'booking_number')) {
                $table->string('booking_number')->nullable()->unique()->after('id');
            }
        });

        DB::table('bookings')->orderBy('created_at')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                if ($row->booking_number) {
                    continue;
                }

                $number = $this->makeBookingNumber($row->tenant_id ?? null);
                DB::table('bookings')
                    ->where('id', $row->id)
                    ->update(['booking_number' => $number]);
            }
        });

        DB::table('bookings')->whereNull('booking_number')->update([
            'booking_number' => $this->makeBookingNumber(null),
        ]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'booking_number')) {
                $table->dropUnique('bookings_booking_number_unique');
                $table->dropColumn('booking_number');
            }
        });
    }

    private function makeBookingNumber(?string $tenantId): string
    {
        $prefix = $tenantId ? strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $tenantId), 0, 4)) : 'GLNT';
        if ($prefix === '') {
            $prefix = 'GLNT';
        } elseif (strlen($prefix) < 4) {
            $prefix = str_pad($prefix, 4, 'X');
        }

        do {
            $candidate = sprintf('%s-%s', $prefix, strtoupper(Str::random(6)));
            $exists = DB::table('bookings')->where('booking_number', $candidate)->exists();
        } while ($exists);

        return $candidate;
    }
};

