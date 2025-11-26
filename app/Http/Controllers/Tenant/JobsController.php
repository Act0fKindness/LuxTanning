<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Job;
use App\Models\User;
use App\Models\TenantUser;
use App\Models\UserProfile;
use App\Models\Address;
use App\Support\JobPresenter;
use App\Support\QuoteCalculator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JobsController extends Controller
{
    private function tenantIdOrFallback(Request $request): ?string
    {
        $tenantId = optional($request->user())->tenant_id;
        if ($tenantId) return $tenantId;
        // Try pivot membership
        if (Schema::hasTable('tenant_user')) {
            $m = DB::table('tenant_user')->where('user_id', optional($request->user())->id)->value('tenant_id');
            if ($m) return $m;
        }
        $u = $request->user();
        if ($u && $u->role === 'owner') {
            // Fallback to AOK World if owner has not been linked yet
            $id = DB::table('tenants')->where('slug','aok-world')->value('id');
            if ($id) return $id;
        }
        return null;
    }
    public function index(Request $request)
    {
        $tenantId = $this->tenantIdOrFallback($request);
        if (!$tenantId) return redirect()->back()->with('error', 'No tenant context');

        $today = Carbon::today();
        $items = Job::with('staff:id,name')
            ->where('tenant_id', $tenantId)
            ->whereDate('date', '>=', $today)
            ->orderBy('date')
            ->orderBy('eta_window')
            ->get()
            ->map(function (Job $job) use ($request) {
                return JobPresenter::make($job, [
                    'is_mine' => $job->staff_user_id === optional($request->user())->id,
                ]);
            })
            ->values();

        if (Schema::hasTable('tenant_user')) {
            $cleanerIds = DB::table('tenant_user')->where('tenant_id',$tenantId)->where('role','cleaner')->pluck('user_id');
            $cleaners = User::whereIn('id', $cleanerIds)->orderBy('name')->get(['id','name','email']);
        } else {
            $cleaners = User::where('tenant_id', $tenantId)->where('role','cleaner')->orderBy('name')->get(['id','name','email']);
        }

        $view = $request->is('owner*') ? 'Owner/Jobs' : 'Tenant/Jobs';
        return Inertia::render($view, [ 'items' => $items, 'cleaners' => $cleaners ]);
    }

    public function store(Request $request)
    {
        $tenantId = $this->tenantIdOrFallback($request);
        if (!$tenantId) return redirect()->back()->with('error', 'No tenant context');

        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postcode' => 'required|string|max:20',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'storeys' => 'required|integer|min:1|max:3',
            'windows' => 'required|integer|min:0|max:300',
            'frames' => 'nullable|boolean',
            'sills' => 'nullable|boolean',
            'gutters' => 'nullable|boolean',
            'frequency' => 'required|in:one_off,four_week,six_week,eight_week',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'staff_user_id' => 'required|uuid',
            'override_price_pence' => 'nullable|integer|min:0',
            'override_deposit_pence' => 'nullable|integer|min:0',
            'override_estimate_minutes' => 'nullable|integer|min:1|max:1440',
            'override_reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Create or find customer user
        $customer = null;
        if (!empty($data['customer_email'])) {
            $customer = User::firstOrNew(['email' => $data['customer_email']]);
        } else {
            $customer = new User();
        }
        $customer->tenant_id = $tenantId;
        $customer->name = $data['customer_name'];
        if (!empty($data['customer_email'])) $customer->email = $data['customer_email'];
        if (!empty($data['customer_phone'])) $customer->phone = $data['customer_phone'];
        $customer->role = 'customer';
        if (!$customer->password) { $customer->password = bcrypt(Str::random(32)); }
        $customer->save();
        TenantUser::firstOrCreate([
            'tenant_id' => $tenantId,
            'user_id' => $customer->id,
        ], [ 'role' => 'customer', 'status' => 'active' ]);

        // Create address
        $address = Address::create([
            'tenant_id' => $tenantId,
            'user_id' => $customer->id,
            'line1' => $data['address_line1'],
            'postcode' => strtoupper(trim($data['postcode'])),
        ]);
        // Ensure the default address is set on profile
        $customer->profile()->updateOrCreate([], ['default_address_id' => $address->id]);

        // Quote calculation (mirror of demo)
        $calc = QuoteCalculator::windowCleaning(
            (int)$data['windows'],
            (int)$data['storeys'],
            (string)$data['frequency'],
            (bool)($data['frames'] ?? false),
            (bool)($data['sills'] ?? false),
            (bool)($data['gutters'] ?? false),
        );

        $pricePence = $calc['total_pence'];
        $depositPence = $calc['deposit_pence'];
        $estimateMinutes = $calc['estimate_minutes'];

        if (!empty($data['override_price_pence'])) {
            $pricePence = (int) $data['override_price_pence'];
        }

        if (!empty($data['override_deposit_pence'])) {
            $depositPence = (int) $data['override_deposit_pence'];
        }

        if (!empty($data['override_estimate_minutes'])) {
            $estimateMinutes = (int) $data['override_estimate_minutes'];
        }

        // Schedule window
        $date = Carbon::parse($data['date']);
        $start = $data['start_time'] ? Carbon::parse($data['date'].' '.$data['start_time']) : Carbon::parse($data['date'].' 14:00');
        $end = (clone $start)->addMinutes($estimateMinutes);
        $etaWindow = $start->format('H:i').'-'.$end->format('H:i');

        // Validate cleaner belongs to tenant and is role cleaner
        $staff = User::where('id', $data['staff_user_id'])->where('tenant_id', $tenantId)->where('role','cleaner')->first();
        if (!$staff) return redirect()->back()->with('error', 'Invalid cleaner selection');

        Job::create([
            'tenant_id' => $tenantId,
            'staff_user_id' => $staff->id,
            'date' => $date,
            'eta_window' => $etaWindow,
            'status' => 'scheduled',
            'checklist_json' => [
                'customer_id' => $customer->id,
                'address_id' => $address->id,
                'address_line1' => $address->line1,
                'postcode' => $address->postcode,
                'address_line2' => $address->line2,
                'city' => $address->city,
                'storeys' => (int)$data['storeys'],
                'windows' => (int)$data['windows'],
                'frames' => (bool)($data['frames'] ?? false),
                'sills' => (bool)($data['sills'] ?? false),
                'gutters' => (bool)($data['gutters'] ?? false),
                'frequency' => (string)$data['frequency'],
                'price_pence' => $pricePence,
                'deposit_pence' => $depositPence,
                'estimate_minutes' => $estimateMinutes,
                'override_reason' => $data['override_reason'] ?? null,
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'notes' => $data['notes'] ?? null,
            ],
        ]);

        if ($request->is('owner*')) {
            return redirect('/owner/jobs')->with('success', 'Job created');
        }

        return redirect()->back()->with('success', 'Job created');
    }

    public function edit(Request $request, string $id)
    {
        $tenantId = $this->tenantIdOrFallback($request);
        if (!$tenantId) return redirect()->back()->with('error', 'No tenant context');
        $job = Job::where('id',$id)->where('tenant_id',$tenantId)->firstOrFail();
        $d = $job->checklist_json ?? [];
        $start = explode('-', (string)$job->eta_window)[0] ?? '14:00';
        $payload = [
            'id' => $job->id,
            'date' => optional($job->date)->format('Y-m-d'),
            'start_time' => $start,
            'staff_user_id' => $job->staff_user_id,
            'address_line1' => $d['address_line1'] ?? null,
            'postcode' => $d['postcode'] ?? null,
            'storeys' => $d['storeys'] ?? 2,
            'windows' => $d['windows'] ?? 0,
            'frames' => (bool)($d['frames'] ?? false),
            'sills' => (bool)($d['sills'] ?? false),
            'gutters' => (bool)($d['gutters'] ?? false),
            'frequency' => $d['frequency'] ?? 'six_week',
            'price_pence' => $d['price_pence'] ?? null,
            'estimate_minutes' => $d['estimate_minutes'] ?? null,
        ];
        if (Schema::hasTable('tenant_user')) {
            $cleanerIds = DB::table('tenant_user')->where('tenant_id',$tenantId)->where('role','cleaner')->pluck('user_id');
            $cleaners = User::whereIn('id', $cleanerIds)->orderBy('name')->get(['id','name','email']);
        } else {
            $cleaners = User::where('tenant_id', $tenantId)->where('role','cleaner')->orderBy('name')->get(['id','name','email']);
        }
        $view = $request->is('owner*') ? 'Owner/JobEdit' : 'Tenant/JobEdit';
        return Inertia::render($view, [ 'job' => $payload, 'cleaners' => $cleaners ]);
    }

    public function update(Request $request, string $id)
    {
        $tenantId = $this->tenantIdOrFallback($request);
        if (!$tenantId) return redirect()->back()->with('error', 'No tenant context');
        $data = $request->validate([
            'address_line1' => 'required|string|max:255',
            'postcode' => 'required|string|max:20',
            'storeys' => 'required|integer|min:1|max:3',
            'windows' => 'required|integer|min:0|max:300',
            'frames' => 'nullable|boolean',
            'sills' => 'nullable|boolean',
            'gutters' => 'nullable|boolean',
            'frequency' => 'required|in:one_off,four_week,six_week,eight_week',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'staff_user_id' => 'required|uuid',
        ]);
        $job = Job::where('id',$id)->where('tenant_id',$tenantId)->firstOrFail();
        $calc = QuoteCalculator::windowCleaning((int)$data['windows'], (int)$data['storeys'], (string)$data['frequency'], (bool)($data['frames'] ?? false), (bool)($data['sills'] ?? false), (bool)($data['gutters'] ?? false));
        $start = Carbon::parse($data['date'].' '.$data['start_time']);
        $end = (clone $start)->addMinutes($calc['estimate_minutes']);
        $etaWindow = $start->format('H:i').'-'.$end->format('H:i');
        if (Schema::hasTable('tenant_user')) {
            $isCleaner = DB::table('tenant_user')->where('tenant_id',$tenantId)->where('user_id',$data['staff_user_id'])->where('role','cleaner')->exists();
            $staff = $isCleaner ? User::find($data['staff_user_id']) : null;
        } else {
            $staff = User::where('id', $data['staff_user_id'])->where('tenant_id',$tenantId)->where('role','cleaner')->first();
        }
        if (!$staff) return redirect()->back()->with('error','Invalid cleaner');
        $d = $job->checklist_json ?? [];
        $d = array_merge($d, [
            'address_line1' => $data['address_line1'],
            'postcode' => strtoupper(trim($data['postcode'])),
            'storeys' => (int)$data['storeys'],
            'windows' => (int)$data['windows'],
            'frames' => (bool)($data['frames'] ?? false),
            'sills' => (bool)($data['sills'] ?? false),
            'gutters' => (bool)($data['gutters'] ?? false),
            'frequency' => (string)$data['frequency'],
            'price_pence' => $calc['total_pence'],
            'deposit_pence' => $calc['deposit_pence'],
            'estimate_minutes' => $calc['estimate_minutes'],
        ]);
        $job->update([
            'staff_user_id' => $staff->id,
            'date' => Carbon::parse($data['date']),
            'eta_window' => $etaWindow,
            'checklist_json' => $d,
        ]);
        return redirect('/tenant/jobs')->with('success','Job updated');
    }

    public function destroy(Request $request, string $id)
    {
        $tenantId = $this->tenantIdOrFallback($request);
        if (!$tenantId) return response()->json(['error' => 'No tenant context'], 422);
        $job = Job::where('id',$id)->where('tenant_id',$tenantId)->firstOrFail();
        $job->delete();
        return response()->json(['ok'=>true]);
    }

    public function assign(Request $request, string $id)
    {
        $tenantId = optional($request->user())->tenant_id;
        if (!$tenantId) return response()->json(['error' => 'No tenant context'], 422);
        $data = $request->validate(['staff_user_id' => 'required|uuid']);
        $job = Job::where('id', $id)->where('tenant_id', $tenantId)->firstOrFail();
        if (Schema::hasTable('tenant_user')) {
            $isCleaner = DB::table('tenant_user')->where('tenant_id',$tenantId)->where('user_id',$data['staff_user_id'])->where('role','cleaner')->exists();
            if (!$isCleaner) return response()->json(['error' => 'Invalid cleaner'], 422);
            $job->staff_user_id = $data['staff_user_id'];
        } else {
            $staff = User::where('id', $data['staff_user_id'])->where('tenant_id',$tenantId)->where('role','cleaner')->first();
            if (!$staff) return response()->json(['error' => 'Invalid cleaner'], 422);
            $job->staff_user_id = $staff->id;
        }
        $job->save();
        return response()->json(['ok' => true]);
    }

}
