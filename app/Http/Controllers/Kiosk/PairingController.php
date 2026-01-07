<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PairingController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function show(): Response
    {
        return Inertia::render('Kiosk/PairDevice');
    }

    public function pair(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $device = Device::where('pairing_code', $data['code'])
            ->where('pairing_code_expires_at', '>', now())
            ->firstOrFail();

        $device->update([
            'name' => $data['device_name'],
            'status' => 'active',
            'pairing_code' => null,
            'pairing_code_expires_at' => null,
            'paired_at' => now(),
            'identifier' => $device->identifier ?: (string) Str::uuid(),
        ]);

        $this->audit->log('device.paired', [
            'tenant_id' => $device->tenant_id,
            'shop_id' => $device->shop_id,
            'entity_type' => 'device',
            'entity_id' => $device->id,
        ]);

        return response()->json([
            'device_id' => $device->id,
            'shop_id' => $device->shop_id,
        ]);
    }
}
