<?php

return [
    'base_domain' => env('TENANT_BASE_DOMAIN', 'glintlabs.com'),
    'marketing_domain' => env('TENANT_MARKETING_DOMAIN', 'https://www.glintlabs.com'),
    'default_slug' => env('TENANT_DEFAULT_SLUG'),
    'ignored_subdomains' => array_values(array_filter(array_map('trim', explode(',', env('TENANT_IGNORED_SUBDOMAINS', 'www,app,admin,api,hq,hub,glint,platform'))))),
    'defaults' => [
        'name' => env('TENANT_DEFAULT_NAME', 'Glint Labs'),
        'logo' => env('TENANT_DEFAULT_LOGO', 'https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152'),
        'icon' => env('TENANT_DEFAULT_ICON', 'https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152'),
        'colors' => [
            'primary' => env('TENANT_DEFAULT_COLOR_PRIMARY', '#0f172a'),
            'secondary' => env('TENANT_DEFAULT_COLOR_SECONDARY', '#1e293b'),
            'accent' => env('TENANT_DEFAULT_COLOR_ACCENT', '#4FE1C1'),
        ],
        'font' => env('TENANT_DEFAULT_FONT', 'Inter'),
    ],
    'powered_by' => [
        'label' => env('TENANT_POWERED_BY_LABEL', 'Glint Labs'),
        'url' => env('TENANT_POWERED_BY_URL', 'https://www.glintlabs.com'),
        'show_by_default' => filter_var(env('TENANT_POWERED_BY_SHOW', true), FILTER_VALIDATE_BOOLEAN),
    ],
];
