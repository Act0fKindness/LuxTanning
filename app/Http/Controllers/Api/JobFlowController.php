<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use App\Models\TrackingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JobFlowController extends BaseApiController
{
    public function startJob(Request $request, Job $job)
    {
        $user = $request->user();
        $this->ensureCleanerOwnsJob($user, $job);

        $request->validate([
            'idempotency_key' => ['required', 'string'],
        ]);

        $now = now();
        if (!$job->started_at) {
            $job->started_at = $now;
        }
        $job->status = 'started';
        $job->save();

        TrackingSession::where('job_id', $job->id)
            ->where('phase', 'enroute')
            ->whereNull('ended_at')
            ->update(['ended_at' => $now]);

        TrackingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $job->tenant_id,
            'job_id' => $job->id,
            'cleaner_id' => $user->id,
            'phase' => 'onsite',
            'started_at' => $now,
            'meta' => ['source' => 'job.start'],
        ]);

        return response()->json(['status' => $job->status]);
    }

    public function finishJob(Request $request, Job $job)
    {
        $user = $request->user();
        $this->ensureCleanerOwnsJob($user, $job);

        $request->validate([
            'idempotency_key' => ['required', 'string'],
        ]);

        $now = now();
        if (!$job->started_at) {
            $job->started_at = $now;
        }
        $job->status = 'completed';
        $job->completed_at = $now;
        $job->actual_minutes = $job->started_at ? $job->started_at->diffInMinutes($now) : null;
        $job->save();

        $this->closeSessions($job, 'onsite');

        return response()->json(['status' => $job->status]);
    }

    public function cancelJob(Request $request, Job $job)
    {
        $user = $request->user();
        $this->ensureCleanerOwnsJob($user, $job);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $job->status = 'cancelled';
        $job->cancelled_at = now();
        $job->save();

        $this->closeSessions($job, null);

        return response()->json(['status' => $job->status]);
    }

    protected function ensureCleanerOwnsJob($user, Job $job): void
    {
        abort_unless($user, 401);
        abort_unless($user->role === 'cleaner', 403);
        abort_if($job->staff_user_id !== $user->id, 403);
    }

    protected function closeSessions(Job $job, ?string $phase): void
    {
        $query = TrackingSession::where('job_id', $job->id)->whereNull('ended_at');
        if ($phase) {
            $query->where('phase', $phase);
        }
        $query->update(['ended_at' => now()]);
    }
}
