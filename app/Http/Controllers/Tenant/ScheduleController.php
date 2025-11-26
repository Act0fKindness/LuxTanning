<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Job;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = optional($request->user())->tenant_id;
        if (!$tenantId) {
            $tenantId = \Illuminate\Support\Facades\DB::table('tenant_user')->where('user_id', optional($request->user())->id)->value('tenant_id');
        }
        $jobs = [];
        if ($tenantId) {
            $jobs = Job::where('tenant_id', $tenantId)
                ->orderBy('date')
                ->get(['id','date','status'])
                ->map(fn($j) => [
                    'id' => $j->id,
                    'date' => $j->date?->toDateString(),
                    'status' => $j->status,
                ]);
        }
        $view = $request->is('owner*') ? 'Owner/Schedule' : 'Tenant/Schedule';
        return Inertia::render($view, [
            'jobs' => $jobs,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);
        $tenantId = optional($request->user())->tenant_id;
        if (!$tenantId) {
            $tenantId = \Illuminate\Support\Facades\DB::table('tenant_user')->where('user_id', optional($request->user())->id)->value('tenant_id');
        }
        if (!$tenantId) return redirect()->back()->with('error', 'No tenant context');

        Job::create([
            'tenant_id' => $tenantId,
            'date' => Carbon::parse($request->date)->toDateString(),
            'status' => 'scheduled',
        ]);
        return redirect()->back()->with('success', 'Job scheduled');
    }
}
