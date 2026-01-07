<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganisationController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function show(Request $request): Response
    {
        $organisation = $request->user()->organisation;

        return Inertia::render('App/Organisation/Profile', [
            'organisation' => $organisation ? [
                'name' => $organisation->trading_name ?? $organisation->name,
                'support_email' => $organisation->support_email,
                'support_phone' => $organisation->support_phone,
                'website_url' => $organisation->website_url,
                'brand' => $organisation->brand_json,
            ] : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organisation = $request->user()->organisation;
        $data = $request->validate([
            'support_email' => ['nullable', 'email'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'website_url' => ['nullable', 'url'],
            'brand.primary_color' => ['nullable', 'string'],
            'brand.secondary_color' => ['nullable', 'string'],
            'brand.button_radius' => ['nullable', 'string'],
        ]);

        $organisation->update([
            'support_email' => $data['support_email'] ?? $organisation->support_email,
            'support_phone' => $data['support_phone'] ?? $organisation->support_phone,
            'website_url' => $data['website_url'] ?? $organisation->website_url,
            'brand_json' => array_merge($organisation->brand_json ?? [], $data['brand'] ?? []),
        ]);

        $this->audit->log('org.updated', [
            'tenant_id' => $organisation->getKey(),
            'entity_type' => 'organisation',
            'entity_id' => $organisation->getKey(),
        ]);

        return back()->with('status', 'Organisation updated.');
    }
}
