<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganisationController extends Controller
{
    public function index(Request $request): Response
    {
        $organisations = Organisation::query()
            ->latest()
            ->limit(100)
            ->get(['id', 'trading_name', 'plan_key', 'plan_status', 'status', 'created_at']);

        return Inertia::render('Platform/Organisations/Index', [
            'organisations' => $organisations,
        ]);
    }
}
