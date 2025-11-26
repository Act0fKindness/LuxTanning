<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function redirectTo()
    {
        $user = $this->guard()->user();

        if (!$user) {
            return RouteServiceProvider::HOME;
        }

        return $this->determineRedirectPath($user);
    }

    protected function authenticated(Request $request, $user)
    {
        return redirect()->intended($this->determineRedirectPath($user));
    }

    protected function determineRedirectPath($user): string
    {
        $role = $user->role;

        if ($role !== 'platform_admin' && Schema::hasTable('tenant_user')) {
            $membership = DB::table('tenant_user')
                ->where('user_id', $user->id)
                ->first();

            if ($membership && $membership->role) {
                $role = $membership->role;
            }
        }

        return match ($role) {
            'platform_admin' => '/glint/platform',
            'owner' => '/owner/overview',
            'manager' => '/manager/dispatch/board',
            'cleaner' => '/cleaner/today',
            'accountant' => '/accountant/invoices',
            'support' => '/support/tickets',
            'customer' => '/customer/dashboard',
            default => RouteServiceProvider::HOME,
        };
    }

    /**
     * Attempt to log the user into the application.
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            true
        );
    }
}
