<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Tenancy\TenancyResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly TenancyResolver $tenancy)
    {
    }

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $organisation = $this->tenancy->organisationFor($user);
        $shops = $this->tenancy->shopsFor($user)->map(fn ($shop) => [
            'id' => $shop->id,
            'name' => $shop->name,
            'timezone' => $shop->timezone,
        ])->values();

        return Inertia::render('App/Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
            'organisation' => $organisation ? [
                'id' => $organisation->id,
                'name' => $organisation->trading_name ?? $organisation->name,
                'timezone' => $organisation->default_timezone,
                'plan' => $organisation->plan_key,
            ] : null,
            'shops' => $shops,
        ]);
    }
}
