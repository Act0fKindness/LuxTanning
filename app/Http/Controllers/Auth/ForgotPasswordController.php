<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct(private readonly AuditService $audit)
    {
        $this->middleware('guest');
    }

    public function showLinkRequestForm(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $email = strtolower($request->input('email'));
        $user = User::where('email', $email)->first();
        Password::sendResetLink(['email' => $email]);

        $this->audit->log('auth.password_reset.requested', [
            'tenant_id' => $user?->tenant_id,
            'entity_type' => 'user',
            'entity_id' => $user?->getKey(),
            'context' => ['email' => $email],
        ]);

        return back()->with('status', 'If an account exists for that email, we sent you a reset link.');
    }
}
