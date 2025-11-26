<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Models\Job;
use Illuminate\Http\Request;

class EtaController extends BaseApiController
{
    public function show(Request $request, Job $job)
    {
        $user = $request->user();
        abort_unless($user, 401);
        if ($user->role === 'cleaner') {
            abort_if($job->staff_user_id !== $user->id, 403);
        }

        $data = $request->validate([
            'fromLat' => ['required', 'numeric', 'between:-90,90'],
            'fromLng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $destination = $this->resolveDestination($job);
        abort_if(!$destination, 422, 'Job destination missing');

        $distance = $this->haversine($data['fromLat'], $data['fromLng'], $destination['lat'], $destination['lng']);
        $etaMinutes = (int) ceil(($distance / 1000) / 35 * 60); // assume 35km/h avg

        return response()->json([
            'eta_minutes' => max(1, $etaMinutes),
            'distance_m' => (int) round($distance),
            'as_of' => now()->toIso8601String(),
        ]);
    }

    private function resolveDestination(Job $job): ?array
    {
        $checklist = $job->checklist_json ?? [];
        $lat = $checklist['lat'] ?? $checklist['latitude'] ?? null;
        $lng = $checklist['lng'] ?? $checklist['longitude'] ?? null;

        if ($lat !== null && $lng !== null) {
            return ['lat' => (float) $lat, 'lng' => (float) $lng];
        }

        $addressId = $checklist['address_id'] ?? null;
        if ($addressId) {
            $address = Address::find($addressId);
            if ($address && $address->lat !== null && $address->lng !== null) {
                return ['lat' => (float) $address->lat, 'lng' => (float) $address->lng];
            }
        }

        return null;
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth * $c;
    }
}
