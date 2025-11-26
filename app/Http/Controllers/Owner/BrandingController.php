<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandingController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 403);
        abort_unless(in_array($user->role, ['owner', 'platform_admin', 'tenant_admin']), 403);

        $tenant = $this->resolveTenantFor($user);
        abort_unless($tenant, 403, 'Unable to determine company.');

        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'marketing_url' => ['nullable', 'url', 'max:255'],
            'back_to_site_url' => ['nullable', 'url', 'max:255'],
            'primary_color' => ['nullable', 'regex:/^#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'secondary_color' => ['nullable', 'regex:/^#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'accent_color' => ['nullable', 'regex:/^#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'workspace_owner_sidebar' => ['nullable', 'regex:/^#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'workspace_staff_sidebar' => ['nullable', 'regex:/^#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'workspace_customer_sidebar' => ['nullable', 'regex:/^#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'font' => ['nullable', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'max:3072'],
            'icon' => ['nullable', 'image', 'max:1024'],
            'integrations' => ['nullable', 'array'],
            'integrations.*' => [
                'string',
                Rule::in(['quote', 'status', 'support', 'booking']),
            ],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:120'],
            'vat_number' => ['nullable', 'string', 'max:120'],
            'timezone' => ['nullable', 'string', 'max:120'],
            'currency' => ['nullable', 'string', 'max:10'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'support_hours' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:2'],
            'sync_support_details' => ['nullable', 'boolean'],
        ]);

        $theme = $tenant->theme_json ?? [];
        $branding = Arr::get($theme, 'branding', []);

        if (!empty($data['company_name'])) {
            $branding['company_name'] = $data['company_name'];
        }

        if (!empty($data['marketing_url'])) {
            $branding['marketing_url'] = $data['marketing_url'];
        }

        if (!empty($data['back_to_site_url'])) {
            $branding['back_to_site_url'] = $data['back_to_site_url'];
        }

        if (!empty($data['font'])) {
            $branding['font'] = $data['font'];
        }

        $branding['colors'] = array_filter([
            'primary' => $this->normaliseHex($data['primary_color'] ?? Arr::get($branding, 'colors.primary')),
            'secondary' => $this->normaliseHex($data['secondary_color'] ?? Arr::get($branding, 'colors.secondary')),
            'accent' => $this->normaliseHex($data['accent_color'] ?? Arr::get($branding, 'colors.accent')),
        ]);

        $existingWorkspaces = Arr::get($branding, 'workspaces', []);
        $workspacePalette = [
            'owner' => $this->normaliseHex($data['workspace_owner_sidebar'] ?? Arr::get($existingWorkspaces, 'owner.sidebar')),
            'staff' => $this->normaliseHex($data['workspace_staff_sidebar'] ?? Arr::get($existingWorkspaces, 'staff.sidebar')),
            'customer' => $this->normaliseHex($data['workspace_customer_sidebar'] ?? Arr::get($existingWorkspaces, 'customer.sidebar')),
        ];

        $workspaces = [];
        foreach ($workspacePalette as $key => $color) {
            if ($color) {
                $workspaces[$key] = array_filter([
                    'sidebar' => $color,
                ]);
            }
        }

        if (!empty($workspaces)) {
            $branding['workspaces'] = $workspaces;
        } else {
            unset($branding['workspaces']);
        }

        if ($request->hasFile('logo')) {
            $branding['logo'] = $this->storeAsset($request->file('logo'), $tenant->id, 'logo');
        }

        if ($request->hasFile('icon')) {
            $branding['icon'] = $this->storeAsset($request->file('icon'), $tenant->id, 'icon');
        }

        if (array_key_exists('integrations', $data)) {
            $integrations = array_values(array_unique(array_filter($data['integrations'] ?? [], fn ($value) => is_string($value) && $value !== '')));
            if (!empty($integrations)) {
                $branding['integrations'] = $integrations;
            } else {
                unset($branding['integrations']);
            }
        }

        $this->setProfileFields($branding, $data);

        Arr::set($theme, 'branding', $branding);
        $tenant->theme_json = $theme;
        $tenant->save();

        return redirect()->back()->with('success', 'Branding updated successfully.');
    }

    protected function setProfileFields(array &$branding, array $data): void
    {
        $profile = Arr::get($branding, 'profile', []);

        $simpleFields = [
            'legal_name',
            'registration_number',
            'vat_number',
            'timezone',
            'currency',
            'support_email',
            'support_phone',
            'support_hours',
            'address_line1',
            'address_line2',
            'city',
            'region',
            'postal_code',
            'country',
        ];

        foreach ($simpleFields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $this->cleanProfileValue($data[$field] ?? null);
            if ($value === null) {
                unset($profile[$field]);
                continue;
            }

            if ($field === 'country') {
                $value = strtoupper($value);
            }

            $profile[$field] = $value;
        }

        if (array_key_exists('sync_support_details', $data)) {
            $profile['sync_support_details'] = (bool) $data['sync_support_details'];
        }

        if (!empty(array_filter($profile, fn ($value) => $value !== null && $value !== '')) || array_key_exists('sync_support_details', $profile)) {
            Arr::set($branding, 'profile', $profile);
        } else {
            unset($branding['profile']);
        }
    }

    protected function cleanProfileValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    protected function resolveTenantFor(User $user): ?Tenant
    {
        if ($user->tenant_id && ($tenant = Tenant::find($user->tenant_id))) {
            return $tenant;
        }

        if (Schema::hasTable('tenant_user')) {
            $tenantId = DB::table('tenant_user')->where('user_id', $user->id)->value('tenant_id');
            if ($tenantId) {
                return Tenant::find($tenantId);
            }
        }

        return null;
    }

    protected function normaliseHex(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $trimmed = ltrim($value, '#');
        if (!preg_match('/^(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $trimmed)) {
            return null;
        }

        if (strlen($trimmed) === 3) {
            $trimmed = preg_replace('/([0-9a-fA-F])/', '$1$1', $trimmed);
        }

        return '#' . strtolower($trimmed);
    }

    protected function storeAsset($file, string $tenantId, string $type): string
    {
        $path = $file->storePubliclyAs(
            'branding/' . $tenantId,
            $type . '-' . Str::uuid() . '.' . strtolower($file->getClientOriginalExtension() ?: 'png'),
            'public'
        );

        return Storage::disk('public')->url($path);
    }
}
