<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Audit\AuditService;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VerificationController extends Controller
{
    use VerifiesEmails;

    public function __construct(private readonly AuditService $audit)
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'send');
    }

    public function notice(Request $request): Response
    {
        return Inertia::render('Auth/VerifyEmail', [
            'email' => $request->user()->email,
            'status' => session('status'),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $request->user()->sendEmailVerificationNotification();
        $this->audit->log('auth.verify.resend', [
            'tenant_id' => $request->user()->tenant_id,
            'entity_type' => 'user',
            'entity_id' => $request->user()->getKey(),
        ]);

        return back()->with('status', 'Verification email sent.');
    }

    public function changeEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->user()->id)],
        ]);

        $user = $request->user();
        $user->forceFill([
            'email' => strtolower($request->input('email')),
            'email_verified_at' => null,
        ])->save();
        $user->sendEmailVerificationNotification();

        $this->audit->log('auth.verify.email_changed', [
            'tenant_id' => $user->tenant_id,
            'entity_type' => 'user',
            'entity_id' => $user->getKey(),
        ]);

        return back()->with('status', 'Email updated. Check your inbox to verify.');
    }
}
