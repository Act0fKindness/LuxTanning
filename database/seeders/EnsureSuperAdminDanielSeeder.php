<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class EnsureSuperAdminDanielSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'daniel.robert.harding@gmail.com';
        $password = 'BRinhf9RTY!!!';

        $user = User::firstOrNew(['email' => $email]);
        if (!$user->exists || empty($user->id)) {
            $user->id = (string) Str::uuid();
        }
        $user->name = 'Daniel Harding';
        $user->role = 'platform_admin';
        $user->tenant_id = null;
        $user->password = Hash::make($password);
        $user->email_verified_at = Carbon::now();
        // In case a record exists with a deleted_at value
        $user->setAttribute('deleted_at', null);
        $user->save();
    }
}
