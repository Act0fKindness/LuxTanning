<?php

namespace App\Support;

use App\Models\Job;
use App\Models\Address;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PlatformJobsFetcher
{
    public static function fetch(Carbon $windowStart, Carbon $windowEnd, ?int $limit = 200): Collection
    {
        $query = Job::with('tenant')
            ->whereBetween('date', [$windowStart, $windowEnd])
            ->where(function ($q) use ($windowStart) {
                $q->whereNull('status')
                    ->orWhereNotIn('status', ['completed', 'cancelled'])
                    ->orWhere(function ($sub) use ($windowStart) {
                        $sub->where('status', 'completed')
                            ->where('date', '>=', $windowStart);
                    });
            })
            ->orderBy('date')
            ->orderBy('eta_window');

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        $jobs = $query->get();

        if ($jobs->isEmpty()) {
            return collect();
        }

        $addressIds = $jobs
            ->map(function (Job $job) {
                $payload = $job->checklist_json ?? [];
                return $payload['address_id'] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $addresses = $addressIds->isNotEmpty()
            ? Address::whereIn('id', $addressIds)->get(['id', 'line1', 'city', 'postcode', 'lat', 'lng'])->keyBy('id')
            : collect();

        return $jobs->map(function (Job $job) use ($addresses) {
            $payload = $job->checklist_json ?? [];
            $addressId = $payload['address_id'] ?? null;
            $address = $addressId ? $addresses->get($addressId) : null;

            [$start, $end] = self::parseEtaWindow($job->date, $job->eta_window);

            $startMinutes = $start ? ($start->hour * 60 + $start->minute) : null;
            $endMinutes = $end ? ($end->hour * 60 + $end->minute) : null;

            return [
                'id' => $job->id,
                'tenant_id' => $job->tenant_id,
                'tenant_name' => optional($job->tenant)->name,
                'date' => optional($job->date)->format('Y-m-d'),
                'eta_window' => $job->eta_window,
                'status' => $job->status,
                'start_at' => $start ? $start->toIso8601String() : null,
                'end_at' => $end ? $end->toIso8601String() : null,
                'start_minutes' => $startMinutes,
                'end_minutes' => $endMinutes,
                'price_pence' => isset($payload['price_pence']) ? (int) $payload['price_pence'] : null,
                'address' => [
                    'line1' => $payload['address_line1'] ?? optional($address)->line1,
                    'postcode' => $payload['postcode'] ?? optional($address)->postcode,
                    'city' => $payload['city'] ?? optional($address)->city,
                    'lat' => isset($payload['lat']) ? $payload['lat'] : optional($address)->lat,
                    'lng' => isset($payload['lng']) ? $payload['lng'] : optional($address)->lng,
                ],
            ];
        })->filter(function (array $job) {
            return !empty($job['tenant_name']) || !empty($job['address']['line1']);
        })->values();
    }

    private static function parseEtaWindow($date, ?string $etaWindow): array
    {
        if (!$date || !$etaWindow || !str_contains($etaWindow, '-')) {
            return [null, null];
        }

        [$rawStart, $rawEnd] = explode('-', $etaWindow, 2);

        try {
            $start = Carbon::parse(optional($date)->format('Y-m-d') . ' ' . trim($rawStart));
        } catch (\Exception $e) {
            $start = null;
        }

        try {
            $end = Carbon::parse(optional($date)->format('Y-m-d') . ' ' . trim($rawEnd));
        } catch (\Exception $e) {
            $end = null;
        }

        return [$start, $end];
    }
}
