<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Invite\InviteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private readonly InviteService $invites, private readonly AuditService $audit)
    {
    }

    public function index(Request $request): Response
    {
        $organisation = $request->user()->organisation;
        $users = $organisation?->users()->orderBy('name')->get();

        return Inertia::render('App/Users/Index', [
            'users' => $users?->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'shops' => $user->shops()->pluck('name'),
            ]) ?? [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(['shop_manager', 'staff'])],
            'shop_ids' => ['nullable', 'array'],
        ]);

        $organisation = $request->user()->organisation;
        $this->invites->issue($request->user(), $organisation, $request->only('name', 'email', 'role', 'shop_ids'));

        return back()->with('status', 'Invitation sent.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ]);

        $user->update(['status' => $request->input('status')]);

        $this->audit->log('user.status_updated', [
            'tenant_id' => $user->tenant_id,
            'entity_type' => 'user',
            'entity_id' => $user->getKey(),
            'context' => ['status' => $user->status],
        ]);

        return back()->with('status', 'User updated.');
    }
}
