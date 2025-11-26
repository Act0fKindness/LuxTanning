<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantBrandingResolver
{
    public function resolve(Request $request): array
    {
        if (!Schema::hasTable('tenants')) {
            return $this->fallback($request);
        }

        $tenant = $this->determineTenant($request);

        if (!$tenant) {
            return $this->fallback($request);
        }

        return $this->formatResponse($tenant, $request);
    }

    protected function determineTenant(Request $request): ?Tenant
    {
        $host = $request->getHost();
        if ($slug = $this->slugFromHost($host)) {
            $tenant = Tenant::where('slug', $slug)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        if ($host) {
            $domainMatch = Tenant::where('domain', $host)->first();
            if ($domainMatch) {
                return $domainMatch;
            }
        }

        $user = $request->user();
        if ($user) {
            if ($user->tenant_id && ($tenant = Tenant::find($user->tenant_id))) {
                return $tenant;
            }

            if (Schema::hasTable('tenant_user')) {
                $tenantId = DB::table('tenant_user')->where('user_id', $user->id)->value('tenant_id');
                if ($tenantId && ($tenant = Tenant::find($tenantId))) {
                    return $tenant;
                }
            }
        }

        $defaultSlug = config('tenant.default_slug');
        if ($defaultSlug) {
            return Tenant::where('slug', $defaultSlug)->first();
        }

        return null;
    }

    protected function slugFromHost(?string $host): ?string
    {
        $baseDomain = strtolower((string) config('tenant.base_domain'));
        if (!$host || !$baseDomain) {
            return null;
        }

        $host = strtolower($host);
        if ($host === $baseDomain) {
            return null;
        }

        $needle = '.' . $baseDomain;
        if (!Str::endsWith($host, $needle)) {
            return null;
        }

        $subdomain = Str::beforeLast($host, $needle);
        $subdomain = trim($subdomain, '.');
        if ($subdomain === '') {
            return null;
        }

        if (in_array($subdomain, config('tenant.ignored_subdomains', []), true)) {
            return null;
        }

        return $subdomain;
    }

    protected function formatResponse(Tenant $tenant, Request $request): array
    {
        $branding = $this->brandingFromTheme($tenant);

        return [
            'id' => $tenant->id,
            'name' => $branding['company'] ?? $tenant->name,
            'slug' => $tenant->slug,
            'host' => $request->getHost(),
            'app_url' => $this->tenantBaseUrl($tenant->slug),
            'marketing_url' => $branding['marketing_url'],
            'back_to_site_url' => $branding['back_link'],
            'branding' => [
                'logo' => $branding['logo'],
                'icon' => $branding['icon'],
                'colors' => $branding['colors'],
                'font' => $branding['font'],
                'powered_by' => $branding['powered_by'],
                'workspaces' => $branding['workspaces'],
                'integrations' => $branding['integrations'],
                'profile' => $branding['profile'],
            ],
            'company_profile' => $branding['profile'],
        ];
    }

    protected function brandingFromTheme(Tenant $tenant): array
    {
        $theme = $tenant->theme_json ?? [];
        $branding = Arr::get($theme, 'branding', []);
        $defaults = config('tenant.defaults');

        $marketingUrl = Arr::get($branding, 'marketing_url')
            ?: $tenant->domain
            ?: config('tenant.marketing_domain', config('app.url'));
        $backLink = Arr::get($branding, 'back_to_site_url') ?: $marketingUrl;

        $poweredByConfig = Arr::get($branding, 'powered_by', []);
        $poweredBy = [
            'label' => Arr::get($poweredByConfig, 'label', config('tenant.powered_by.label', 'Glint Labs')),
            'url' => Arr::get($poweredByConfig, 'url', config('tenant.powered_by.url', 'https://www.glintlabs.com')),
            'show' => Arr::has($poweredByConfig, 'show')
                ? (bool) Arr::get($poweredByConfig, 'show')
                : (bool) config('tenant.powered_by.show_by_default', true),
        ];

        return [
            'company' => Arr::get($branding, 'company_name', $tenant->name),
            'logo' => Arr::get($branding, 'logo', Arr::get($theme, 'assets.logo', Arr::get($defaults, 'logo'))),
            'icon' => Arr::get($branding, 'icon', Arr::get($theme, 'assets.icon', Arr::get($defaults, 'icon'))),
            'colors' => [
                'primary' => Arr::get($branding, 'colors.primary', Arr::get($defaults, 'colors.primary', '#0f172a')),
                'secondary' => Arr::get($branding, 'colors.secondary', Arr::get($defaults, 'colors.secondary', '#1e293b')),
                'accent' => Arr::get($branding, 'colors.accent', Arr::get($defaults, 'colors.accent', '#4FE1C1')),
            ],
            'font' => Arr::get($branding, 'font', Arr::get($defaults, 'font', 'Inter')),
            'marketing_url' => $marketingUrl,
            'back_link' => $backLink,
            'powered_by' => $poweredBy,
            'workspaces' => $this->formatWorkspaceOverrides(Arr::get($branding, 'workspaces', [])),
            'integrations' => $this->formatIntegrations(Arr::get($branding, 'integrations', [])),
            'profile' => $this->formatCompanyProfile(Arr::get($branding, 'profile', []), $tenant),
        ];
    }

    protected function formatCompanyProfile($input, Tenant $tenant): array
    {
        if (!is_array($input)) {
            $input = [];
        }

        $lines = array_values(array_filter([
            Arr::get($input, 'address_line1'),
            Arr::get($input, 'address_line2'),
            trim(implode(' ', array_filter([Arr::get($input, 'city'), Arr::get($input, 'region')]))),
            Arr::get($input, 'postal_code'),
            Arr::get($input, 'country'),
        ], fn ($value) => $value !== null && $value !== ''));

        $syncDetails = Arr::get($input, 'sync_support_details');
        $syncFlag = is_bool($syncDetails) ? $syncDetails : true;

        return [
            'legal_name' => Arr::get($input, 'legal_name'),
            'registration_number' => Arr::get($input, 'registration_number'),
            'vat_number' => Arr::get($input, 'vat_number'),
            'timezone' => Arr::get($input, 'timezone'),
            'currency' => Arr::get($input, 'currency'),
            'support_email' => Arr::get($input, 'support_email'),
            'support_phone' => Arr::get($input, 'support_phone'),
            'support_hours' => Arr::get($input, 'support_hours'),
            'address_line1' => Arr::get($input, 'address_line1'),
            'address_line2' => Arr::get($input, 'address_line2'),
            'city' => Arr::get($input, 'city'),
            'region' => Arr::get($input, 'region'),
            'postal_code' => Arr::get($input, 'postal_code'),
            'country' => Arr::get($input, 'country'),
            'address_lines' => $lines,
            'sync_support_details' => $syncFlag,
            'sync_support_details_label' => $syncFlag ? 'Enabled' : 'Disabled',
        ];
    }

    protected function formatWorkspaceOverrides($input): array
    {
        if (!is_array($input)) {
            return [];
        }

        $allowed = ['owner', 'staff', 'customer'];
        $output = [];

        foreach ($allowed as $key) {
            $sidebar = Arr::get($input, $key . '.sidebar');
            if ($sidebar && is_string($sidebar)) {
                $output[$key] = [
                    'sidebar' => $sidebar,
                ];
            }
        }

        return $output;
    }

    protected function formatIntegrations($input): array
    {
        if (!is_array($input)) {
            return [];
        }

        $allowed = ['quote', 'status', 'support', 'booking'];
        $unique = [];

        foreach ($input as $value) {
            if (!is_string($value)) {
                continue;
            }

            $normalized = strtolower(trim($value));
            if ($normalized === '' || !in_array($normalized, $allowed, true)) {
                continue;
            }

            if (!in_array($normalized, $unique, true)) {
                $unique[] = $normalized;
            }
        }

        return $unique;
    }

    protected function tenantBaseUrl(string $slug): string
    {
        $appUrl = config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
        $domain = config('tenant.base_domain', parse_url($appUrl, PHP_URL_HOST));
        $domain = ltrim($domain ?? '', '.');

        return sprintf('%s://%s.%s', $scheme, $slug, $domain);
    }

    protected function fallback(Request $request): array
    {
        $defaults = config('tenant.defaults');
        $marketingUrl = config('tenant.marketing_domain', config('app.url'));

        return [
            'id' => null,
            'name' => Arr::get($defaults, 'name', config('app.name', 'Glint Labs')),
            'slug' => null,
            'host' => $request->getHost(),
            'app_url' => config('app.url'),
            'marketing_url' => $marketingUrl,
            'back_to_site_url' => $marketingUrl,
            'branding' => [
                'logo' => Arr::get($defaults, 'logo'),
                'icon' => Arr::get($defaults, 'icon'),
                'colors' => [
                    'primary' => Arr::get($defaults, 'colors.primary', '#0f172a'),
                    'secondary' => Arr::get($defaults, 'colors.secondary', '#1e293b'),
                    'accent' => Arr::get($defaults, 'colors.accent', '#4FE1C1'),
                ],
                'font' => Arr::get($defaults, 'font', 'Inter'),
                'powered_by' => [
                    'label' => config('tenant.powered_by.label', 'Glint Labs'),
                    'url' => config('tenant.powered_by.url', 'https://www.glintlabs.com'),
                    'show' => (bool) config('tenant.powered_by.show_by_default', true),
                ],
                'workspaces' => [],
                'integrations' => [],
            ],
        ];
    }
}
