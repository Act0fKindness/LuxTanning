<?php

namespace App\Http\Controllers\Api;

use App\Events\SessionPinged;
use App\Models\Job;
use App\Models\LocationPing;
use App\Models\TrackingSession;
use Illuminate\Http\Request;

class TrackingPingsController extends BaseApiController
{
    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $data = $request->validate([
            'session_id' => ['required', 'uuid'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'ts' => ['required', 'date'],
        ]);

        $session = TrackingSession::whereKey($data['session_id'])->firstOrFail();
        if ($session->cleaner_id && $user->role === 'cleaner') {
            abort_if($session->cleaner_id !== $user->id, 403);
        }

        abort_if($session->ended_at, 409, 'Session already ended');

        $ping = LocationPing::create([
            'tracking_session_id' => $session->id,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'accuracy' => $data['accuracy'],
            'ts' => $data['ts'],
        ]);

        Job::whereKey($session->job_id)->update([
            'last_lat' => $ping->lat,
            'last_lng' => $ping->lng,
            'last_location_at' => now(),
        ]);

        broadcast(new SessionPinged($session->job_id, [
            'session_id' => $session->id,
            'lat' => (float) $ping->lat,
            'lng' => (float) $ping->lng,
            'accuracy' => $ping->accuracy ? (float) $ping->accuracy : null,
            'ts' => $ping->ts?->toIso8601String(),
            'phase' => $session->phase,
        ]))->toOthers();

        return response()->json(['ok' => true]);
    }
}
