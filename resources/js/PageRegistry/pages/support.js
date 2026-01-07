import { definePage } from '../registry'
import { summary, dataTable, actionGrid, insights, statusList } from '../helpers'

const supportSummary = [
  { label: 'Open tickets', value: '42', delta: '12 waiting on guest' },
  { label: 'Avg response', value: '3m 42s', delta: 'Goal <5m' },
  { label: 'CSAT', value: '4.92/5', delta: 'Past 7 days' },
]

const createSupportPage = spec => ({
  key: spec.key,
  route: spec.route,
  layout: 'workspace',
  role: 'support',
  badge: 'Concierge desk',
  title: spec.title,
  description: spec.description,
  sections: [
    summary(`${spec.key}-summary`, 'Desk pulse', spec.summaryCards || supportSummary),
    ...(spec.sections || []),
  ],
})

const supportPages = [
  createSupportPage({
    key: 'support.inbox',
    route: '/support/inbox',
    title: 'Omnichannel inbox',
    description: 'Email, SMS, chat, and Instagram DMs unified with Lux context.',
    sections: [
      dataTable('support-inbox-table', 'Queues', [
        { label: 'Queue', key: 'queue' },
        { label: 'Waiting', key: 'waiting', align: 'right' },
        { label: 'SLA', key: 'sla' },
        { label: 'Owner', key: 'owner' },
      ], [
        { queue: 'Members · Wallet', waiting: '9', sla: '<5m', owner: 'Team Amber' },
        { queue: 'Studios · Ops', waiting: '3', sla: '<10m', owner: 'Team Drift' },
        { queue: 'Partners', waiting: '2', sla: '<30m', owner: 'Priya' },
      ]),
      actionGrid('support-inbox-actions', 'Shortcuts', [
        { label: 'Macros', description: 'Pre-built replies with wallet + lamp data.', icon: 'bi-lightning' },
        { label: 'Escalate to manager', description: 'Push issue straight into /manager/inbox.', icon: 'bi-arrow-up-right' },
        { label: 'Trigger refund', description: 'Start credit workflow with proper guardrails.', icon: 'bi-cash-stack' },
      ]),
    ],
  }),
  createSupportPage({
    key: 'support.customers',
    route: '/support/customers',
    title: 'Customer lookup',
    description: 'Search by email, phone, membership ID, or kiosk check-in token.',
    sections: [
      dataTable('support-customers-table', 'Recent lookups', [
        { label: 'Guest', key: 'guest' },
        { label: 'Plan', key: 'plan' },
        { label: 'Minutes', key: 'minutes', align: 'right' },
        { label: 'Status', key: 'status' },
      ], [
        { guest: 'Isla Rose', plan: 'Glow Pro', minutes: '120', status: 'Active' },
        { guest: 'Kai Hart', plan: 'Dawn', minutes: '12', status: 'Refund review' },
      ]),
      insights('support-customers-tools', 'Context tools', [
        { title: 'Session playback', description: 'Watch lamp + concierge events for the visit.' },
        { title: 'Health checks', description: 'See last contraindication updates.' },
        { title: 'Wallet overrides', description: 'Issue credits with audit trail.' },
      ]),
    ],
  }),
  createSupportPage({
    key: 'support.studios',
    route: '/support/studios',
    title: 'Studio feed',
    description: 'Monitor incidents, staff escalations, and lamp telemetry for every studio.',
    sections: [
      statusList('support-studios-status', 'Active incidents', [
        { label: 'Mayfair', value: 'None', hint: 'All systems go', state: 'success' },
        { label: 'Shoreditch', value: 'Lamp warm warning', hint: 'Room 2 5° high', state: 'warning' },
        { label: 'Manchester', value: 'Retail stock low', hint: 'Hydration kits', state: 'danger' },
      ]),
      actionGrid('support-studios-actions', 'Studio tooling', [
        { label: 'Broadcast update', description: 'Send SMS/email to affected members.', icon: 'bi-megaphone' },
        { label: 'Ping on-call lead', description: 'Route to specific GM with priority level.', icon: 'bi-phone' },
        { label: 'Log incident', description: 'Capture notes + resolution timeline.', icon: 'bi-clipboard-pulse' },
      ]),
    ],
  }),
  createSupportPage({
    key: 'support.tools',
    route: '/support/tools',
    title: 'Toolbox',
    description: 'Utilities to impersonate guests, regenerate magic links, or purge cached kiosks.',
    sections: [
      actionGrid('support-tools-actions', 'Utilities', [
        { label: 'Impersonate member', description: 'Jump into /customer/* with audit logging.', icon: 'bi-eye' },
        { label: 'Magic link reset', description: 'Send branded link or SMS OTP instantly.', icon: 'bi-link-45deg' },
        { label: 'Wipe kiosk binding', description: 'Clear device pairing + issue new code.', icon: 'bi-tablet' },
      ]),
      insights('support-tools-guardrails', 'Guardrails', [
        { title: 'Dual approval', description: 'Certain actions require manager approval + MFA.' },
        { title: 'Auto-logging', description: 'Every impersonation or override lands in audit log.' },
      ]),
    ],
  }),
]

export function registerSupportPages() {
  supportPages.forEach(page => definePage(page))
}
