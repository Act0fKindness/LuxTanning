<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AokJobsSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = DB::table('tenants')->where('slug', 'aok-world')->first();
        if (!$tenant) {
            $tenantId = (string) Str::uuid();
            DB::table('tenants')->insert([
                'id' => $tenantId,
                'name' => 'AOK World',
                'slug' => 'aok-world',
                'status' => 'active',
                'country' => 'GB',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        }

        $tenantId = $tenant->id;

        // Keep the sample data predictable on reseed
        DB::table('jobs')->where('tenant_id', $tenantId)->delete();

        $sampleCustomerIds = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('name', 'like', 'AOK Test Customer %')
            ->pluck('id');

        if ($sampleCustomerIds->isNotEmpty()) {
            DB::table('addresses')->whereIn('customer_id', $sampleCustomerIds)->delete();
            DB::table('customers')->whereIn('id', $sampleCustomerIds)->delete();
        }

        $cleanerIds = DB::table('users')
            ->where('tenant_id', $tenantId)
            ->where('role', 'cleaner')
            ->pluck('id')
            ->values();

        if ($cleanerIds->isEmpty()) {
            $fallbackId = (string) Str::uuid();
            DB::table('users')->insert([
                'id' => $fallbackId,
                'tenant_id' => $tenantId,
                'name' => 'Sample Cleaner',
                'email' => 'sample.cleaner+aok@glintlabs.dev',
                'role' => 'cleaner',
                'password' => bcrypt('ChangeMe123!'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $cleanerIds = collect([$fallbackId]);
        }

        $jobCount = 50;
        $days = 7;
        $baseLat = 51.2416;   // 3 Hayward Road, ME17 3GA approx
        $baseLng = 0.6086;
        $radiusMeters = 8047; // ~5 miles
        $streets = [
            'Hayward Road',
            'Eyhorne Street',
            'Greenway',
            'Abbey Road',
            'Buddle Drive',
            'Greenfields Drive',
            'Windmill Lane',
            'Hollingbourne Hill',
            'Musket Lane',
            'Oak Tree Close',
        ];
        $frequencies = ['four_week', 'six_week', 'eight_week'];

        // Quote rules
        $windowPrice = 2.5;
        $extraFrames = 4;
        $extraSills = 3;
        $extraGutters = 25;
        $modStoreys = [1 => 0.00, 2 => 0.10, 3 => 0.20];
        $modFrequency = ['four_week' => -0.05, 'six_week' => 0.00, 'eight_week' => 0.05];
        $firstVisitFactor = 1.25;
        $vatRate = 0.2;
        $minCallout = 15;
        $rounding = 0.5;
        $timePerWindow = 2.2; // minutes per window
        $timeGutters = 25;

        $startDate = Carbon::today();
        $jobsRemaining = $jobCount;
        $sequence = 1;

        for ($day = 0; $day < $days; $day++) {
            $date = $startDate->copy()->addDays($day);
            $jobsForDay = intdiv($jobCount, $days) + ($day < ($jobCount % $days) ? 1 : 0);
            $jobsForDay = min($jobsForDay, $jobsRemaining);
            $jobsRemaining -= $jobsForDay;

            $cursor = $date->copy()->setTime(8, 0);

            for ($i = 0; $i < $jobsForDay; $i++, $sequence++) {
                $coords = $this->randomPointWithinRadius($baseLat, $baseLng, $radiusMeters);
                $street = $streets[array_rand($streets)];
                $houseNumber = random_int(2, 180);
                $addressLine = sprintf('%d %s', $houseNumber, $street);
                $postcode = 'ME17 3GA';

                $customerId = (string) Str::uuid();
                DB::table('customers')->insert([
                    'id' => $customerId,
                    'tenant_id' => $tenantId,
                    'name' => 'AOK Test Customer ' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
                    'email' => null,
                    'phone' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $addressId = (string) Str::uuid();
                DB::table('addresses')->insert([
                    'id' => $addressId,
                    'tenant_id' => $tenantId,
                    'customer_id' => $customerId,
                    'line1' => $addressLine,
                    'city' => 'Maidstone',
                    'county' => 'Kent',
                    'postcode' => strtoupper($postcode),
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('customers')->where('id', $customerId)->update(['default_address_id' => $addressId]);

                $storeys = random_int(1, 3);
                $windows = random_int(12, 32);
                $frames = (bool) random_int(0, 1);
                $sills = (bool) random_int(0, 1);
                $gutters = random_int(0, 3) === 0; // roughly 25%
                $frequency = $frequencies[array_rand($frequencies)];

                $base = $windows * $windowPrice
                    + ($frames ? $extraFrames : 0)
                    + ($sills ? $extraSills : 0)
                    + ($gutters ? $extraGutters : 0);
                $storeyAdj = $base * ($modStoreys[$storeys] ?? 0);
                $freqAdj = $base * ($modFrequency[$frequency] ?? 0);
                $exVat = ($base + $storeyAdj + $freqAdj) * $firstVisitFactor;
                $exVat = max($exVat, $minCallout);
                $exVat = round($exVat / $rounding) * $rounding;
                $total = $exVat * (1 + $vatRate);
                $totalPence = (int) round($total * 100);
                $estimateMinutes = (int) round($windows * $timePerWindow + ($gutters ? $timeGutters : 0));
                $estimateMinutes = max($estimateMinutes, 45);

                $start = $cursor->copy();
                $end = $cursor->copy()->addMinutes($estimateMinutes);
                $etaWindow = $start->format('H:i') . '-' . $end->format('H:i');

                DB::table('jobs')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'staff_user_id' => $cleanerIds[($sequence - 1) % $cleanerIds->count()],
                    'date' => $date,
                    'eta_window' => $etaWindow,
                    'sequence' => $sequence,
                    'status' => 'scheduled',
                    'checklist_json' => json_encode([
                        'customer_id' => $customerId,
                        'address_id' => $addressId,
                        'address_line1' => $addressLine,
                        'postcode' => strtoupper($postcode),
                        'storeys' => $storeys,
                        'windows' => $windows,
                        'frames' => $frames,
                        'sills' => $sills,
                        'gutters' => $gutters,
                        'frequency' => $frequency,
                        'price_pence' => $totalPence,
                        'deposit_pence' => 0,
                        'estimate_minutes' => $estimateMinutes,
                        'lat' => $coords['lat'],
                        'lng' => $coords['lng'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $cursor = $end->addMinutes(random_int(10, 20));
            }
        }
    }

    protected function randomPointWithinRadius(float $baseLat, float $baseLng, float $radiusMeters): array
    {
        $distance = sqrt($this->randomFloat()) * $radiusMeters;
        $bearing = 2 * M_PI * $this->randomFloat();
        $earthRadius = 6378137; // meters

        $deltaLat = ($distance * cos($bearing)) / $earthRadius;
        $deltaLngDenominator = $earthRadius * cos(deg2rad($baseLat));
        $deltaLng = $deltaLngDenominator !== 0.0
            ? ($distance * sin($bearing)) / $deltaLngDenominator
            : 0.0;

        $lat = $baseLat + rad2deg($deltaLat);
        $lng = $baseLng + rad2deg($deltaLng);

        return [
            'lat' => round($lat, 6),
            'lng' => round($lng, 6),
        ];
    }

    protected function randomFloat(): float
    {
        return function_exists('lcg_value') ? lcg_value() : mt_rand() / mt_getrandmax();
    }
}
