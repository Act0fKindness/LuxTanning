<?php

namespace App\Support;

use App\Models\Address;
use App\Models\Job;
use App\Models\TenantUser;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CustomerProfileService
{
    public function findOrCreateCustomer(string $tenantId, array $data): User
    {
        $email = trim($data['email'] ?? '');
        $customer = null;

        if ($email !== '') {
            $customer = User::firstOrNew(['email' => $email]);
        } else {
            $customer = $this->findCustomerByAddress($tenantId, $data);
            if (!$customer) {
                $email = $this->generatePlaceholderEmail($tenantId, $data);
                $customer = User::firstOrNew(['email' => $email]);
            }
        }

        $name = $this->deriveName($data);
        $phone = $data['phone'] ?? null;

        if (!$customer || !$customer->exists) {
            $customer = $customer ?: new User();
            $customer->tenant_id = $tenantId;
            $customer->name = $name;
            if ($email !== '') {
                $customer->email = $email;
            }
            $customer->phone = $phone;
            $customer->role = 'customer';
            if (!$customer->password) {
                $customer->password = bcrypt(Str::random(24));
            }
            $customer->save();
        } else {
            $customer->tenant_id = $customer->tenant_id ?: $tenantId;
            if ($name && !$customer->name) {
                $customer->name = $name;
            }
            if ($phone) {
                $customer->phone = $phone;
            }
            $customer->save();
        }

        $this->ensureMembership($tenantId, $customer);

        return $customer;
    }

    public function ensureMembership(string $tenantId, User $customer): void
    {
        if (!Schema::hasTable('tenant_user')) {
            return;
        }

        TenantUser::firstOrCreate([
            'tenant_id' => $tenantId,
            'user_id' => $customer->id,
        ], [
            'role' => 'customer',
            'status' => 'active',
        ]);
    }

    public function createAddressFromChecklist(string $tenantId, User $customer, array $checklist, bool $createWhenMissing = true): ?Address
    {
        $line1 = $checklist['address_line1'] ?? null;
        if (!$line1 && !$createWhenMissing) {
            return null;
        }

        if (!$line1) {
            $line1 = 'Job address';
        }

        $column = $this->addressUserColumn();
        $addressQuery = Address::where('tenant_id', $tenantId)
            ->where('line1', $line1);
        if ($column) {
            $addressQuery->where($column, $customer->id);
        }
        $address = $addressQuery->first();

        $attributes = [
            'tenant_id' => $tenantId,
            'line1' => $line1,
            'line2' => $checklist['address_line2'] ?? null,
            'city' => $checklist['city'] ?? null,
            'postcode' => isset($checklist['postcode']) ? strtoupper($checklist['postcode']) : null,
            'lat' => isset($checklist['lat']) ? (float) $checklist['lat'] : null,
            'lng' => isset($checklist['lng']) ? (float) $checklist['lng'] : null,
        ];

        if ($column) {
            $attributes[$column] = $customer->id;
        }

        if ($address) {
            $address->fill($attributes)->save();
        } else {
            $address = Address::create($attributes);
        }

        if (method_exists($customer, 'profile')) {
            $customer->profile()->updateOrCreate([], ['default_address_id' => $address->id]);
        } else {
            UserProfile::updateOrCreate([
                'user_id' => $customer->id,
            ], [
                'default_address_id' => $address->id,
            ]);
        }

        return $address;
    }

    public function attachJobToCustomer(Job $job, User $customer, ?Address $address = null, ?array $checklist = null): void
    {
        $payload = $checklist && is_array($checklist)
            ? $checklist
            : (is_array($job->checklist_json) ? $job->checklist_json : (json_decode($job->checklist_json ?? '[]', true) ?: []));

        $payload['customer_id'] = $customer->id;

        if ($address) {
            $payload['address_line1'] = $address->line1;
            $payload['address_line2'] = $address->line2;
            $payload['city'] = $address->city;
            $payload['postcode'] = $address->postcode;
            $payload['lat'] = $address->lat;
            $payload['lng'] = $address->lng;
        }

        $job->checklist_json = $payload;
        $job->save();
    }

    public function reassignAddresses(string $tenantId, User $from, User $to): void
    {
        $column = $this->addressUserColumn();
        if (!$column || !Schema::hasTable('addresses')) {
            return;
        }

        Address::where('tenant_id', $tenantId)
            ->where($column, $from->id)
            ->update([$column => $to->id]);
    }

    protected function deriveName(array $data): string
    {
        $name = trim($data['name'] ?? '');
        if ($name !== '') {
            return $name;
        }

        $address = trim($data['address_line1'] ?? '');
        if ($address !== '') {
            return $address;
        }

        $city = trim($data['city'] ?? '');
        if ($city !== '') {
            return $city . ' customer';
        }

        return 'Customer';
    }

    protected function generatePlaceholderEmail(string $tenantId, array $data): string
    {
        $addressKey = strtolower(trim(($data['address_line1'] ?? '') . '|' . ($data['postcode'] ?? '')));
        if ($addressKey === '|' || $addressKey === '') {
            $addressKey = Str::uuid()->toString();
        }

        $hash = substr(md5($addressKey), 0, 10);
        $tenantSlug = Str::slug($tenantId, '');

        return sprintf('%s+%s@customers.glint', $tenantSlug ?: 'tenant', $hash);
    }

    protected function findCustomerByAddress(string $tenantId, array $data): ?User
    {
        $column = $this->addressUserColumn();
        $line1 = trim($data['address_line1'] ?? '');
        if (!$column || $line1 === '' || !Schema::hasTable('addresses')) {
            return null;
        }

        $query = Address::where('tenant_id', $tenantId)
            ->where('line1', $line1)
            ->whereNotNull($column);

        if (!empty($data['postcode'])) {
            $query->where('postcode', strtoupper($data['postcode']));
        }

        $address = $query->orderByDesc('created_at')->first();
        if ($address && $address->{$column}) {
            return User::find($address->{$column});
        }

        return null;
    }

    protected function addressUserColumn(): ?string
    {
        if (!Schema::hasTable('addresses')) {
            return null;
        }

        if (Schema::hasColumn('addresses', 'user_id')) {
            return 'user_id';
        }

        if (Schema::hasColumn('addresses', 'customer_id')) {
            return 'customer_id';
        }

        return null;
    }
}
