import { definePage } from '../registry'
import { summary, insights, dataTable, actionGrid, mapPanel, statusList, splitPanels } from '../helpers'

const defaultGlintSummary = [
  { label: 'Active companies', value: '{{ glint.metrics.active_tenants }}', delta: '{{ glint.metrics.pending_tenants }} awaiting activation' },
  { label: 'Jobs today', value: '{{ glint.metrics.jobs_today }}', delta: '{{ glint.metrics.late_jobs }} late / at-risk' },
  { label: 'Active cleaners', value: '{{ glint.metrics.active_cleaners }}', delta: 'MAU {{ glint.metrics.platform_mau }}' },
  { label: 'Revenue (month)', value: '{{ glint.metrics.revenue_month }}', delta: 'Platform-wide gross' },
]

const createGlintPage = input => ({
  key: input.key,
  route: input.route,
  layout: 'workspace',
  role: 'glint',
  badge: 'Platform control',
  title: input.title,
  description: input.description,
  sections: [
    summary(`${input.key}-summary`, 'Platform signal', input.summaryCards || defaultGlintSummary),
    insights(`${input.key}-insights`, 'Controls & telemetry', input.highlights),
    ...(input.sections || []),
  ],
})

const glintPages = [
  createGlintPage({
    key: 'glint.tenants',
    route: '/glint/tenants',
    title: 'Companies directory',
    description: 'List/search companies; view plan, usage, status.',
    highlights: [
      { title: 'Search + filters', description: 'Filter by plan, health, last activity.' },
      { title: 'Bulk actions', description: 'Suspend, upgrade, or send announcements.' },
      { title: 'Usage meters', description: 'Show API calls, bookings, seats per company.' },
    ],
    sections: [
      dataTable('glint-tenants-table', 'Companies', [
        { label: 'Company', key: 'company' },
        { label: 'Plan', key: 'plan' },
        { label: 'Status', key: 'status' },
        { label: 'Customers', key: 'customers', align: 'center' },
        { label: 'Staff', key: 'staff', align: 'center' },
        { label: 'Business details', key: 'business' },
        { label: 'Jobs', key: 'jobs', align: 'right' },
      ], [], null, null, { rowsKey: 'glint.tenants.table' }),
    ],
  }),
  createGlintPage({
    key: 'glint.customers',
    route: '/glint/customers',
    title: 'Customers overview',
    description: 'See customer reach per company, along with staffing and workload context.',
    highlights: [
      { title: 'Customer reach', description: 'Track how many customers each company is serving.' },
      { title: 'Cross-check staffing', description: 'Compare customer counts to staff levels.' },
      { title: 'Business context', description: 'Surface domains, regions, and VAT schemes for audits.' },
    ],
    sections: [
      dataTable('glint-customers-table', 'Recent customers', [
        { label: 'Customer', key: 'customer' },
        { label: 'Email', key: 'email' },
        { label: 'Phone', key: 'phone' },
        { label: 'Company', key: 'company' },
        { label: 'Tags', key: 'tags' },
        { label: 'Joined', key: 'joined' },
      ], [], null, null, { rowsKey: 'glint.customers.table' }),
    ],
  }),
  createGlintPage({
    key: 'glint.staff',
    route: '/glint/staff',
    title: 'Staffing overview',
    description: 'Audit staffing levels per company to match workload and compliance requirements.',
    highlights: [
      { title: 'Staff coverage', description: 'Quickly gauge staffing versus job volume.' },
      { title: 'Customer ratio', description: 'Watch customers-per-staff for service quality.' },
      { title: 'Plan insights', description: 'Confirm staffing aligns with contracted plan tiers.' },
    ],
    sections: [
      dataTable('glint-staff-table', 'People directory', [
        { label: 'Name', key: 'name' },
        { label: 'Email', key: 'email' },
        { label: 'Role', key: 'role' },
        { label: 'Company', key: 'company' },
        { label: 'Status', key: 'status' },
        { label: 'Jobs', key: 'jobs', align: 'right' },
      ], [], null, null, { rowsKey: 'glint.staff.table' }),
    ],
  }),
  createGlintPage({
    key: 'glint.platform-overview',
    route: '/glint/platform',
    title: 'Platform overview',
    description: 'Cross-company KPIs, incidents, feature rollout status.',
    highlights: [
      { title: 'Companies by health', description: 'Segment by healthy, watchlist, critical.' },
      { title: 'Incidents feed', description: 'Active + recent incidents across all companies.' },
      { title: 'Rollout tracker', description: 'Feature flag adoption by company.' },
    ],
    sections: [
      dataTable('glint-platform-jobs', 'Live jobs (next 72h)', [
        { label: 'Job', key: 'job' },
        { label: 'Company', key: 'tenant' },
        { label: 'When', key: 'when' },
        { label: 'Status', key: 'status' },
      ], [], 'All active work across companies.', null, { rowsKey: 'glint.jobs.table' }),
      mapPanel('glint-platform-map', 'Live map', [], 'Plot of active jobs by company.', 7, { markersKey: 'glint.jobs.markers' }),
      statusList('glint-platform-queues', 'Operational queues', [], 'Dispatch, billing, comms and webhooks.', { itemsKey: 'glint.queues' }),
    ],
  }),
  createGlintPage({
    key: 'glint.tenants.impersonate',
    route: '/glint/tenants/:id/impersonate',
    title: 'Tenant impersonation',
    description: 'Secure assume-role with approvals + logging.',
    highlights: [
      { title: 'Approval chain', description: 'Require second approver for entering owner consoles.' },
      { title: 'Time-boxed sessions', description: 'Sessions expire after 15 minutes.' },
      { title: 'Audit trail', description: 'Logs before/after actions with ticket references.' },
    ],
  }),
  createGlintPage({
    key: 'glint.tenants.overview',
    route: '/glint/tenants/:id/overview',
    title: 'Tenant overview',
    description: 'KPIs, incidents, webhook health per tenant.',
    highlights: [
      { title: 'Webhook panel', description: 'Success rate, DLQs, retry stats.' },
      { title: 'Incident timeline', description: 'Tenant-specific outages or escalations.' },
      { title: 'Plan usage', description: 'Seats used, API calls, add-on consumption.' },
    ],
  }),
  createGlintPage({
    key: 'glint.health.queues',
    route: '/glint/health/queues',
    title: 'Queues health',
    description: 'Job queues, lag, retries, DLQs.',
    highlights: [
      { title: 'Lag meters', description: 'Visualise queue latency vs. SLA.' },
      { title: 'Retry controls', description: 'Replay or purge stuck jobs.' },
      { title: 'Tenant drill-down', description: 'See queue usage per tenant.' },
    ],
    sections: [
      statusList('glint-queues-status', 'Key queues', [], 'Live queue signal.', { itemsKey: 'glint.queues' }),
    ],
  }),
  createGlintPage({
    key: 'glint.health.webhooks',
    route: '/glint/health/webhooks',
    title: 'Webhooks',
    description: 'Delivery success, DLQs, per-tenant metrics.',
    highlights: [
      { title: 'Success rate', description: 'Graphs by tenant + event type.' },
      { title: 'DLQ viewer', description: 'Inspect payloads stuck in dead-letter queue.' },
      { title: 'Replay tools', description: 'Select events and replay with new endpoints.' },
    ],
  }),
  createGlintPage({
    key: 'glint.health.services',
    route: '/glint/health/services',
    title: 'Services uptime',
    description: 'Service-by-service uptime, errors, release version.',
    highlights: [
      { title: 'Service cards', description: 'Show version, uptime %, open incidents.' },
      { title: 'Error budgets', description: 'Track SLO burn-downs for each service.' },
      { title: 'Release feed', description: 'Latest deploy, commit, author.' },
    ],
  }),
  createGlintPage({
    key: 'glint.billing.plans',
    route: '/glint/billing/plans',
    title: 'Plans',
    description: 'Manage tenant plans, features, limits.',
    highlights: [
      { title: 'Plan matrix', description: 'Define features per plan tier.' },
      { title: 'Usage caps', description: 'Set booking/API limits per plan.' },
      { title: 'Upgrade offers', description: 'Send upgrade prompts to tenants hitting limits.' },
    ],
  }),
  createGlintPage({
    key: 'glint.billing.fees',
    route: '/glint/billing/fees',
    title: 'Fee schedules',
    description: 'Platform fee schedules.',
    highlights: [
      { title: 'Tiered fees', description: 'Configure per-tenant or global fee rules.' },
      { title: 'Adjustments', description: 'Create promotional fee holidays.' },
      { title: 'Reporting', description: 'See fees collected vs. waived.' },
    ],
  }),
  createGlintPage({
    key: 'glint.billing.settlements',
    route: '/glint/billing/settlements',
    title: 'Settlements',
    description: 'Marketplace payouts / rev-share view.',
    highlights: [
      { title: 'Batch settlement', description: 'Process rev-share payouts per tenant.' },
      { title: 'Audit trails', description: 'Link settlement to ledger entries.' },
      { title: 'Exceptions', description: 'Handle disputes or withheld payouts.' },
    ],
  }),
  createGlintPage({
    key: 'glint.audit',
    route: '/glint/audit',
    title: 'Cross-tenant audit',
    description: 'Search audit logs across tenants.',
    highlights: [
      { title: 'Cross-tenant search', description: 'Query by email, IP, action type.' },
      { title: 'Export to SIEM', description: 'Streaming exports to SOC tools.' },
      { title: 'Alerting', description: 'Set alerts for sensitive actions (role changes, mass exports).' },
    ],
  }),
  createGlintPage({
    key: 'glint.gdpr.sar',
    route: '/glint/gdpr/sar',
    title: 'Subject access requests',
    description: 'Manage SAR workflows for tenants.',
    highlights: [
      { title: 'Workflow tracker', description: 'Intake, assign, respond statuses.' },
      { title: 'Data export', description: 'Generate redactable packages.' },
      { title: 'Deadline monitor', description: 'Remind teams about 30-day SLA.' },
    ],
  }),
  createGlintPage({
    key: 'glint.data-exports',
    route: '/glint/data-exports',
    title: 'Data exports',
    description: 'Ad-hoc exports (secure) for tenants or regulators.',
    highlights: [
      { title: 'Export templates', description: 'Prebuilt sets for jobs, customers, or telemetry.' },
      { title: 'Delivery options', description: 'Secure link, S3, or API push.' },
      { title: 'Access control', description: 'Requires owner approval per request.' },
    ],
  }),
  createGlintPage({
    key: 'glint.retention',
    route: '/glint/retention',
    title: 'Global retention',
    description: 'Define global defaults; enforce tenant compliance.',
    highlights: [
      { title: 'Policy tiers', description: 'Set base retention by plan.' },
      { title: 'Compliance view', description: 'See which tenants deviate.' },
      { title: 'Auto enforcement', description: 'Purge data when TTL hits.' },
    ],
  }),
  createGlintPage({
    key: 'glint.feature-flags',
    route: '/glint/feature-flags',
    title: 'Feature flags',
    description: 'Rollout controls per tenant or cohort.',
    highlights: [
      { title: 'Targeting rules', description: 'Enable by plan, region, or tenant ID.' },
      { title: 'Gradual rollout', description: 'Percentage-based rollouts with kill switch.' },
      { title: 'Metrics tie-in', description: 'Track usage/impact per flag.' },
    ],
  }),
  createGlintPage({
    key: 'glint.templates',
    route: '/glint/templates',
    title: 'Global templates',
    description: 'Email/SMS templates for all tenants.',
    highlights: [
      { title: 'Template library', description: 'Global defaults tenant can override.' },
      { title: 'Localization', description: 'Multi-language support with fallback.' },
      { title: 'Testing', description: 'Send test messages to sample tenants.' },
    ],
  }),
  createGlintPage({
    key: 'glint.checklists',
    route: '/glint/checklists',
    title: 'Checklist library',
    description: 'Global checklist templates.',
    highlights: [
      { title: 'Blueprints', description: 'Provide base templates for tenants to clone.' },
      { title: 'Version control', description: 'Push updates while tracking tenant overrides.' },
      { title: 'Best-practice tags', description: 'Label templates by service type/market.' },
    ],
  }),
  createGlintPage({
    key: 'glint.maps',
    route: '/glint/maps',
    title: 'Maps & routing',
    description: 'Manage routing provider keys/quotas.',
    highlights: [
      { title: 'Key management', description: 'Rotate Google/Mapbox keys across tenants.' },
      { title: 'Quota monitor', description: 'Watch usage vs. quotas, alert when high.' },
      { title: 'Fallback routing', description: 'Switch provider per tenant region.' },
    ],
    sections: [
      mapPanel('glint-maps-preview', 'Global coverage', [
        { title: 'EU cluster', detail: 'Quotas 65%', lat: 48.8566, lng: 2.3522, state: 'info' },
        { title: 'NA cluster', detail: 'Quotas 48%', lat: 40.7128, lng: -74.006, state: 'success' },
      ]),
    ],
  }),
  createGlintPage({
    key: 'glint.security',
    route: '/glint/security',
    title: 'Security',
    description: 'Key rotation, SCIM/SAML connections.',
    highlights: [
      { title: 'Key rotation', description: 'Rotate API + webhook secrets platform-wide.' },
      { title: 'SAML/SCIM', description: 'Provision enterprise SSO for large tenants.' },
      { title: 'Pen-test status', description: 'Track outstanding security reviews.' },
    ],
  }),
  createGlintPage({
    key: 'glint.abuse',
    route: '/glint/abuse',
    title: 'Abuse desk',
    description: 'Rate limits, IP blocks, fraud detection.',
    highlights: [
      { title: 'Rate limit editor', description: 'Adjust booking or API limits quickly.' },
      { title: 'IP blocklist', description: 'Manage shared blocklist for spam/fraud.' },
      { title: 'Alerts', description: 'Trigger alerts for suspicious booking patterns.' },
    ],
  }),
  createGlintPage({
    key: 'glint.logs',
    route: '/glint/logs',
    title: 'Logs',
    description: 'Platform logs viewer (PII-safe).',
    highlights: [
      { title: 'PII redaction', description: 'Sensitive fields masked by default.' },
      { title: 'Filter builder', description: 'Filter by tenant, route, correlation ID.' },
      { title: 'Live tail', description: 'Follow logs during incidents.' },
    ],
  }),
  createGlintPage({
    key: 'glint.metrics',
    route: '/glint/metrics',
    title: 'Metrics',
    description: 'MAU, orders/day, ETA MAE, push delivery, etc.',
    highlights: [
      { title: 'Ops KPIs', description: 'Cross-tenant on-time, completion, cancellations.' },
      { title: 'Engagement', description: 'MAU per role, daily actives, retention.' },
      { title: 'Notification delivery', description: 'Push/SMS/email success vs. fail.' },
    ],
  }),
  createGlintPage({
    key: 'glint.incidents',
    route: '/glint/incidents',
    title: 'Incidents',
    description: 'Incident runbooks, postmortems, status sync.',
    highlights: [
      { title: 'Runbook templates', description: 'Checklist for each incident type.' },
      { title: 'Comms centre', description: 'Update status page + customer comms from one view.' },
      { title: 'Postmortems', description: 'Store findings and follow-up tasks.' },
    ],
  }),
  createGlintPage({
    key: 'glint.cms',
    route: '/glint/cms',
    title: 'Content hub',
    description: 'Marketing pages, FAQs, release notes.',
    highlights: [
      { title: 'Content collections', description: 'Manage marketing site and in-app copy.' },
      { title: 'Release notes', description: 'Publish cross-tenant release notes.' },
      { title: 'Preview links', description: 'Share previews before publishing.' },
    ],
  }),
]

export function registerGlintPages() {
  glintPages.forEach(page => definePage(page))
}
