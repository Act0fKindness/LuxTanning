import { definePage } from '../registry'
import { summary, insights, timeline, actionGrid, dataTable } from '../helpers'

const createCleanerPage = input => ({
  key: input.key,
  route: input.route,
  layout: 'pwa',
  role: 'cleaner',
  badge: 'Cleaner PWA',
  title: input.title,
  description: input.description,
  sections: input.sections || [
    summary(`${input.key}-summary`, 'Run-ready stats', [
      { label: 'Jobs today', value: '6', delta: 'Route auto-optimised' },
      { label: 'Travel time', value: '1h12', delta: 'Traffic adjusted' },
      { label: 'Late risk', value: 'Low', delta: 'Keep tracker online' },
    ]),
    insights(`${input.key}-insights`, 'Key actions', input.highlights),
    ...(input.timelineEvents ? [timeline(`${input.key}-timeline`, 'Flow', input.timelineEvents)] : []),
    ...(input.extraSections || []),
  ],
})

const cleanerNextJobSection = {
  id: 'cleaner-next-job',
  component: 'CleanerNextJob',
  title: 'Next stop',
  description: 'Navigate, start, finish, or cancel the focused job.',
  props: { jobKey: 'cleaner.next_job_card' },
}

const cleanerTodaySections = [
  cleanerNextJobSection,
  summary('cleaner-today-summary', 'Today’s stats', [
    { label: 'Jobs today', value: '{{ cleaner.stats.total_today }}', delta: '{{ cleaner.stats.completed_today }} completed' },
    { label: 'In progress', value: '{{ cleaner.stats.in_progress }}', delta: 'Travel ~{{ cleaner.stats.travel_minutes }} mins' },
    { label: 'Late risk', value: '{{ cleaner.stats.late_risk }}', delta: 'Keep tracker + location on' },
  ]),
  insights('cleaner-today-insights', 'Key actions', [
    { title: 'Next stop', description: '{{ cleaner.next_job_label }}' },
    { title: 'Remaining route', description: '{{ cleaner.stats.total_today }} scheduled · {{ cleaner.stats.completed_today }} done' },
    { title: 'Location sharing', description: 'Navigation uses Google Maps for {{ cleaner.stats.in_progress }} active jobs.' },
  ]),
  timeline('cleaner-today-timeline', 'Route timeline', [], null, { eventsKey: 'cleaner.timeline' }),
  dataTable('cleaner-today-table', 'Stops today', [
    { label: 'Window', key: 'slot' },
    { label: 'Address', key: 'address' },
    { label: 'Status', key: 'status' },
  ], [], 'Chronological view of assigned stops.', null, { rowsKey: 'cleaner.tables.today' }),
  dataTable('cleaner-upcoming-table', 'Upcoming stops', [
    { label: 'Window', key: 'slot' },
    { label: 'Address', key: 'address' },
    { label: 'Status', key: 'status' },
  ], [], 'Next cleans beyond today.', null, { rowsKey: 'cleaner.tables.upcoming' }),
  {
    id: 'cleaner-today-map',
    component: 'CleanerMap',
    title: 'Navigate to next job',
    description: 'Live Google Maps directions using the job’s address and your GPS.',
    props: { jobKey: 'cleaner.active_job' },
  },
]

const cleanerHistorySections = [
  summary('cleaner-history-summary', 'Recent jobs', [
    { label: 'Logged', value: '{{ cleaner.history.stats.recent_total }}', delta: '{{ cleaner.history.stats.completed }} completed' },
    { label: 'Streak', value: '{{ cleaner.history.stats.completed_streak }}', delta: '{{ cleaner.history.stats.cancelled }} cancellations recorded' },
    { label: 'Latest status', value: '{{ cleaner.history.stats.latest_status }}', delta: '{{ cleaner.history.stats.latest_job_label }}' },
  ]),
  insights('cleaner-history-insights', 'Quick recap', [
    { title: 'Latest job', description: '{{ cleaner.history.stats.latest_job_label }}' },
    { title: 'Completion streak', description: '{{ cleaner.history.stats.completed_streak }} finished back-to-back.' },
    { title: 'Cancellations', description: '{{ cleaner.history.stats.cancelled }} cancellations recorded.' },
  ]),
  dataTable('cleaner-history-table', 'Previous jobs', [
    { label: 'Date', key: 'date' },
    { label: 'Window', key: 'window' },
    { label: 'Address', key: 'address' },
    { label: 'Status', key: 'status' },
    { label: 'Duration', key: 'duration', align: 'right' },
  ], [], 'Most recent logs (30 max) with their outcomes and time on site.', null, { rowsKey: 'cleaner.tables.history', maxWidth: '960px' }),
]

const cleanerNavigateSections = [
  {
    id: 'cleaner-navigation-panel',
    component: 'CleanerNavigation',
    title: 'Navigate to job',
    description: 'Live directions, arrival control, and quick actions.',
    props: { jobIdKey: 'jobId', jobMapKey: 'cleaner.jobs_by_id' },
  },
  timeline('cleaner-nav-timeline', 'Route timeline', [], null, { eventsKey: 'cleaner.timeline' }),
]

const cleanerPages = [
  createCleanerPage({
    key: 'cleaner.today',
    route: '/cleaner/today',
    title: 'Today / Route',
    description: 'Route timeline with travel, buffers, lateness badges, and leave-by timers.',
    sections: cleanerTodaySections,
  }),
  createCleanerPage({
    key: 'cleaner.job-detail',
    route: '/cleaner/jobs/:jobId',
    title: 'Job detail',
    description: 'Address, contacts, checklist, add-ons, and photos.',
    highlights: [
      { title: 'Access card', description: 'Door codes, parking instructions, pets, and hazards.' },
      { title: 'Checklist-first', description: 'Tap-friendly checklist that logs timestamps and photos.' },
      { title: 'One-tap incident', description: 'Report issues with reason codes + photos.' },
    ],
  }),
  createCleanerPage({
    key: 'cleaner.navigate',
    route: '/cleaner/jobs/:jobId/navigate',
    title: 'Navigate',
    description: 'Open the in-app navigation, mark arrival, and jump into Start/Cancel.',
    sections: cleanerNavigateSections,
  }),
  createCleanerPage({
    key: 'cleaner.start',
    route: '/cleaner/jobs/:jobId/start',
    title: 'Start job',
    description: 'Confirm arrival, capture timestamp + geofence, trigger customer notification.',
    highlights: [
      { title: 'Geo fence', description: 'Ensures cleaner is within 80m before allowing start.' },
      { title: 'Arrival note', description: 'Optional note to customer (e.g., “running 5 mins late”).' },
      { title: 'Auto statuses', description: 'Dispatch timeline flips to On-site and recomputes ETA for later jobs.' },
    ],
  }),
  createCleanerPage({
    key: 'cleaner.pause',
    route: '/cleaner/jobs/:jobId/pause',
    title: 'Pause job',
    description: 'Capture pause reason (waiting, break, supplies) with resume reminders.',
    highlights: [
      { title: 'Reason codes', description: 'Waiting, break, supplies, customer request, incident.' },
      { title: 'Timer', description: 'Shows paused duration, warns after 10 minutes.' },
      { title: 'Dispatch ping', description: 'Managers get alert if pause exceeds threshold.' },
    ],
  }),
  createCleanerPage({
    key: 'cleaner.finish',
    route: '/cleaner/jobs/:jobId/finish',
    title: 'Finish job',
    description: 'Complete checklist, upload after-photos, collect signature, upsell add-ons.',
    highlights: [
      { title: 'Checklist validation', description: 'All tasks must be ticked with optional photo proof.' },
      { title: 'Signature capture', description: 'Customers can sign on device or skip with reason.' },
      { title: 'Upsell prompts', description: 'Offer add-ons for next visit if time allowed extras.' },
    ],
  }),
  createCleanerPage({
    key: 'cleaner.cancel',
    route: '/cleaner/jobs/:jobId/cancel',
    title: 'Cancel job',
    description: 'Reason-coded cancellations with photo evidence and auto-notifications.',
    highlights: [
      { title: 'Reason list', description: 'No access, unsafe, customer cancelled, supplies missing, other.' },
      { title: 'Photo requirement', description: 'Unsafe/no access require at least one photo.' },
      { title: 'Dispatch workflow', description: 'Triggers reroute to cover the slot.' },
    ],
  }),
  createCleanerPage({
    key: 'cleaner.history',
    route: '/cleaner/history',
    title: 'History',
    description: 'Past jobs with durations, notes, ratings, and earnings.',
    sections: cleanerHistorySections,
  }),
  createCleanerPage({
    key: 'cleaner.earnings',
    route: '/cleaner/earnings',
    title: 'Earnings',
    description: 'Week summary, tips, payouts, and adjustments.',
    highlights: [
      { title: 'Weekly overview', description: 'See estimated payout, tips, and outstanding adjustments.' },
      { title: 'Payout schedule', description: 'Countdown to next payout with bank info.' },
      { title: 'Tax info', description: 'Remind cleaners to download statements.' },
    ],
    extraSections: [
      dataTable('cleaner-earnings-table', 'This week', [
        { label: 'Job', key: 'job' },
        { label: 'Pay', key: 'pay' },
        { label: 'Tip', key: 'tip' },
      ], [
        { job: '#1234 Tue 09:00', pay: '£52.00', tip: '£8.00' },
        { job: '#1235 Tue 13:00', pay: '£48.00', tip: '—' },
      ]),
    ],
  }),
  createCleanerPage({
    key: 'cleaner.inbox',
    route: '/cleaner/inbox',
    title: 'Inbox',
    description: 'Announcements, job reassignment alerts, payroll updates.',
    highlights: [
      { title: 'Pinned alerts', description: 'Urgent posts stay on top until acknowledged.' },
      { title: 'Job reassignments', description: 'Swipe to accept/decline new jobs.' },
      { title: 'Read receipts', description: 'Managers see who read critical notices.' },
    ],
  }),
  createCleanerPage({
    key: 'cleaner.settings',
    route: '/cleaner/settings',
    title: 'Settings',
    description: 'Availability toggle, language, notifications, device health.',
    highlights: [
      { title: 'Availability', description: 'Toggle “Available today” or plan time off.' },
      { title: 'Location permission', description: 'Remind cleaners to keep precise location on.' },
      { title: 'Notification tones', description: 'Choose push/email combos for assignments.' },
    ],
  }),
  createCleanerPage({
    key: 'cleaner.offline',
    route: '/cleaner/offline',
    title: 'Offline sync centre',
    description: 'Manage queued pings, unsent photos, and retries when connection returns.',
    highlights: [
      { title: 'Sync queue', description: 'List of events/photos waiting for upload.' },
      { title: 'Retry controls', description: 'Retry all or discard individual items.' },
      { title: 'Storage usage', description: 'Warn when offline cache exceeds safe size.' },
    ],
  }),
]

export function registerCleanerPages() {
  cleanerPages.forEach(page => definePage(page))
}
