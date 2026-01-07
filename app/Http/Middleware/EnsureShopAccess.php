<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureShopAccess
{
    public function handle(Request $request, Closure $next, string $parameter = 'shop')
    {
        $user = $request->user();
        if (! $user) {
            throw new AccessDeniedHttpException('Authentication required.');
        }

        $shopParam = $request->route($parameter);
        $shopId = null;
        if ($shopParam instanceof Shop) {
            $shopId = $shopParam->getKey();
        } elseif ($shopParam) {
            $shopId = (string) $shopParam;
        }

        if ($user->canAccessShop($shopId)) {
            return $next($request);
        }

        throw new AccessDeniedHttpException('Shop access denied.');
    }
}
