<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function index(Request $request): Response
    {
        $organisation = $request->user()->organisation;
        $shops = $organisation?->shops()->orderBy('name')->get();

        return Inertia::render('App/Shops/Index', [
            'shops' => $shops?->map(fn ($shop) => [
                'id' => $shop->id,
                'name' => $shop->name,
                'phone' => $shop->phone,
                'email' => $shop->email,
                'city' => $shop->city,
                'postcode' => $shop->postcode,
            ]) ?? [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organisation = $request->user()->organisation;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'address_line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20'],
        ]);

        $shop = $organisation->shops()->create($data);
        $this->audit->log('shop.created', [
            'tenant_id' => $organisation->getKey(),
            'entity_type' => 'shop',
            'entity_id' => $shop->getKey(),
        ]);

        return back()->with('status', 'Shop added.');
    }
}
