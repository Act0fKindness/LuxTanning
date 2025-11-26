# Glint Labs Platform

> **Required:** after every change run `npm install && npm run build` to keep the Vue + Laravel bundles in sync.

Glint Labs is a Laravel 10 + Inertia + Vue 3 stack that now ships a fully described information architecture for every role in the business (public guest flows, customers, cleaners, managers, owners, accountants, support, and Glint platform admins). The backend exposes thin blueprint routes, while the frontend uses a registry-driven system so designers and engineers can iterate on page content without chasing scattered single-file components.

## Stack quickstart

- **Backend:** Laravel with Sanctum auth, standard `php artisan serve` or Valet.
- **Frontend:** Vue 3 compiled with Laravel Mix. Run `npm run dev` for watch mode, `npm run build`/`npm run production` for deploy bundles.
- **Authentication:** Laravel auth forms (newly redesigned) for operators + passwordless `/auth/magic-link` + `/auth/verify` flows for every role.
- **Mapping:** Google Maps key pulled from `.env` via `config('services.google.maps_key')` and consumed by the new `MapPanel` component.

## Frontend architecture

```
resources/js
├── Components/Blueprint      # Generic section renderers (cards, tables, timelines, map, etc.)
├── Layouts                  # Public, Auth, and Workspace shells
├── PageRegistry             # Nav configs, helpers, per-role page specs
├── Pages
│   ├── Auth/Public/Shared   # Hosts for public + auth blueprints
│   ├── Customer/.../Glint  # Thin wrappers that feed role + pageKey into WorkspacePage
│   └── Workspaces          # WorkspacePage wiring layout + PageBlueprint
└── router-map.js          # Auto-generated from the registry for developer reference
```

Every page is defined once inside `resources/js/PageRegistry/pages/*.js`. A page spec records:

- `route` (`/manager/dispatch/board`, `/cleaner/today`, etc.)
- `layout` + `role` (determines layout + required auth role)
- `actions`, `quickLinks`, and ordered `sections`

`PageBlueprint.vue` renders those sections using a small set of opinionated components (summary cards, insight lists, tables, timelines, kanban boards, action grids, split panels, checklists, and Google Maps panels). Adding a new page = drop a spec entry, no extra `.vue` files.

## Routing + roles

Laravel now registers blueprint routes via a helper (`registerBlueprintRoutes`) so each role-specific shell only needs `pageKey` + optional context IDs. All internal surfaces sit behind `auth` middleware. Public/guest experiences live under the `Public/PageShell`. Highlights:

- **Shared system:** `/auth/magic-link`, `/auth/verify`, `/me`, `/help`, `/status`, `/privacy`, `/terms`, plus branded 403/404/500 screens.
- **Public guest:** `/`, `/book`, `/checkout`, `/booking/confirmed/:sessionId`, `/find`, `/manage/:token`, `/track/:trackingId`, `/receipt/:id`.
- **Customers:** `/customer/*` (dashboard, cleans, addresses, billing, invoices, preferences, security, support, live tracking).
- **Cleaners (PWA):** `/cleaner/today`, job lifecycle routes, history, earnings, inbox, settings, offline sync.
- **Managers:** Full dispatch/route builder, jobs CRUD, live map/timeline, customers/subscriptions, staff/shifts/announcements, refunds/adjustments, reports, settings.
- **Owners:** Overview, finance, roles/RBAC, branding, pricing, domains, API keys, policies, integrations, audit/data retention.
- **Accountants:** Invoices, payments, payouts, taxes, adjustments, disputes, exports.
- **Support:** Tickets inbox, customer/job quick actions, tooling (`/support/tools`).
- **Glint platform:** Tenants directory + impersonation, platform health, billing plans/fees/settlements, compliance (audit, SAR, retention), feature flags/templates/checklists/maps, security/abuse, observability (logs, metrics, incidents, CMS).

The developer-facing route map (`resources/js/router-map.js`) now derives from the registry so documentation and runtime always match.

## White-label hosts + branding

- `config/tenant.php` defines the base domain for tenant apps (`TENANT_BASE_DOMAIN`), marketing domain defaults, ignored subdomains, fallback logos, palette, and the "Powered by Glint" footer toggle.
- `App\Support\TenantBrandingResolver` inspects the request host → tenant slug → `tenants.theme_json.branding` to share branding + URL context with Inertia. Every request exposes `tenant` + `branding` props containing logo/icon, palette tokens, fonts, marketing URL, and `back_to_site_url`.
- `WorkspaceLayout` + `PublicLayout` now read that context to swap logos, recolour CTA buttons, and surface the optional “Back to {marketingHost}” + “Powered by Glint” links whenever the tenant config enables them.
- The page registry automatically annotates every page with a `host` (`tenant`, `marketing`, or `glint`), concrete `url` (e.g. `https://{tenant}.glintlabs.com/customer/dashboard`), and a `branding` descriptor. Guest pages that should still inherit tenant branding (booking, auth, error pages, /help, etc.) simply set `tenantFacing: true`.
- `resources/js/router-map.js` now exports that metadata so docs/tooling can see the canonical host + branding treatment for each surface.

Global white-label expectations:

1. Every company tenant gets `https://{tenantSlug}.glintlabs.com` for customer, cleaner, manager, owner, accountant, and support workspaces plus guest flows such as `/book`, `/find`, `/track`, `/receipt`, `/help`, and `/status`.
2. Their marketing URL/back-link (usually `https://www.{tenant}.com`) is stored in `theme_json.branding.back_to_site_url` and displayed automatically in the new layout footer/sidebar links.
3. Logos + palette + font choices live in `theme_json.branding.*` so all mailers/pdfs/UI shells reuse tenants’ visual language while keeping a tiny optional “Powered by Glint” attribution.

## Authentication UI refresh

`resources/views/auth/login.blade.php` and `register.blade.php` were rebuilt to match the new platform story: split layouts, clear copy about magic links + role coverage, and consistent styling without external Tailwind scripts. The Laravel auth scaffolding remains (`Auth::routes()`), so existing login flows keep working while the new `/auth/*` pages handle passwordless experiences.

## Development workflow

1. Install dependencies: `composer install` + `npm install`.
2. Run Laravel: `php artisan serve` (or your preferred stack).
3. Frontend dev server: `npm run dev` for hot reload.
4. Ship builds: `npm run build` (alias for Mix production) **after every change**.
5. Optional: `php artisan test` for backend coverage.

## Conventions recap

- **Page naming:** `role.section.slug` (e.g., `manager.dispatch.board`).
- **Contexts:** Pass IDs through the `context` prop (e.g., `jobId`, `tenantId`) so blueprint sections can surface dynamic text via the shared formatter.
- **Navigation:** Role menus live in `resources/js/PageRegistry/nav.ts` and feed `WorkspaceLayout` to keep the UI consistent.
- **Docs:** Update this README whenever you add roles/pages so product + engineering share one source of truth.
