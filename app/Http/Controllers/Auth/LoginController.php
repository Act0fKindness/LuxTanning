<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthLoginEvent;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\Audit\AuditService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct(private readonly AuditService $audit)
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user instanceof User) {
            if ($user->status === 'invited') {
                $this->guard()->logout();
                $this->recordAttempt($request, $user, false, 'invited');
                throw ValidationException::withMessages([
                    'email' => 'Your account invitation has not been accepted yet.',
                ]);
            }

            if ($user->status === 'disabled') {
                $this->guard()->logout();
                $this->recordAttempt($request, $user, false, 'disabled');
                throw ValidationException::withMessages([
                    'email' => 'Account disabled. Contact your organisation owner.',
                ]);
            }

            if (! $user->hasVerifiedEmail()) {
                $this->guard()->logout();
                $this->recordAttempt($request, $user, false, 'unverified');
                throw ValidationException::withMessages([
                    'email' => 'Verify your email before signing in.',
                ]);
            }

            $user->forceFill([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ])->save();

            $this->recordAttempt($request, $user, true);
            RateLimiter::clear($this->throttleKey($request));

            return redirect()->intended($this->determineRedirectPath($user));
        }

        return redirect()->intended($this->redirectTo);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $this->recordAttempt($request, null, false, 'invalid');

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    protected function determineRedirectPath(User $user): string
    {
        return match ($user->role) {
            'glint_super_admin' => '/platform/organisations',
            'org_owner' => '/app',
            'shop_manager' => '/app/shops',
            'staff' => '/app/staff',
            default => $this->redirectTo,
        };
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }

    private function recordAttempt(Request $request, ?User $user, bool $success, string $reason = null): void
    {
        AuthLoginEvent::create([
            'user_id' => $user?->getKey(),
            'email' => strtolower($request->input('email')),
            'success' => $success,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->audit->log($success ? 'auth.login.success' : 'auth.login.failed', [
            'tenant_id' => $user?->tenant_id,
            'entity_type' => 'user',
            'entity_id' => $user?->getKey(),
            'context' => array_filter([
                'reason' => $reason,
                'ip' => $request->ip(),
            ]),
        ]);
    }
}
