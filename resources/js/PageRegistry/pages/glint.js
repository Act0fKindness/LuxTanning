import { definePage } from '../registry'
import { summary, dataTable, actionGrid, insights, statusList, checklist } from '../helpers'

const platformSummary = [
  { label: 'Tenants live', value: '214', delta: '+6 this week' },
  { label: 'Studios', value: '612', delta: '+28 adding Lux OS' },
  { label: 'Incidents', value: '0 open', delta: 'Last 34 days' },
]

const createPlatformPage = spec => ({
  key: spec.key,
  route: spec.route,
  layout: 'workspace',
  role: 'glint',
  badge: 'Lux Platform',
  title: spec.title,
  description: spec.description,
  sections: [
    summary(`${spec.key}-summary`, 'Platform pulse', spec.summaryCards || platformSummary),
    ...(spec.sections || []),
  ],
})

const glintPages = [
  createPlatformPage({
    key: 'glint.tenants',
    route: '/glint/tenants',
    title: 'Tenant directory',
    description: 'Monitor every Lux franchise, subdomain, and branding profile.',
    sections: [
      dataTable('glint-tenants-table', 'Recent tenants', [
        { label: 'Tenant', key: 'tenant' },
        { label: 'Studios', key: 'studios', align: 'right' },
        { label: 'Plan', key: 'plan' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { tenant: 'Glow & Co.', studios: '5', plan: 'Scale', status: 'Active' },
        { tenant: 'Aurora Labs', studios: '2', plan: 'Growth', status: 'Trial' },
      ]),
      actionGrid('glint-tenants-actions', 'Tenant ops', [
        { label: 'Impersonate', description: 'Jump into any workspace with audit log.', icon: 'bi-person-bounding-box' },
        { label: 'Branding sync', description: 'Push palette/logos to kiosks + email.', icon: 'bi-brush' },
        { label: 'Suspend', description: 'Freeze tenant for billing/compliance issues.', icon: 'bi-lock' },
      ]),
    ],
  }),
  createPlatformPage({
    key: 'glint.studios',
    route: '/glint/studios',
    title: 'Studio telemetry',
    description: 'Cross-tenant lamp health, occupancy, and incidents.',
    sections: [
      statusList('glint-studios-status', 'Studios status', [
        { label: 'Lux-owned', value: '14', hint: 'All green', state: 'success' },
        { label: 'Franchise', value: '67', hint: '3 with alerts', state: 'warning' },
        { label: 'Independent', value: '133', hint: 'Pending onboarding', state: 'info' },
      ]),
      actionGrid('glint-studios-actions', 'Platform tools', [
        { label: 'Lamp analytics', description: 'Trend lamp hours + replacements globally.', icon: 'bi-activity' },
        { label: 'Dispatch global notice', description: 'Send system alerts to specific geos.', icon: 'bi-megaphone' },
        { label: 'QoS sampling', description: 'Select studios for random QA + video review.', icon: 'bi-camera-video' },
      ]),
    ],
  }),
  createPlatformPage({
    key: 'glint.staff',
    route: '/glint/staff',
    title: 'Staff accounts',
    description: 'Provision platform staff, enforce roles, and review activity.',
    sections: [
      dataTable('glint-staff-table', 'Platform staff', [
        { label: 'Name', key: 'name' },
        { label: 'Role', key: 'role' },
        { label: 'Last active', key: 'lastActive' },
        { label: 'MFA', key: 'mfa', align: 'right' },
      ], [
        { name: 'Nora Quinn', role: 'Platform admin', lastActive: '2m ago', mfa: 'TOTP' },
        { name: 'Dylan Cho', role: 'Tenant ops', lastActive: '10m ago', mfa: 'U2F' },
      ]),
      checklist('glint-staff-checklist', 'Access hygiene', [
        { label: 'Quarterly review', detail: 'Auto-remind managers to re-certify.' },
        { label: 'Just-in-time access', detail: 'Time-box escalated roles.' },
      ]),
    ],
  }),
  createPlatformPage({
    key: 'glint.customers',
    route: '/glint/customers',
    title: 'Global customers',
    description: 'Search members across tenants, honor GDPR, and run insights.',
    sections: [
      dataTable('glint-customers-table', 'Recent SARs', [
        { label: 'Requestor', key: 'name' },
        { label: 'Tenant', key: 'tenant' },
        { label: 'Status', key: 'status' },
        { label: 'Due', key: 'due' },
      ], [
        { name: 'Maya Kapoor', tenant: 'Glow & Co', status: 'Processing', due: '13 May' },
        { name: 'Evan Li', tenant: 'Aurora Labs', status: 'Exported', due: 'Complete' },
      ]),
      actionGrid('glint-customers-actions', 'Controls', [
        { label: 'Blacklist', description: 'Block abusive users at platform level.', icon: 'bi-shield-lock' },
        { label: 'Merge identities', description: 'Resolve duplicates across tenants.', icon: 'bi-intersect' },
        { label: 'Share concierge notes', description: 'Allow cross-tenant context with consent.', icon: 'bi-card-text' },
      ]),
    ],
  }),
  createPlatformPage({
    key: 'glint.billing',
    route: '/glint/billing',
    title: 'Plans & billing',
    description: 'Manage Lux OS plan tiers, usage metering, and invoices.',
    sections: [
      dataTable('glint-billing-plans', 'Plans', [
        { label: 'Plan', key: 'plan' },
        { label: 'Base', key: 'base', align: 'right' },
        { label: 'Usage', key: 'usage' },
      ], [
        { plan: 'Starter', base: '£0', usage: '£1 / booking' },
        { plan: 'Growth', base: '£249', usage: '0.6% payments' },
        { plan: 'Scale', base: '£Custom', usage: 'Blended' },
      ]),
      actionGrid('glint-billing-actions', 'Billing ops', [
        { label: 'Change plan', description: 'Upgrade/downgrade tenant plan in seconds.', icon: 'bi-arrow-repeat' },
        { label: 'Apply credit', description: 'Account-level credits for incidents.', icon: 'bi-cash-coin' },
        { label: 'Send invoice', description: 'Issue PDF + email with taxes auto-calculated.', icon: 'bi-receipt' },
      ]),
    ],
  }),
  createPlatformPage({
    key: 'glint.settlements',
    route: '/glint/settlements',
    title: 'Settlements & fees',
    description: 'Track platform rev share, Stripe Connect movements, and partner payouts.',
    sections: [
      dataTable('glint-settlement-table', 'Latest settlements', [
        { label: 'Tenant', key: 'tenant' },
        { label: 'Amount', key: 'amount', align: 'right' },
        { label: 'Fees', key: 'fees', align: 'right' },
        { label: 'Status', key: 'status' },
      ], [
        { tenant: 'Glow & Co', amount: '£62k', fees: '£1.8k', status: 'Paid' },
        { tenant: 'Aurora Labs', amount: '£14k', fees: '£420', status: 'Pending' },
      ]),
      checklist('glint-settlement-checks', 'Controls', [
        { label: 'FX hedging', detail: 'Auto apply for USD/EUR payouts.' },
        { label: 'Reserve ratios', detail: 'Warn if tenant dips below policy.' },
      ]),
    ],
  }),
  createPlatformPage({
    key: 'glint.templates',
    route: '/glint/templates',
    title: 'Templates & playbooks',
    description: 'Share course blueprints, automations, and rituals to all tenants.',
    sections: [
      insights('glint-templates-list', 'Popular templates', [
        { title: 'Glow Pro launch kit', description: 'Web, kiosk, CRM, and staff scripts pre-loaded.' },
        { title: 'Lamp maintenance ritual', description: 'Checklist + IoT logic for lamp health.' },
        { title: 'Creator residency', description: 'Referral flows, payouts, contracts.' },
      ]),
      actionGrid('glint-templates-actions', 'Manage templates', [
        { label: 'Publish to tenants', description: 'Send recommended updates to chosen groups.', icon: 'bi-broadcast' },
        { label: 'Version control', description: 'Track changes + rollbacks.', icon: 'bi-clock-history' },
        { label: 'Localization', description: 'Translate + adapt to each market.', icon: 'bi-translate' },
      ]),
    ],
  }),
  createPlatformPage({
    key: 'glint.health',
    route: '/glint/health',
    title: 'Platform health',
    description: 'Queues, workers, APIs, and client SDK versions.',
    sections: [
      statusList('glint-health-components', 'Components', [
        { label: 'API', value: 'Operational', hint: 'p95 220ms', state: 'success' },
        { label: 'Queues', value: 'Elevated', hint: 'Lamp telemetry jobs 6m back', state: 'warning' },
        { label: 'Notifications', value: 'Partial', hint: 'SMS backlog in EU', state: 'danger' },
      ]),
      actionGrid('glint-health-actions', 'Ops actions', [
        { label: 'Scale workers', description: 'Spin up containers automatically.', icon: 'bi-cpu' },
        { label: 'Failover SMS', description: 'Switch to backup provider.', icon: 'bi-arrow-repeat' },
        { label: 'Notify tenants', description: 'Push status page update.', icon: 'bi-activity' },
      ]),
    ],
  }),
  createPlatformPage({
    key: 'glint.incidents',
    route: '/glint/incidents',
    title: 'Incidents',
    description: 'Declare, triage, and resolve incidents with postmortems.',
    sections: [
      dataTable('glint-incidents-table', 'Active incidents', [
        { label: 'Incident', key: 'incident' },
        { label: 'Severity', key: 'severity' },
        { label: 'Owner', key: 'owner' },
        { label: 'Status', key: 'status' },
      ], [
        { incident: 'INC-982', severity: 'SEV2', owner: 'Nora', status: 'Investigating SMS' },
      ]),
      checklist('glint-incidents-playbook', 'Response playbook', [
        { label: 'Declare + page', detail: 'Slash command or UI button alerts on-call.' },
        { label: 'Tenant comms', detail: 'Auto-publish status + targeted notifications.' },
        { label: 'Postmortem', detail: 'Template ensures root cause + actions captured.' },
      ]),
    ],
  }),
  createPlatformPage({
    key: 'glint.growth',
    route: '/glint/growth',
    title: 'Growth & ecosystem',
    description: 'Track leads, partners, and Lux Labs experiments.',
    sections: [
      dataTable('glint-growth-pipeline', 'Pipeline', [
        { label: 'Prospect', key: 'prospect' },
        { label: 'Stage', key: 'stage' },
        { label: 'Studios', key: 'studios', align: 'right' },
        { label: 'Owner', key: 'owner' },
      ], [
        { prospect: 'Radiant Labs', stage: 'Demo complete', studios: '4', owner: 'Dylan' },
        { prospect: 'Solar Den', stage: 'Proposal sent', studios: '3', owner: 'Amelia' },
      ]),
      insights('glint-growth-initiatives', 'Initiatives', [
        { title: 'Creator marketplace', description: 'Pair studios with creators for pop-ups.' },
        { title: 'Wellness bundles', description: 'Partner with recovery studios + wellness apps.' },
      ]),
    ],
  }),
]

export function registerGlintPages() {
  glintPages.forEach(page => definePage(page))
}
