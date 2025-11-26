<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $rows = DB::table('users')->select('id as user_id','tenant_id','role')->whereNotNull('tenant_id')->get();
        foreach ($rows as $r) {
            if (!$r->tenant_id) continue;
            $role = in_array($r->role, ['owner','manager','cleaner','accountant','customer']) ? $r->role : 'customer';
            $exists = DB::table('tenant_user')
                ->where('tenant_id', $r->tenant_id)
                ->where('user_id', $r->user_id)
                ->exists();
            if (!$exists) {
                DB::table('tenant_user')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'tenant_id' => $r->tenant_id,
                    'user_id' => $r->user_id,
                    'role' => $role,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep memberships; no destructive rollback
    }
};

