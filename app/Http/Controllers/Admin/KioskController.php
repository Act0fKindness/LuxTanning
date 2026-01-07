<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Shop;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class KioskController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function index(Request $request): Response
    {
        $organisation = $request->user()->organisation;
        $devices = Device::query()
            ->where('tenant_id', $organisation?->id)
            ->orderByDesc('created_at')
            ->with('shop')
            ->limit(50)
            ->get();

        return Inertia::render('App/Kiosks/Index', [
            'shops' => $organisation?->shops()->get(['id', 'name']),
            'devices' => $devices->map(fn ($device) => [
                'id' => $device->id,
                'name' => $device->name,
                'status' => $device->status,
                'shop' => $device->shop?->name,
                'pairing_code' => $device->pairing_code,
                'pairing_code_expires_at' => optional($device->pairing_code_expires_at)->toIso8601String(),
            ])->values(),
        ]);
    }

    public function generatePairingCode(Request $request, Shop $shop): JsonResponse
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $code = (string) random_int(100000, 999999);
        $device = $shop->devices()->create([
            'tenant_id' => $shop->tenant_id,
            'name' => $request->input('name', $shop->name.' Kiosk'),
            'identifier' => (string) Str::uuid(),
            'status' => 'pending',
            'pairing_code' => $code,
            'pairing_code_expires_at' => now()->addMinutes(10),
            'created_by_user_id' => $request->user()->id,
        ]);

        $this->audit->log('device.pairing_code.generated', [
            'tenant_id' => $shop->tenant_id,
            'entity_type' => 'device',
            'entity_id' => $device->id,
            'shop_id' => $shop->id,
        ]);

        return response()->json([
            'code' => $code,
            'expires_at' => optional($device->pairing_code_expires_at)->toIso8601String(),
        ]);
    }

    public function revoke(Request $request, Device $device): RedirectResponse
    {
        $this->ensureDeviceBelongsToUser($request, $device);
        $device->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'pairing_code' => null,
            'pairing_code_expires_at' => null,
        ]);

        $this->audit->log('device.revoked', [
            'tenant_id' => $device->tenant_id,
            'entity_type' => 'device',
            'entity_id' => $device->id,
        ]);

        return back()->with('status', 'Device revoked.');
    }

    private function ensureDeviceBelongsToUser(Request $request, Device $device): void
    {
        if ((string) $device->tenant_id !== (string) $request->user()->tenant_id) {
            abort(403);
        }
    }
}
