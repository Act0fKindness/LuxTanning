<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;

class CoreSetupSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the core tenant exists
        $tenantName = 'AOK World';
        $tenantSlug = Str::slug($tenantName);

        $tenant = Tenant::firstOrCreate(
            ['slug' => $tenantSlug],
            [
                'name' => $tenantName,
                'domain' => null,
                'country' => 'GB',
                'status' => 'active',
                'fee_tier' => null,
                'vat_scheme' => 'standard',
            ]
        );

        // Super admin (platform_admin) - Daniel Harding
        $superAdminEmail = 'daniel.robert.harding@gmail.com';
        $superAdmin = User::firstOrNew(['email' => $superAdminEmail]);
        $superAdmin->name = 'Daniel Harding';
        $superAdmin->role = 'platform_admin';
        $superAdmin->tenant_id = null; // platform-wide
        if (!$superAdmin->exists || empty($superAdmin->password)) {
            $superAdmin->password = Hash::make('ChangeMe123!');
        }
        $superAdmin->save();

        // Company owner for AOK World
        $ownerEmail = 'daniel.robert.harding+wow@gmail.com';
        $owner = User::firstOrNew(['email' => $ownerEmail]);
        $owner->name = 'Daniel Harding';
        $owner->role = 'owner';
        $owner->tenant_id = $tenant->id;
        if (!$owner->exists || empty($owner->password)) {
            $owner->password = Hash::make('ChangeMe123!');
        }
        $owner->save();

        // Ensure membership record exists
        TenantUser::firstOrCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
        ], [ 'role' => 'owner', 'status' => 'active' ]);
    }
}
