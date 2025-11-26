import { definePage } from '../registry'
import { summary, insights, dataTable, actionGrid } from '../helpers'

const supportSummary = [
  { label: 'Open tickets', value: '62', delta: '22 SLA risk' },
  { label: 'Avg first response', value: '5m', delta: 'Goal <10m' },
  { label: 'Escalations', value: '4', delta: 'Owner attention' },
]

const supportPages = [
  {
    key: 'support.tickets',
    route: '/support/tickets',
    layout: 'workspace',
    role: 'support',
    badge: 'Support desk',
    title: 'Tickets',
    description: 'Inbox with SLA timers and role-aware macros.',
    sections: [
      summary('support-tickets-summary', 'Queue health', supportSummary),
      dataTable('support-tickets-table', 'Active tickets', [
        { label: 'Ticket', key: 'ticket' },
        { label: 'Customer', key: 'customer' },
        { label: 'Status', key: 'status' },
        { label: 'SLA', key: 'sla', align: 'right' },
      ], [
        { ticket: '#T-540', customer: 'Priya', status: 'Waiting on customer', sla: '02:14' },
        { ticket: '#T-539', customer: 'Marcus', status: 'Needs reroute', sla: '00:32' },
      ]),
      insights('support-tickets-highlights', 'Workflow', [
        { title: 'Role macros', description: 'Send manager, cleaner, or customer templates with correct branding.' },
        { title: 'Live state sync', description: 'See job + dispatch context while replying.' },
        { title: 'Escalation ladder', description: 'Escalate to manager or owner with one click when policy thresholds exceeded.' },
      ]),
    ],
  },
  {
    key: 'support.customer-detail',
    route: '/support/customers/:id',
    layout: 'workspace',
    role: 'support',
    badge: 'Support desk',
    title: 'Customer quick actions',
    description: 'Search customer, resend links, change windows within policy.',
    sections: [
      summary('support-customer-summary', 'Customer snapshot', supportSummary),
      actionGrid('support-customer-actions', 'Common actions', [
        { label: 'Resend magic link', description: 'Send secure manage link in one tap.', icon: 'bi-link' },
        { label: 'Adjust window', description: 'Move today’s window within policy limits.', icon: 'bi-clock-history' },
        { label: 'Mask PII', description: 'Anonymise data on request.', icon: 'bi-incognito' },
      ]),
      insights('support-customer-notes', 'Context', [
        { title: 'Last communication', description: 'Summary of last touch with timestamps.' },
        { title: 'Plan status', description: 'Active/paused, next billing date.' },
        { title: 'Support sentiment', description: 'CSAT + history of disputes.' },
      ]),
    ],
  },
  {
    key: 'support.job-detail',
    route: '/support/jobs/:jobId',
    layout: 'workspace',
    role: 'support',
    badge: 'Support desk',
    title: 'Job status',
    description: 'View job status, reassign, notify customer.',
    sections: [
      summary('support-job-summary', 'Job snapshot', supportSummary),
      insights('support-job-actions', 'Tooling', [
        { title: 'Notify customer', description: 'Send SMS/email updates with templated copy.' },
        { title: 'Reassign cleaner', description: 'Search available cleaners + travel time.' },
        { title: 'Dispatch ping', description: 'Push updates to managers for urgent follow-up.' },
      ]),
    ],
  },
  {
    key: 'support.tools',
    route: '/support/tools',
    layout: 'workspace',
    role: 'support',
    badge: 'Support desk',
    title: 'Tools',
    description: 'Utility panel for magic links, receipts, anonymisation.',
    sections: [
      summary('support-tools-summary', 'Usage', supportSummary),
      actionGrid('support-tools-actions', 'Utilities', [
        { label: 'Resend receipt', description: 'Email PDF to any address.', icon: 'bi-envelope' },
        { label: 'Generate manage link', description: 'Create short-lived manage link for customer.', icon: 'bi-magic' },
        { label: 'Anonymise PII', description: 'Scrub personal data upon GDPR request.', icon: 'bi-incognito' },
      ]),
      insights('support-tools-guardrails', 'Guardrails', [
        { title: 'Audit logging', description: 'Every tool action logged for compliance.' },
        { title: 'Rate limits', description: 'Throttle high-risk actions like manage link creation.' },
        { title: 'Policy reminders', description: 'Remind agents of rules before performing destructive actions.' },
      ]),
    ],
  },
]

export function registerSupportPages() {
  supportPages.forEach(page => definePage(page))
}
