<?php

namespace App\Services\Audit;

use App\Models\GlintAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public function log(string $action, array $payload = []): void
    {
        try {
            $request = request();
            $user = $request?->user();
            $context = Arr::get($payload, 'context', []);
            if ($request instanceof Request) {
                $context = array_merge([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'path' => $request->path(),
                ], $context);
            }

            GlintAuditLog::create([
                'tenant_id' => $payload['tenant_id'] ?? $user?->tenant_id,
                'shop_id' => $payload['shop_id'] ?? null,
                'actor_type' => $payload['actor_type'] ?? ($user ? 'user' : 'system'),
                'actor_id' => $payload['actor_id'] ?? $user?->getKey(),
                'action' => $action,
                'entity_type' => $payload['entity_type'] ?? null,
                'entity_id' => $payload['entity_id'] ?? null,
                'before_json' => $payload['before'] ?? null,
                'after_json' => $payload['after'] ?? null,
                'context_json' => $context,
            ]);
        } catch (\Throwable $e) {
            Log::warning('audit_log_failed', [
                'action' => $action,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
