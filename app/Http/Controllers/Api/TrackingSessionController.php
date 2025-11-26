<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use App\Models\TrackingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TrackingSessionController extends BaseApiController
{
    public function start(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->role === 'cleaner', 403);

        $data = $request->validate([
            'job_id' => ['required', 'uuid'],
            'phase' => ['required', Rule::in(['enroute', 'onsite'])],
        ]);

        $job = Job::whereKey($data['job_id'])
            ->where('staff_user_id', $user->id)
            ->firstOrFail();

        TrackingSession::where('job_id', $job->id)
            ->where('cleaner_id', $user->id)
            ->where('phase', $data['phase'])
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        $session = TrackingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $job->tenant_id,
            'job_id' => $job->id,
            'cleaner_id' => $user->id,
            'phase' => $data['phase'],
            'started_at' => now(),
            'meta' => $request->input('meta', []),
        ]);

        return response()->json([
            'session_id' => $session->id,
            'phase' => $session->phase,
            'started_at' => $session->started_at,
        ], 201);
    }

    public function close(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $data = $request->validate([
            'session_id' => ['required', 'uuid'],
            'reason' => ['nullable', 'string'],
        ]);

        $session = TrackingSession::whereKey($data['session_id'])->first();
        if (!$session) {
            return response()->noContent();
        }

        if ($user->role !== 'cleaner' && $user->role !== 'manager') {
            abort(403);
        }

        if ($session->ended_at === null) {
            $session->ended_at = now();
            $session->meta = array_merge($session->meta ?? [], [
                'closed_reason' => $data['reason'] ?? null,
            ]);
            $session->save();
        }

        return response()->noContent();
    }
}
