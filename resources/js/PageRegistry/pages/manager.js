import { definePage } from '../registry'
import { summary, insights, kanban, dataTable, mapPanel, timeline, actionGrid } from '../helpers'

const baseSummary = [
  { label: 'Jobs today', value: '184', delta: '26 unassigned' },
  { label: 'On-time rate', value: '94%', delta: 'Target 96%' },
  { label: 'Open incidents', value: '5', delta: 'Escalated to support' },
]

const createManagerPage = input => ({
  key: input.key,
  route: input.route,
  layout: 'workspace',
  role: 'manager',
  badge: input.badge || 'Manager console',
  title: input.title,
  description: input.description,
  sections: [
    summary(`${input.key}-summary`, 'Operational pulse', input.summaryCards || baseSummary),
    insights(`${input.key}-insights`, 'Tooling highlights', input.highlights),
    ...(input.sections || []),
  ],
})

const dispatchPages = [
  createManagerPage({
    key: 'manager.dispatch.board',
    route: '/manager/dispatch/board',
    title: 'Dispatch board',
    description: 'Day/week calendar with drag-assign, unassigned queue, and conflict alerts.',
    highlights: [
      { title: 'Drag + drop scheduling', description: 'Move jobs across cleaners or days with SLA checks.' },
      { title: 'Unassigned queue', description: 'Queue for new/failed jobs with suggestions for best cleaner.' },
      { title: 'At-risk badges', description: 'Late/overlap warnings inline before saving.' },
    ],
    sections: [
      kanban('manager-dispatch-kanban', 'Workload snapshot', [
        { title: 'Unassigned', items: [ { title: '#1245 Deep clean', detail: '2h · North', assignee: 'Suggested: Maya' } ] },
        { title: 'Assigned', items: [ { title: '#1239 Weekly clean', detail: 'Leo · 10:00', assignee: 'On time' } ] },
        { title: 'At risk', items: [ { title: '#1234 Move-out', detail: 'Lena · 30m late', assignee: 'Need reroute' } ] },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.dispatch.routes',
    route: '/manager/dispatch/routes',
    title: 'Route builder',
    description: 'Build/optimise routes with constraints, travel time, and fuel estimation.',
    highlights: [
      { title: 'Multi-stop optimizer', description: 'Balance travel vs. job duration with fairness settings.' },
      { title: 'Constraints', description: 'Support shift windows, cleaner skills, and vehicle capacity.' },
      { title: 'Map preview', description: 'Overlay route with live traffic and distance metrics.' },
    ],
    sections: [
      mapPanel('manager-routes-map', 'Route preview', [
        { title: 'Cleaner route', detail: '6 stops • 14 miles', lat: 51.509, lng: -0.1, state: 'info' },
        { title: 'High priority job', detail: '#1242', lat: 51.503, lng: -0.08, state: 'warning' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.dispatch.exceptions',
    route: '/manager/dispatch/exceptions',
    title: 'Exceptions',
    description: 'Late/at-risk list with reroute tools.',
    highlights: [
      { title: 'Exception feed', description: 'Ranked list of jobs at risk with recommended actions.' },
      { title: 'One-click reroute', description: 'Reassign to standby cleaners or split jobs.' },
      { title: 'Customer notification', description: 'Notify customers with templated updates.' },
    ],
  }),
  createManagerPage({
    key: 'manager.dispatch.bulk',
    route: '/manager/dispatch/bulk',
    title: 'Bulk scheduling',
    description: 'Create/shift/skip multiple jobs at once.',
    highlights: [
      { title: 'Spreadsheet import', description: 'Upload CSV or copy/paste from Excel.' },
      { title: 'Bulk edit wizard', description: 'Shift, cancel, or duplicate sets of jobs with policy guardrails.' },
      { title: 'Preview diff', description: 'See final changes before committing.' },
    ],
  }),
]

const jobPages = [
  createManagerPage({
    key: 'manager.jobs.list',
    route: '/manager/jobs',
    title: 'Jobs',
    description: 'List/filter jobs by status, date, cleaner, or customer.',
    highlights: [
      { title: 'Power filters', description: 'Status, tags, cleaners, addresses, or timeframe.' },
      { title: 'Batch actions', description: 'Select multiple jobs for assign/reschedule.' },
      { title: 'Export CSV', description: 'Send to ops analytics or share externally.' },
    ],
    sections: [
      dataTable('manager-jobs-table', 'Jobs today', [
        { label: 'Job', key: 'job' },
        { label: 'Customer', key: 'customer' },
        { label: 'Cleaner', key: 'cleaner' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { job: '#1241 · Weekly clean', customer: 'Priya', cleaner: 'Leo', status: 'Scheduled' },
        { job: '#1242 · Deep clean', customer: 'Marcus', cleaner: 'Maya', status: 'At risk' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.jobs.new',
    route: '/manager/jobs/new',
    title: 'Create job',
    description: 'Create one-off or series with templates.',
    highlights: [
      { title: 'Series builder', description: 'Define cadence, end date, and skip rules.' },
      { title: 'Template picker', description: 'Load standard checklists and add-ons per service type.' },
      { title: 'Availability preview', description: 'See best slots before confirming.' },
    ],
  }),
  createManagerPage({
    key: 'manager.jobs.detail',
    route: '/manager/jobs/:jobId',
    title: 'Job detail',
    description: 'Edit job details, add-ons, time windows, notes.',
    highlights: [
      { title: 'Audit trail', description: 'Every change logged with user + timestamp.' },
      { title: 'Time window control', description: 'Set primary/secondary windows with travel validation.' },
      { title: 'Add-on store', description: 'Upsell extras or change pricing on the fly.' },
    ],
  }),
  createManagerPage({
    key: 'manager.checklists',
    route: '/manager/checklists',
    title: 'Checklists',
    description: 'Template library for service scopes.',
    highlights: [
      { title: 'Template versions', description: 'Version history with effective dates.' },
      { title: 'Role visibility', description: 'Choose which roles can see each step.' },
      { title: 'Media hints', description: 'Attach reference photos or instructions.' },
    ],
  }),
  createManagerPage({
    key: 'manager.addons',
    route: '/manager/addons',
    title: 'Add-ons catalog',
    description: 'Manage add-on pricing, durations, and availability.',
    highlights: [
      { title: 'Duration overrides', description: 'Adjust labour time per add-on.' },
      { title: 'Eligibility', description: 'Limit add-ons to property size or plan type.' },
      { title: 'Promotions', description: 'Attach coupons or thresholds.' },
    ],
  }),
]

const livePages = [
  createManagerPage({
    key: 'manager.live.map',
    route: '/manager/live/map',
    title: 'Live map',
    description: 'See cleaners on a map with active jobs and ETAs.',
    highlights: [
      { title: 'Fleet view', description: 'Color-coded pins for on-time vs. late vs. idle.' },
      { title: 'Job overlays', description: 'Display job windows and customer notes.' },
      { title: 'Heatmaps', description: 'Optionally show demand hotspots.' },
    ],
    sections: [
      mapPanel('manager-live-map', 'Tracking', [
        { title: 'Cleaner Maya', detail: 'On time · Job #1241', lat: 51.513, lng: -0.09, state: 'success' },
        { title: 'Cleaner Leo', detail: 'Running late 12m', lat: 51.52, lng: -0.07, state: 'warning' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.live.timeline',
    route: '/manager/live/timeline',
    title: 'Live timeline',
    description: 'Status feed of en-route, started, paused, completed events.',
    highlights: [
      { title: 'Filterable feed', description: 'Filter by cleaner, customer, or alert type.' },
      { title: 'Resync controls', description: 'Force tracker resend if heartbeat missing.' },
      { title: 'Escalation buttons', description: 'Ping support or owner when something drifts.' },
    ],
    sections: [
      timeline('manager-live-events', 'Events', [
        { title: 'Job #1241 started', time: '09:02', detail: 'Maya on site', state: 'success' },
        { title: 'Job #1238 paused', time: '09:15', detail: 'Waiting for access', state: 'warning' },
        { title: 'Job #1235 completed', time: '09:20', detail: 'Photos uploaded', state: 'success' },
      ]),
    ],
  }),
]

const customerPages = [
  createManagerPage({
    key: 'manager.customers.list',
    route: '/manager/customers',
    title: 'Customers',
    description: 'Searchable customer list with job + billing context.',
    highlights: [
      { title: '360° profile cards', description: 'Show plan, last job, and support status.' },
      { title: 'Segment filters', description: 'Filter by cadence, tags, or lifecycle.' },
      { title: 'Bulk messaging', description: 'Send announcements to filtered cohorts.' },
    ],
  }),
  createManagerPage({
    key: 'manager.customers.detail',
    route: '/manager/customers/:id',
    title: 'Customer detail',
    description: 'Profile, addresses, jobs, invoices, notes.',
    highlights: [
      { title: 'Interaction log', description: 'Every touchpoint recorded for context.' },
      { title: 'Plan controls', description: 'Adjust cadence, add skips, pause/resume plans.' },
      { title: 'Billing snapshot', description: 'See latest payment status and outstanding balances.' },
    ],
  }),
  createManagerPage({
    key: 'manager.subscriptions',
    route: '/manager/subscriptions',
    title: 'Subscriptions',
    description: 'Manage cadence, skips, and pauses for plans.',
    highlights: [
      { title: 'Skip tracker', description: 'Track auto-skips and manual pauses.' },
      { title: 'Schedule preview', description: 'Show upcoming visits impacted by changes.' },
      { title: 'Billing alignment', description: 'Sync plan changes with invoice cycles.' },
    ],
  }),
]

const staffPages = [
  createManagerPage({
    key: 'manager.staff.list',
    route: '/manager/staff',
    title: 'Staff',
    description: 'Roster of cleaners/managers with availability and performance.',
    highlights: [
      { title: 'Availability matrix', description: 'See who is working which days.' },
      { title: 'Device status', description: 'Monitor tracker heartbeat / app version.' },
      { title: 'Performance', description: 'Show on-time %, CSAT, incidents.' },
    ],
  }),
  createManagerPage({
    key: 'manager.staff.detail',
    route: '/manager/staff/:id',
    title: 'Staff profile',
    description: 'Profile, documents, time-off, performance.',
    highlights: [
      { title: 'Certifications', description: 'Store docs and expiry dates.' },
      { title: 'Time-off calendar', description: 'Approve PTO and block scheduling conflicts.' },
      { title: 'Device health', description: 'Check last login, OS version, and location permission.' },
    ],
  }),
  createManagerPage({
    key: 'manager.shifts',
    route: '/manager/shifts',
    title: 'Shifts',
    description: 'Capacity planning and time-off.',
    highlights: [
      { title: 'Shift templates', description: 'Define standard shift blocks for quick assignment.' },
      { title: 'Capacity chart', description: 'See coverage vs. demand per day.' },
      { title: 'Time-off approvals', description: 'Approve/deny requests inline.' },
    ],
  }),
  createManagerPage({
    key: 'manager.announcements',
    route: '/manager/announcements',
    title: 'Announcements',
    description: 'Broadcast updates to cleaners with read receipts.',
    highlights: [
      { title: 'Audience targeting', description: 'Filter by team, zone, or role.' },
      { title: 'Delivery channels', description: 'Push, SMS, email, or in-app banners.' },
      { title: 'Acknowledgements', description: 'Track who confirmed reading critical notices.' },
    ],
  }),
]

const financePages = [
  createManagerPage({
    key: 'manager.refunds',
    route: '/manager/refunds',
    title: 'Refunds',
    description: 'Create/view refunds within policy.',
    highlights: [
      { title: 'Policy guardrails', description: 'Warn when outside refund window or exceeding limits.' },
      { title: 'Evidence attachments', description: 'Attach photos, notes, or support tickets.' },
      { title: 'Stripe sync', description: 'Issue refunds via Stripe with audit trail.' },
    ],
  }),
  createManagerPage({
    key: 'manager.adjustments',
    route: '/manager/adjustments',
    title: 'Adjustments',
    description: 'Credits/charges ledger.',
    highlights: [
      { title: 'Ledger view', description: 'See all manual credits/debits.' },
      { title: 'Bulk adjustments', description: 'Apply to cohorts for promos.' },
      { title: 'Audit-ready', description: 'Exportable ledger with user + reason codes.' },
    ],
  }),
]

const reportsPages = [
  createManagerPage({
    key: 'manager.reports.operations',
    route: '/manager/reports/operations',
    title: 'Ops report',
    description: 'OTIF, lateness, completion rate, productivity.',
    highlights: [
      { title: 'OTIF widgets', description: 'Shows on-time-in-full for the period.' },
      { title: 'Bottleneck analysis', description: 'Spot cleaners or zones causing delays.' },
      { title: 'Export to CSV/Looker', description: 'Push metrics into BI tools.' },
    ],
  }),
  createManagerPage({
    key: 'manager.reports.quality',
    route: '/manager/reports/quality',
    title: 'Quality report',
    description: 'CSAT, disputes, re-cleans, quality trends.',
    highlights: [
      { title: 'CSAT distribution', description: 'Charts by cleaner or customer.' },
      { title: 'Dispute reasons', description: 'Breakdown by root cause.' },
      { title: 'Re-clean tracker', description: 'Monitor repeat issues.' },
    ],
  }),
  createManagerPage({
    key: 'manager.reports.volume',
    route: '/manager/reports/volume',
    title: 'Volume report',
    description: 'Jobs per area/weekday, booking mix.',
    highlights: [
      { title: 'Booking mix', description: 'Recurring vs. one-off share.' },
      { title: 'Zone/weekday heatmap', description: 'Identify peaks to staff accordingly.' },
      { title: 'Forecast export', description: 'Send to owners/accountants.' },
    ],
  }),
]

const settingsPages = [
  createManagerPage({
    key: 'manager.settings.policies',
    route: '/manager/settings/policies',
    title: 'Policies',
    description: 'Cancellation, reschedule, service windows, surcharges.',
    highlights: [
      { title: 'Policy builder', description: 'Define windows per plan tier.' },
      { title: 'Preview fees', description: 'Show customers what happens when they cancel late.' },
      { title: 'Version control', description: 'Effective dates + approvals.' },
    ],
  }),
  createManagerPage({
    key: 'manager.settings.notifications',
    route: '/manager/settings/notifications',
    title: 'Notification templates',
    description: 'Manage SMS/email pushes to customers & cleaners.',
    highlights: [
      { title: 'Template variables', description: 'Use {{job.window}}, {{customer.name}} etc.' },
      { title: 'A/B testing', description: 'Test variants before roll-out.' },
      { title: 'Channel fallback', description: 'Define SMS backup if push fails.' },
    ],
  }),
  createManagerPage({
    key: 'manager.settings.integrations',
    route: '/manager/settings/integrations',
    title: 'Integrations',
    description: 'SMS/email providers, webhooks, Slack alerts.',
    highlights: [
      { title: 'Provider connections', description: 'Twilio, Mailgun, etc. with health checks.' },
      { title: 'Webhook secret mgmt', description: 'Rotate secrets and view delivery logs.' },
      { title: 'Sandbox keys', description: 'Test mode for QA.' },
    ],
  }),
]

const managerPages = [
  ...dispatchPages,
  ...jobPages,
  ...livePages,
  ...customerPages,
  ...staffPages,
  ...financePages,
  ...reportsPages,
  ...settingsPages,
]

export function registerManagerPages() {
  managerPages.forEach(page => definePage(page))
}
