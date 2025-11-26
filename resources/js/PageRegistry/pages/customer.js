import { definePage } from '../registry'
import { summary, insights, dataTable, timeline, actionGrid } from '../helpers'

const defaultMetrics = [
  { label: 'Next clean', value: 'Wed · 10:00', delta: 'Track link ready 1h before' },
  { label: 'Plan cadence', value: 'Every 2 weeks', delta: 'Change any time' },
  { label: 'Payment status', value: 'Paid', delta: 'Auto-charge enabled' },
]

const createCustomerPage = input => ({
  key: input.key,
  route: input.route,
  layout: 'workspace',
  role: 'customer',
  badge: input.badge || 'Customer portal',
  title: input.title,
  description: input.description,
  sections: [
    summary(`${input.key}-summary`, 'At a glance', input.metrics || defaultMetrics),
    insights(`${input.key}-insights`, 'What you can do', input.highlights),
    ...(input.sections || []),
  ],
})

const customerPages = [
  createCustomerPage({
    key: 'customer.dashboard',
    route: '/customer/dashboard',
    title: 'Dashboard',
    description: 'Shows upcoming jobs, quick reschedule, and live tracking shortcuts.',
    highlights: [
      { title: 'Upcoming jobs', description: 'Timeline of the next three visits with reschedule + skip buttons and ETA badges.', meta: ['Timeline'] },
      { title: 'Track now', description: 'One tap to open live map for today’s clean when the cleaner starts travelling.' },
      { title: 'Account reminders', description: 'Cards for expiring cards, pending invoices, or paused plans.' },
    ],
  }),
  createCustomerPage({
    key: 'customer.cleans',
    route: '/customer/cleans',
    title: 'All cleans',
    description: 'List view of upcoming and past jobs with filters and export.',
    highlights: [
      { title: 'Segmented tabs', description: 'Separate upcoming vs. history with quick search by address or cleaner.' },
      { title: 'Reschedule in-line', description: 'Drag any future visit onto a new day, respecting policy windows.' },
      { title: 'Download receipts', description: 'Each entry links to /receipt/:id without leaving the portal.' },
    ],
    sections: [
      dataTable('customer-cleans-table', 'Recent visits', [
        { label: 'Date', key: 'date' },
        { label: 'Cleaner', key: 'cleaner' },
        { label: 'Status', key: 'status' },
        { label: 'Actions', key: 'actions', align: 'right' },
      ], [
        { date: 'Wed 10 Apr · 10:00', cleaner: 'Maya', status: 'Completed', actions: 'Receipt' },
        { date: 'Fri 26 Apr · 14:00', cleaner: 'Leo', status: 'Scheduled', actions: 'Reschedule' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.clean-detail',
    route: '/customer/cleans/:jobId',
    title: 'Clean details',
    description: 'Full job breakdown, notes, checklists, add-ons, and track link.',
    highlights: [
      { title: 'Scope overview', description: 'Show plan, add-ons, cleaner assignments, and prep notes.' },
      { title: 'Checklist playback', description: 'View before/after photos, checklist ticks, and incident notes.' },
      { title: 'One-tap support', description: 'Raise dispute or request re-clean directly from the job.' },
    ],
    sections: [
      timeline('customer-clean-timeline', 'Job timeline', [
        { title: 'En-route', time: '09:42', detail: 'Cleaner left previous job.', state: 'info' },
        { title: 'On site', time: '10:10', detail: 'Checklist started · kitchen first', state: 'success' },
        { title: 'Completed', time: '12:05', detail: 'After photos uploaded', state: 'success' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.track',
    route: '/customer/track/:jobId',
    title: 'Track clean',
    description: 'Embedded live tracking page for authenticated customers.',
    highlights: [
      { title: 'Background refresh', description: 'Auto-updates ETA, lateness badges, and cleaner profile without reload.' },
      { title: 'Communication shortcuts', description: 'Buttons to call, text, or notify support with context.' },
      { title: 'Neighborhood tips', description: 'Shows parking and building instructions pinned from addresses.' },
    ],
    sections: [
      actionGrid('customer-track-actions', 'Quick actions', [
        { label: 'Message cleaner', description: 'Send templated or custom notes.', icon: 'bi-chat-left-text' },
        { label: 'Share link', description: 'Send live tracking to a family member.', icon: 'bi-share' },
        { label: 'Report issue', description: 'Open support ticket referencing this job.', icon: 'bi-exclamation-octagon' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.addresses',
    route: '/customer/addresses',
    title: 'Addresses',
    description: 'CRUD interface for service locations and access instructions.',
    highlights: [
      { title: 'Access notes', description: 'Store door codes, concierge notes, and pet warnings with role-based visibility.' },
      { title: 'Default toggles', description: 'Mark preferred address per plan or per booking.' },
      { title: 'Map preview', description: 'Confirm pin placement and travel time impact when editing addresses.' },
    ],
  }),
  createCustomerPage({
    key: 'customer.billing',
    route: '/customer/billing',
    title: 'Billing',
    description: 'Links to Stripe Customer Portal for payment methods and invoices.',
    highlights: [
      { title: 'Stripe Customer Portal', description: 'Launches hosted portal inside a modal with contextual copy.' },
      { title: 'Flexible cadences', description: 'Switch between pay-per-visit or monthly autopay.' },
      { title: 'Credit balance', description: 'Show referral credits and how they will apply to next invoice.' },
    ],
  }),
  createCustomerPage({
    key: 'customer.invoices',
    route: '/customer/invoices',
    title: 'Invoices',
    description: 'List and download historical invoices.',
    highlights: [
      { title: 'Filters', description: 'Filter by status (paid, due, refunded).' },
      { title: 'Exports', description: 'Download CSV or forward to accountant email.' },
      { title: 'Dispute inline', description: 'Open disputes referencing invoice lines.' },
    ],
    sections: [
      dataTable('customer-invoices-table', 'Invoices', [
        { label: 'Invoice', key: 'inv' },
        { label: 'Date', key: 'date' },
        { label: 'Status', key: 'status' },
        { label: 'Amount', key: 'amount', align: 'right' },
      ], [
        { inv: '#GL-1042', date: '02 Apr 2025', status: 'Paid', amount: '£142.00' },
        { inv: '#GL-1037', date: '19 Mar 2025', status: 'Paid', amount: '£142.00' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.preferences',
    route: '/customer/preferences',
    title: 'Preferences',
    description: 'Control communications, language, and plan defaults.',
    highlights: [
      { title: 'Notification toggles', description: 'Email/SMS/push per event type.' },
      { title: 'Language selection', description: 'Localise portal UI + notifications instantly.' },
      { title: 'Cleaner requests', description: 'Choose “same cleaner” vs. “best availability”.' },
    ],
  }),
  createCustomerPage({
    key: 'customer.security',
    route: '/customer/security',
    title: 'Security',
    description: 'View magic-link history, revoke sessions, and claim account.',
    highlights: [
      { title: 'Session list', description: 'Show device, browser, and location for each active login.' },
      { title: 'Magic-link history', description: 'See when and where links were requested.' },
      { title: 'Passwordless claim', description: 'Optional password creation for legacy billing systems.' },
    ],
  }),
  createCustomerPage({
    key: 'customer.support',
    route: '/customer/support',
    title: 'Support',
    description: 'Open/view tickets and chat with ops.',
    highlights: [
      { title: 'Ticket inbox', description: 'Threaded view with SLA timers and attachments.' },
      { title: 'Live chat', description: 'Escalates to support role with context about plan + address.' },
      { title: 'Self-serve suggestions', description: 'Suggest relevant help articles before starting a chat.' },
    ],
    sections: [
      actionGrid('customer-support-actions', 'Support actions', [
        { label: 'Open ticket', description: 'Send a structured request with job context.', icon: 'bi-inbox' },
        { label: 'Start chat', description: 'Real-time chat staffed by support role.', icon: 'bi-chat-dots' },
        { label: 'View policies', description: 'Key terms for refunds and cancellations.', icon: 'bi-journal-text' },
      ]),
    ],
  }),
]

export function registerCustomerPages() {
  customerPages.forEach(page => definePage(page))
}
