<?php

namespace App\Support;

use App\Models\Job;
use Carbon\CarbonInterface;

class JobPresenter
{
    public static function make(Job $job, array $context = []): array
    {
        $checklist = $job->checklist_json ?? [];
        $etaWindow = (string) ($job->eta_window ?? '');
        [$startTime, $endTime] = self::parseWindow($etaWindow);
        $status = (string) ($job->status ?? 'scheduled');

        $startedAt = $job->started_at instanceof CarbonInterface ? $job->started_at : null;
        $completedAt = $job->completed_at instanceof CarbonInterface ? $job->completed_at : null;
        $cancelledAt = $job->cancelled_at instanceof CarbonInterface ? $job->cancelled_at : null;

        $actualMinutes = $job->actual_minutes;
        if ($actualMinutes === null && $startedAt && $completedAt) {
            $actualMinutes = $startedAt->diffInMinutes($completedAt);
        }

        $addressContext = self::normalizeAddressContext($context['address'] ?? null);
        $location = self::resolveLocation($checklist, $addressContext, $context);

        return [
            'id' => $job->id,
            'tenant_id' => $job->tenant_id,
            'date' => optional($job->date)->format('Y-m-d'),
            'eta_window' => $etaWindow,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'sequence' => $job->sequence,
            'status' => $status,
            'status_label' => self::statusLabel($status),
            'status_badge' => self::statusBadge($status),
            'status_color' => self::statusColor($status),
            'estimate_minutes' => $checklist['estimate_minutes'] ?? null,
            'frequency' => $checklist['frequency'] ?? null,
            'address_line1' => $checklist['address_line1'] ?? null,
            'postcode' => $checklist['postcode'] ?? null,
            'address_id' => $checklist['address_id'] ?? null,
            'storeys' => $checklist['storeys'] ?? null,
            'windows' => $checklist['windows'] ?? null,
            'price_pence' => $checklist['price_pence'] ?? null,
            'extras' => [
                'frames' => (bool) ($checklist['frames'] ?? false),
                'sills' => (bool) ($checklist['sills'] ?? false),
                'gutters' => (bool) ($checklist['gutters'] ?? false),
            ],
            'customer_id' => $checklist['customer_id'] ?? null,
            'staff_user_id' => $job->staff_user_id,
            'staff' => $job->relationLoaded('staff') && $job->staff ? [
                'id' => $job->staff->id,
                'name' => $job->staff->name,
            ] : null,
            'started_at' => self::formatDate($startedAt),
            'completed_at' => self::formatDate($completedAt),
            'cancelled_at' => self::formatDate($cancelledAt),
            'actual_minutes' => $actualMinutes,
            'last_location' => self::presentLocation($job),
            'is_mine' => (bool) ($context['is_mine'] ?? $context['isMine'] ?? false),
            'location' => $location,
            'address' => $addressContext,
            'metadata' => $context,
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'scheduled' => 'Scheduled',
            'en_route' => 'En Route',
            'arrived' => 'Arrived',
            'started' => 'Cleaning In Progress',
            'completed' => 'Finished',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public static function statusBadge(string $status): string
    {
        return match ($status) {
            'scheduled' => 'secondary',
            'en_route', 'arrived', 'started' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'scheduled' => '#6B7280',
            'en_route' => '#2563EB',
            'arrived' => '#0891B2',
            'started' => '#0EA5E9',
            'completed' => '#22C55E',
            'cancelled' => '#EF4444',
            default => '#6B7280',
        };
    }

    private static function formatDate(?CarbonInterface $value): ?string
    {
        return $value?->toIso8601String();
    }

    private static function parseWindow(?string $window): array
    {
        if (!$window) {
            return [null, null];
        }
        $parts = explode('-', $window);
        $start = trim($parts[0] ?? '') ?: null;
        $end = trim($parts[1] ?? '') ?: null;
        return [$start, $end];
    }

    private static function presentLocation(Job $job): ?array
    {
        if ($job->last_lat === null || $job->last_lng === null) {
            return null;
        }

        return [
            'lat' => (float) $job->last_lat,
            'lng' => (float) $job->last_lng,
            'recorded_at' => self::formatDate($job->last_location_at instanceof CarbonInterface ? $job->last_location_at : null),
        ];
    }

    private static function normalizeAddressContext($address): ?array
    {
        if (!$address) {
            return null;
        }

        if ($address instanceof \JsonSerializable) {
            $address = $address->jsonSerialize();
        }

        if (is_object($address)) {
            $address = [
                'id' => $address->id ?? null,
                'line1' => $address->line1 ?? null,
                'line2' => $address->line2 ?? null,
                'city' => $address->city ?? null,
                'county' => $address->county ?? null,
                'postcode' => $address->postcode ?? null,
                'lat' => isset($address->lat) ? (float) $address->lat : null,
                'lng' => isset($address->lng) ? (float) $address->lng : null,
            ];
        }

        if (!is_array($address)) {
            return null;
        }

        return [
            'id' => $address['id'] ?? null,
            'line1' => $address['line1'] ?? null,
            'line2' => $address['line2'] ?? null,
            'city' => $address['city'] ?? null,
            'county' => $address['county'] ?? null,
            'postcode' => $address['postcode'] ?? null,
            'lat' => self::castCoordinate($address['lat'] ?? null),
            'lng' => self::castCoordinate($address['lng'] ?? null),
        ];
    }

    private static function resolveLocation(array $checklist, ?array $address, array $context): ?array
    {
        $lat = self::castCoordinate($checklist['lat'] ?? $checklist['latitude'] ?? null);
        $lng = self::castCoordinate($checklist['lng'] ?? $checklist['longitude'] ?? null);

        if ($lat === null && $address) {
            $lat = self::castCoordinate($address['lat'] ?? null);
        }
        if ($lng === null && $address) {
            $lng = self::castCoordinate($address['lng'] ?? null);
        }

        if ($lat === null) {
            $lat = self::castCoordinate($context['lat'] ?? $context['location_lat'] ?? null);
        }
        if ($lng === null) {
            $lng = self::castCoordinate($context['lng'] ?? $context['location_lng'] ?? null);
        }

        if ($lat === null || $lng === null) {
            return null;
        }

        return [
            'lat' => $lat,
            'lng' => $lng,
        ];
    }

    private static function castCoordinate($value): ?float
    {
        if ($value === null) {
            return null;
        }

        if ($value === '' || (is_string($value) && trim($value) === '')) {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        $float = (float) $value;

        if ($float === 0.0 && !in_array((string) $value, ['0', '0.0', '0.00'], true)) {
            return null;
        }

        return $float;
    }
}
