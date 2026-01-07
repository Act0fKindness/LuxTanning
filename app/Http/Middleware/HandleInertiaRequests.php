<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Support\PlatformJobsFetcher;
use App\Support\TenantBrandingResolver;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $tenantContext = app(TenantBrandingResolver::class)->resolve($request);

        return array_merge(parent::share($request), [
            'csrfToken' => csrf_token(),
            'auth' => [
                'user' => fn () => $request->user() ? (function () use ($request) {
                    $u = $request->user();
                    $role = $u->role;
                    $company = null;
                    // Derive tenant + role from membership if available (but never override platform_admin)
                    $membership = null;
                    if ($role !== 'platform_admin' && Schema::hasTable('tenant_user')) {
                        $membership = DB::table('tenant_user')->where('user_id',$u->id)->first();
                        if ($membership) {
                            $role = $membership->role ?? $role;
                            $company = DB::table('tenants')->where('id', $membership->tenant_id)->value('name');
                        }
                    }
                    // Special-case dev owner mapping
                    if ($u->email === 'daniel.robert.harding+wow@gmail.com' && $role !== 'owner') {
                        $role = 'owner';
                        if (!$company) { $company = 'AOK World'; }
                    }
                    if ($role === 'platform_admin' && !$company) {
                        $company = 'Lux Tanning';
                    }

                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'role' => $role,
                        'company' => $company,
                        'must_change_password' => (bool) ($u->must_change_password ?? false),
                    ];
                })() : null
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'tenant' => fn () => $tenantContext,
            'branding' => fn () => $tenantContext['branding'] ?? [],
            'platform' => [
                'active_jobs' => fn () => $this->sharePlatformJobs($request),
            ],
            'mapbox' => [
                'token' => config('services.mapbox.public_token'),
            ],
            'google' => [
                'maps_key' => config('services.google.maps_key'),
            ],
        ]);
    }

    private function sharePlatformJobs(Request $request): array
    {
        $user = $request->user();
        if (!$user || $user->role !== 'platform_admin') {
            return [];
        }

        $today = Carbon::today();
        $windowStart = (clone $today)->subDays(3);
        $windowEnd = (clone $today)->addDays(7);

        return PlatformJobsFetcher::fetch($windowStart, $windowEnd, 200)->toArray();
    }
}
