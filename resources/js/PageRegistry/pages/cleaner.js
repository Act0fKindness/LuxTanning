import { definePage } from '../registry'
import { summary, timeline, dataTable, actionGrid, insights, checklist, statusList } from '../helpers'

const shiftSummary = [
  { label: 'Sessions today', value: '11', delta: '3 completed' },
  { label: 'Add-on goal', value: '£240', delta: '£120 achieved' },
  { label: 'Lamp alerts', value: '1', delta: 'Room 4 warm' },
]

const createStaffPage = spec => ({
  key: spec.key,
  route: spec.route,
  layout: 'pwa',
  role: 'cleaner',
  badge: 'Glow Guide PWA',
  title: spec.title,
  description: spec.description,
  sections: [
    ...(spec.skipSummary ? [] : [summary(`${spec.key}-summary`, 'Shift pulse', spec.summaryCards || shiftSummary)]),
    ...(spec.sections || []),
  ],
})

const staffPages = [
  createStaffPage({
    key: 'cleaner.today',
    route: '/cleaner/today',
    title: 'Shift board',
    description: 'Timeline of every session, prep task, and retail goal for today.',
    sections: [
      timeline('cleaner-today-route', 'Route timeline', [
        { title: '06:30 · Prep', time: 'Done', detail: 'Lamps warmed, hydration bar stocked.', state: 'success' },
        { title: '07:00 · Glow Pro — Room 1', time: 'In progress', detail: 'Guest: Ava K · Playlist “Amber Drift”', state: 'info' },
        { title: '08:15 · Waitlist slot', time: 'Up next', detail: 'Text Jenny once lamp cools (5m).', state: 'warning' },
      ]),
      actionGrid('cleaner-today-actions', 'On-shift actions', [
        { label: 'Log aftercare', description: 'Mark serums + boosters sold.', icon: 'bi-heart' },
        { label: 'Capture photo proof', description: 'Upload lamp/room shots for QC.', icon: 'bi-camera' },
        { label: 'Nudge waitlist', description: 'Broadcast open slot to top 5.', icon: 'bi-broadcast-pin' },
      ]),
    ],
  }),
  createStaffPage({
    key: 'cleaner.clients',
    route: '/cleaner/clients',
    title: 'Client notes',
    description: 'Every guest’s preferences, contraindications, and retail history.',
    sections: [
      dataTable('cleaner-clients-table', 'Today’s guests', [
        { label: 'Guest', key: 'guest' },
        { label: 'Plan', key: 'plan' },
        { label: 'Notes', key: 'notes' },
      ], [
        { guest: 'Ava Kim', plan: 'Glow Pro 20', notes: 'Prefers citrus scent · focus on hydration' },
        { guest: 'Marcus Rao', plan: 'Dawn Session', notes: 'Sensitive skin — reduce exposure 2m' },
      ]),
      checklist('cleaner-clients-checks', 'Before they arrive', [
        { label: 'Review contraindications', detail: 'Medication changes or medical flags.' },
        { label: 'Set playlist + scent', detail: 'Auto from preferences, but edit anytime.' },
        { label: 'Prep hydration kit', detail: 'Electrolyte + serum combos waiting in room.' },
      ]),
    ],
  }),
  createStaffPage({
    key: 'cleaner.courses',
    route: '/cleaner/courses',
    title: 'Courses & promos',
    description: 'Upsell boosters, gift cards, and new courses straight from the PWA.',
    sections: [
      insights('cleaner-courses-highlights', 'What’s running this week', [
        { title: 'Hydration Boost kit', description: 'Attach to Dawn + Glow Pro for +£18.' },
        { title: 'Solar Club waiting list', description: 'Invite members who finished 2+ Glow Pro cycles.' },
        { title: 'Creator referral', description: 'Share one-tap referral codes with engaged guests.' },
      ]),
      actionGrid('cleaner-courses-cta', 'Quick add', [
        { label: 'Add booster', description: 'Log retail sale + attach to receipt.', icon: 'bi-bag-plus' },
        { label: 'Send guest pass', description: 'Gift 10min from this wallet.', icon: 'bi-send' },
        { label: 'Escalate to Glow Guide', description: 'Need approval? Ping manager.', icon: 'bi-arrow-up-circle' },
      ]),
    ],
  }),
  createStaffPage({
    key: 'cleaner.bed-health',
    route: '/cleaner/bed-health',
    title: 'Bed health & maintenance',
    description: 'Lamp hours, temperature, and cleaning timers in one checklist.',
    sections: [
      statusList('cleaner-bed-status', 'Bed telemetry', [
        { label: 'Room 1', value: 'Optimal', hint: 'Lamp hours 412 / 600', state: 'success' },
        { label: 'Room 3', value: 'Cooling', hint: 'Ready in 4 min', state: 'warning' },
        { label: 'Room 4', value: 'Service due', hint: 'Lamp swap tonight', state: 'danger' },
      ]),
      checklist('cleaner-bed-checks', 'Closing tasks', [
        { label: 'Disinfect surfaces', detail: 'Capture photo proof for QC.' },
        { label: 'Update lamp hours', detail: 'System auto-logs, but confirm any anomalies.' },
        { label: 'Restock boosters', detail: 'Scan barcode to confirm inventory.' },
      ]),
    ],
  }),
  createStaffPage({
    key: 'cleaner.inbox',
    route: '/cleaner/inbox',
    title: 'Inbox & announcements',
    description: 'Chats with members, manager alerts, and automation nudges.',
    sections: [
      dataTable('cleaner-inbox-feed', 'Recent threads', [
        { label: 'From', key: 'from' },
        { label: 'Topic', key: 'topic' },
        { label: 'Status', key: 'status' },
      ], [
        { from: 'Glow Guide HQ', topic: 'Lamp Lab agenda', status: 'Unread' },
        { from: 'Ava Kim', topic: 'Playlist tweak', status: 'Replied' },
        { from: 'System', topic: 'Hydration kit restock', status: 'Pinned' },
      ]),
      actionGrid('cleaner-inbox-actions', 'Do more', [
        { label: 'Broadcast shift note', description: 'Send studio-wide message.', icon: 'bi-megaphone' },
        { label: 'Escalate to manager', description: 'Need support? escalate w/ context.', icon: 'bi-arrow-up-right-square' },
        { label: 'Mute automation', description: 'Silence non-critical pings when with guests.', icon: 'bi-bell-slash' },
      ]),
    ],
  }),
  createStaffPage({
    key: 'cleaner.earnings',
    route: '/cleaner/earnings',
    title: 'Earnings & goals',
    description: 'Track hourly pay, commission, and booster goals.',
    sections: [
      summary('cleaner-earnings-stats', 'This pay period', [
        { label: 'Hours', value: '32', delta: 'Scheduled 36' },
        { label: 'Commission', value: '£184', delta: '+32 vs avg' },
        { label: 'Boosters sold', value: '11', delta: 'Goal 14' },
      ]),
      dataTable('cleaner-earnings-ledger', 'Ledger', [
        { label: 'Date', key: 'date' },
        { label: 'Type', key: 'type' },
        { label: 'Amount', key: 'amount', align: 'right' },
      ], [
        { date: '9 May', type: 'Hourly', amount: '£96' },
        { date: '9 May', type: 'Commission', amount: '£24' },
        { date: '8 May', type: 'Tip', amount: '£12' },
      ]),
    ],
  }),
  createStaffPage({
    key: 'cleaner.settings',
    route: '/cleaner/settings',
    title: 'PWA settings',
    description: 'Offline cache, navigation apps, and security controls.',
    sections: [
      checklist('cleaner-settings-checklist', 'Device + app', [
        { label: 'Enable offline kit', detail: 'Download today’s route + guest notes.' },
        { label: 'Choose maps', detail: 'Default to Apple or Google when opening directions.' },
        { label: 'Biometric unlock', detail: 'Require FaceID before showing wallets.' },
      ]),
      insights('cleaner-settings-support', 'Support options', [
        { title: 'Reset kiosk binding', description: 'If tablet replaced, request new pairing code.' },
        { title: 'Report lost device', description: 'Notify manager to revoke tokens instantly.' },
      ]),
    ],
  }),
  createStaffPage({
    key: 'cleaner.offline',
    route: '/cleaner/offline',
    title: 'Offline kit',
    description: 'Operate even when connectivity drops.',
    sections: [
      summary('cleaner-offline-summary', 'Sync status', [
        { label: 'Cached guests', value: '14', delta: 'Auto refresh in Wi-Fi' },
        { label: 'Offline logs', value: '3', delta: 'Pending upload' },
      ]),
      checklist('cleaner-offline-steps', 'When offline', [
        { label: 'Log exposure manually', detail: 'System will reconcile once online.' },
        { label: 'Capture photo proof', detail: 'Stores locally until sync.' },
        { label: 'Use fallback PINs', detail: 'Members can still check in via offline PIN codes.' },
      ]),
    ],
  }),
]

export function registerCleanerPages() {
  staffPages.forEach(page => definePage(page))
}
