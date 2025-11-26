<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AokCleanersSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = DB::table('tenants')->where('slug','aok-world')->first();
        if (!$tenant) return; // rely on CoreSetupSeeder to create it
        $tenantId = $tenant->id;

        $cleaners = [
            ['name' => 'Alice Cleaner', 'email' => 'alice.cleaner+aok@glintlabs.dev'],
            ['name' => 'Bob Cleaner', 'email' => 'bob.cleaner+aok@glintlabs.dev'],
            ['name' => 'Charlie Cleaner', 'email' => 'charlie.cleaner+aok@glintlabs.dev'],
        ];

        foreach ($cleaners as $c) {
            $user = DB::table('users')->where('email', $c['email'])->first();
            if (!$user) {
                DB::table('users')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'name' => $c['name'],
                    'email' => $c['email'],
                    'role' => 'cleaner',
                    'password' => Hash::make('ChangeMe123!'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('users')->where('id',$user->id)->update([
                    'tenant_id' => $tenantId,
                    'role' => 'cleaner',
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

