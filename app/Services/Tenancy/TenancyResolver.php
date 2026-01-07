<?php

namespace App\Services\Tenancy;

use App\Models\Organisation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Collection;

class TenancyResolver
{
    public function organisationFor(User $user): ?Organisation
    {
        return $user->organisation;
    }

    public function shopsFor(User $user): Collection
    {
        if ($user->shop_access_mode === 'all') {
            return $user->organisation?->shops ?? collect();
        }

        if ($user->shop_access_mode === 'single' && $user->primary_shop_id) {
            return Shop::where('id', $user->primary_shop_id)->get();
        }

        return $user->shops;
    }
}
