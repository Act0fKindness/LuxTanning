# Glint Labs — Platform Overview

Your Brand. Your Portal. Powered by Glint.

## Mission

Glint Labs™ powers local service businesses (starting with window cleaners) to run smarter, faster, and cashless under their own brand. It automates bookings, payments, ETAs, communication, and upsells — while Glint earns a small platform fee per transaction.

## Multi‑Tenant Architecture

- Companies: Stored as `tenants` (aka companies). Branded domains, pricing, and automation per company.
- Users: Single table for all identities: platform admins, owners, managers, cleaners, accountants, and customers. Users can belong to one or more tenants via the `tenant_user` membership pivot.
- Memberships: `tenant_user` holds the tenant-scoped role per user and enforces exactly one owner per tenant.
- Profiles: `user_profiles` is a 1:1 extension of users for attributes like default address and marketing preferences.
- Addresses: Customer addresses (`addresses`) belong to a user and tenant.
- Jobs & Routes: Jobs (`jobs`) link to a tenant and the assigned staff user. Optional booking/subscription linkage for recurring work.
- Finance: Payments, invoices, payouts, fees, and disputes tracked per tenant.
- Communications: Provider‑agnostic messages (SMS/email) with simple delivery state.
- Tracking: Live job sessions + GPS pings for customer ETA pages.

## Branding & Subdomains

- Base domain comes from `config/tenant.php`. Every tenant automatically inherits `{tenantSlug}.glintlabs.com` for all public guest flows, customer portals, and staff workspaces while `admin.glintlabs.com` remains Glint HQ.
- `theme_json.branding` stores company name overrides, marketing/back-to-site link, logos (full + icon), palette tokens, powered-by preferences, and font selections. Those values power both the Laravel email themes and the Vue layouts.
- `App\Support\TenantBrandingResolver` resolves the current tenant from the host or logged-in membership and shares `tenant` + `branding` props with every Inertia response.
- Layouts consume that data to swap logos, recolour CTA buttons, and optionally render “Back to windowcleaners.com” + “Powered by Glint” links. No extra per-page wiring is required.
- `resources/js/PageRegistry` now marks each page with a host + branding descriptor (plus `tenantFacing` override) and exports the canonical URL pattern to `router-map.js`.

## Roles

- platform_admin: Glint HQ operations, all tenants.
- owner: One per company; full control within tenant.
- manager: Schedules, jobs, customers, payouts, staff.
- accountant: Payments, invoices, exports.
- cleaner: Field staff; mobile app for jobs and earnings.
- customer: End customer; portal for subscription, visits, and payments.

DB enforcement (MySQL/PG) on `tenant_user` ensures at most one `owner` per company.

## Key Data Model

- users (id, name, email, phone, role, password, …)
- tenant_user (id, tenant_id, user_id, role, status)
- user_profiles (id, user_id unique, default_address_id?, marketing_opt_in, tags, notes, avatar_url)
- tenants (id, name, slug, domain, country, status, theme_json, fee_tier, vat_scheme)
- addresses (id, tenant_id, user_id, line1, line2?, city?, county?, postcode, lat?, lng?, access_notes?, door_code?)
- jobs (id, tenant_id, staff_user_id?, date, eta_window?, status, sequence?, checklist_json, required_photos?, no_access_fee_pence)
- payments (id, tenant_id, job_id?, invoice_id?, method, amount_pence, application_fee_pence, processor_fee_pence, stripe_charge_id?, status, attempts, last_error?)
- invoices (id, tenant_id, user_id, number, totals_json, pdf_url?, issued_at?, paid_at?, status)
- subscriptions (id, tenant_id, user_id, address_id, cadence, next_due_at?, payment_method, status, risk_score?)
- bookings (id, tenant_id, user_id, address_id, status, channel, source?, quote_json, deposit_pence, tcs_accepted_at?)
- job_photos, tracking_sessions, location_pings, messages, disputes, payouts, routes, areas, vehicles, exports, settings, fees_config, audit_logs

## Request Flow (Example)

1. Customer books: creates booking + optional deposit PaymentIntent; user is role `customer` scoped to tenant.
2. Ops assign job: `jobs.staff_user_id` links to a role `cleaner` user.
3. Staff completes job: checklist_json updated; before/after photos optional; charge on completion.
4. Payments & invoicing: invoice created and paid; Glint fee recorded; payouts aggregated for tenant.
5. Comms: SMS/email for on-the-way, receipts, and review/up-sell nudges.
6. Tracking: real-time location via `tracking_sessions` and `location_pings` for live ETA page.

## Security & Authorization

- All app users authenticate via `users`.
- Server-side: tenant scoping on queries; role-based checks in controllers (and ready for Gates/Policies).
- DB constraint ensures single owner per tenant.

## Extensibility

- Add services by extending `price_matrices`, `checklist_json`, and job generation.
- Stripe Connect and messaging providers integrated via `webhook_events`.
- Future modules: AI scheduler, forecasting, marketplace, and SDKs.

## Glossary

- Company/Tenant: A customer business running Glint.
- Customer: End client of a tenant; represented as a `users` row with role `customer`.
- Staff: Users with roles `owner|manager|cleaner|accountant`.
