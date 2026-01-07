<?php

namespace App\Services\Invite;

use App\Models\Organisation;
use App\Models\Shop;
use App\Models\User;
use App\Notifications\UserInviteNotification;
use App\Services\Audit\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteService
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function issue(User $actor, Organisation $organisation, array $input): User
    {
        $email = strtolower(trim($input['email']));
        $name = trim($input['name'] ?? '');
        $role = $input['role'];
        $shopIds = Arr::wrap($input['shop_ids'] ?? []);
        $status = $input['status'] ?? 'invited';
        $user = User::updateOrCreate(
            ['email' => $email, 'tenant_id' => $organisation->getKey()],
            [
                'name' => $name ?: $email,
                'tenant_id' => $organisation->getKey(),
                'role' => $role,
            ]
        );

        $token = Str::random(64);
        $user->fill([
            'status' => $status,
            'tenant_id' => $organisation->getKey(),
            'role' => $role,
            'invited_by_user_id' => $actor->getKey(),
            'invite_token_hash' => hash('sha256', $token),
            'invite_expires_at' => Carbon::now()->addDays(7),
            'invite_accepted_at' => null,
        ]);
        $user->save();

        $this->syncShops($user, $shopIds);
        $user->notify(new UserInviteNotification($organisation, $role, $token));

        $this->audit->log('auth.invite.created', [
            'tenant_id' => $organisation->getKey(),
            'entity_type' => 'user',
            'entity_id' => $user->getKey(),
            'context' => [
                'invited_by' => $actor->getKey(),
                'role' => $role,
            ],
        ]);

        return $user;
    }

    public function resend(User $user): void
    {
        if ($user->status !== 'invited') {
            throw ValidationException::withMessages([
                'user' => 'Only invited users can be resent.',
            ]);
        }

        $token = Str::random(64);
        $user->fill([
            'invite_token_hash' => hash('sha256', $token),
            'invite_expires_at' => Carbon::now()->addDays(7),
        ])->save();
        $user->notify(new UserInviteNotification($user->organisation, $user->role, $token));

        $this->audit->log('auth.invite.resent', [
            'tenant_id' => $user->tenant_id,
            'entity_type' => 'user',
            'entity_id' => $user->getKey(),
        ]);
    }

    public function accept(User $user, array $input): User
    {
        if ($user->status !== 'invited') {
            throw ValidationException::withMessages(['token' => 'Invitation already processed.']);
        }

        if ($user->invite_expires_at && $user->invite_expires_at->isPast()) {
            throw ValidationException::withMessages(['token' => 'This invitation has expired.']);
        }

        $user->fill([
            'name' => trim(($input['first_name'] ?? '').' '.($input['last_name'] ?? '')) ?: $user->name,
            'password' => Hash::make($input['password']),
            'status' => 'active',
            'invite_token_hash' => null,
            'invite_accepted_at' => Carbon::now(),
            'email_verified_at' => Carbon::now(),
        ])->save();

        $this->audit->log('auth.invite.accepted', [
            'tenant_id' => $user->tenant_id,
            'entity_type' => 'user',
            'entity_id' => $user->getKey(),
        ]);

        return $user;
    }

    public function decline(User $user): void
    {
        $user->update([
            'status' => 'disabled',
            'invite_token_hash' => null,
            'invite_expires_at' => null,
        ]);

        $this->audit->log('auth.invite.declined', [
            'tenant_id' => $user->tenant_id,
            'entity_type' => 'user',
            'entity_id' => $user->getKey(),
        ]);
    }

    private function syncShops(User $user, array $shopIds): void
    {
        if ($user->shop_access_mode === 'single' && $user->primary_shop_id) {
            return;
        }

        if (empty($shopIds)) {
            $user->shops()->sync([]);
            return;
        }

        $validShopIds = Shop::whereIn('id', $shopIds)
            ->where('tenant_id', $user->tenant_id)
            ->pluck('id')
            ->all();

        $user->shops()->sync($validShopIds);
    }
}
