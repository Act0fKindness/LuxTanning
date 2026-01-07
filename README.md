# Lux Tanning OS

> **Reminder:** run `npm install && npm run build` after every change so the Laravel + Inertia + Vue bundles stay aligned.

Lux Tanning OS is a Laravel 10 + Inertia + Vue 3 monorepo built for multi-studio sun bed operators. Guests can browse courses, purchase memberships, and track minutes across devices. Studio staff (Glow Guides) get a PWA with lamp telemetry, playlists, and upsell prompts. Managers and owners orchestrate courses, marketing, finance, and compliance inside data-rich workspaces, while platform admins run the Lux ecosystem (tenants, health, billing, incidents) from a dedicated hub.

## Stack quickstart

- **Backend:** Laravel with Sanctum auth + queue-ready notifications. `php artisan serve` or Valet works out of the box.
- **Frontend:** Vue 3 bundled with Laravel Mix. Use `npm run dev` for watch mode and `npm run build` for production assets.
- **Authentication:** Traditional Laravel auth (login/forgot/reset) plus passwordless `/auth/magic-link` + `/auth/verify` flows for every role.
- **Maps & telemetry:** Google Maps key pulled from `.env` (`services.google.maps_key`) to power things like the course booking map panel.

## Experience architecture

```
resources/js
├── Components/Blueprint      # Section renderers (summary cards, timelines, tables, pricing plans, etc.)
├── Layouts                   # Public, Auth, Workspace, Hub shells
├── PageRegistry              # Navigation + per-role blueprint definitions
├── Pages                     # Thin PageShell wrappers per role
└── router-map.js             # Generated route inventory with host + branding metadata
```

Pages are defined declaratively inside `resources/js/PageRegistry/pages/*.js`. Each spec records:
- canonical `route` (e.g., `/customer/minutes`, `/cleaner/bed-health`, `/manager/courses`)
- `layout` + `role` (controls which Workspace shell renders the blueprint)
- hero copy, nav metadata, and the ordered `sections` to render

`PageBlueprint.vue` handles layout, actions, quick links, and renders each section via composable components (summary cards, insight lists, timelines, action grids, map panels, pricing plans, etc.). Shipping a new surface = add an entry to the registry – no bespoke `.vue` files required.

## Role surfaces

- **Public guest site:** `/`, `/courses`, `/book`, `/membership`, `/locations`, `/shop`, `/status`, `/privacy`, `/terms`, `/track/:token`.
- **Shared pages:** `/me`, `/help`, `/status`, `/privacy`, `/terms`, branded 403/404/500 pages, Lux Concierge widget.
- **Customer portal:** `/customer/dashboard`, `/customer/minutes`, `/customer/courses`, `/customer/bookings`, `/customer/membership`, `/customer/payments`, `/customer/preferences`, `/customer/documents`, `/customer/support`.
- **Glow Guide PWA (cleaner role):** `/cleaner/today`, `/cleaner/clients`, `/cleaner/courses`, `/cleaner/bed-health`, `/cleaner/inbox`, `/cleaner/earnings`, `/cleaner/settings`, `/cleaner/offline`.
- **Studio managers:** `/manager/overview`, `/manager/calendar`, `/manager/waitlist`, `/manager/multiroom`, `/manager/courses`, `/manager/bundles`, `/manager/stock`, `/manager/compliance`, `/manager/customers`, `/manager/membership`, `/manager/marketing`, `/manager/staff`, `/manager/schedules`, `/manager/settlements`, `/manager/settings`.
- **Owners:** `/owner/overview`, `/owner/portfolio`, `/owner/performance`, `/owner/brand`, `/owner/experience`, `/owner/finance`, `/owner/security`, `/owner/integrations`, `/owner/audit`.
- **Accountants:** `/accountant/overview`, `/accountant/payouts`, `/accountant/reconciliation`, `/accountant/fees`, `/accountant/disputes`, `/accountant/export`.
- **Support desk:** `/support/inbox`, `/support/customers`, `/support/studios`, `/support/tools`.
- **Lux platform / hub:** `/glint/tenants`, `/glint/studios`, `/glint/staff`, `/glint/customers`, `/glint/billing`, `/glint/settlements`, `/glint/templates`, `/glint/health`, `/glint/incidents`, `/glint/growth` plus the legacy `/hub/*` console.

The registry drives `resources/js/router-map.js`, so documentation and runtime URLs stay in sync.

## Branding + host configuration

- `config/tenant.php` controls marketing + workspace domains (`https://{studio}.luxtan.app` for tenants, `https://luxtanning.com` for public, `https://hq.luxtan.app` for platform).
- `App\Support\TenantBrandingResolver` inspects the incoming host and injects `tenant` + `branding` props (logos, palette tokens, marketing links, "Powered by" settings) into every Inertia response.
- `WorkspaceLayout` and `PublicLayout` honour those props to display tenant-specific logos, accent colours, and optional “Back to {marketingHost}” / “Powered by Lux” links.
- Guest pages that should still inherit tenant theming simply set `tenantFacing: true` in the registry, and the layout does the rest.

## Authentication + concierge experience

- Auth layouts (`resources/js/Layouts/AuthLayout.vue` + Blade fallbacks) are themed for Lux, include passwordless CTAs, and highlight device security.
- The Lux Concierge widget (`resources/js/Components/GlintConcierge.vue` + Blade fallback) mounts on every page. It talks to `SupportChatController`, which proxies to Gemini with a Lux-specific system prompt.

## Development workflow

1. `composer install`
2. `npm install`
3. `php artisan serve`
4. `npm run dev` (during development) or `npm run build` before pushing/shipping
5. Optional: `php artisan test`

## Conventions

- **Blueprint keys:** `role.section.slug` (`manager.courses`, `customer.minutes`, etc.).
- **Context values:** pass IDs through `context` props when rendering a blueprint so formatter helpers can swap placeholders.
- **Navigation:** update `resources/js/PageRegistry/nav.js` whenever you add/remove pages so WorkspaceLayout menus stay accurate.
- **Docs:** this README is the canonical product+engineering overview — extend it when you add domains, roles, or cross-cutting features.
