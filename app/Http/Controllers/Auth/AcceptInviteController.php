<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\Invite\InviteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AcceptInviteController extends Controller
{
    public function __construct(private readonly InviteService $invites)
    {
    }

    public function show(string $token): Response
    {
        $user = $this->resolveInvite($token);

        return Inertia::render('Auth/AcceptInvite', [
            'token' => $token,
            'invitee' => [
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
                'shops' => $user->shops()->orderBy('name')->get(['id', 'name']),
                'organisation' => [
                    'name' => $user->organisation?->trading_name ?? $user->organisation?->name,
                    'logo' => $user->organisation?->logo,
                ],
            ],
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $user = $this->resolveInvite($token);
        $data = $request->validate([
            'first_name' => ['required', 'string', 'min:2'],
            'last_name' => ['required', 'string', 'min:2'],
            'password' => ['required', 'string', 'min:10', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $this->invites->accept($user, $data);
        Auth::login($user);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function decline(string $token): RedirectResponse
    {
        $user = $this->resolveInvite($token);
        $this->invites->decline($user);

        return redirect()->route('login')->with('status', 'Invitation declined.');
    }

    public function resendFromLogin(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', strtolower($request->input('email')))->first();
        if ($user && $user->status === 'invited') {
            $this->invites->resend($user);
        }

        return back()->with('status', 'If your invite is still pending, we resent it.');
    }

    public function resendForUser(Request $request, User $user): RedirectResponse
    {
        if (! $request->user()->hasRole('org_owner', 'shop_manager', 'glint_super_admin')) {
            throw ValidationException::withMessages(['user' => 'Not allowed.']);
        }

        $this->invites->resend($user);

        return back()->with('status', 'Invite resent.');
    }

    private function resolveInvite(string $token): User
    {
        $hash = hash('sha256', $token);
        $user = User::where('invite_token_hash', $hash)->first();
        if (! $user) {
            abort(404);
        }

        if ($user->invite_expires_at && $user->invite_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'token' => 'This invitation has expired.',
            ]);
        }

        return $user;
    }
}
