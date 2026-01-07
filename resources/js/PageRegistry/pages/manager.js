import { definePage } from '../registry'
import { summary, insights, dataTable, actionGrid, timeline, statusList, checklist, mapPanel } from '../helpers'

const opsSummary = [
  { label: 'Occupancy today', value: '94%', delta: '2 rooms cooling' },
  { label: 'Waitlist', value: '36 guests', delta: 'Avg move 14m' },
  { label: 'Retail attach', value: '31%', delta: '+6 vs last week' },
]

const createManagerPage = spec => ({
  key: spec.key,
  route: spec.route,
  layout: 'workspace',
  role: 'manager',
  badge: 'Studio operations',
  title: spec.title,
  description: spec.description,
  sections: [
    summary(`${spec.key}-summary`, 'Operational pulse', spec.summaryCards || opsSummary),
    ...(spec.sections || []),
  ],
})

const managerPages = [
  createManagerPage({
    key: 'manager.overview',
    route: '/manager/overview',
    title: 'Studio overview',
    description: 'Occupancy, lamp health, campaigns, and staff load in one glance.',
    sections: [
      actionGrid('manager-overview-actions', 'Immediate actions', [
        { label: 'Open slot broadcast', description: 'Notify waitlisted guests automatically.', icon: 'bi-broadcast-pin' },
        { label: 'Escalate lamp issue', description: 'Ping owner + maintenance with lamp data.', icon: 'bi-exclamation-triangle' },
        { label: 'Drop new promo', description: 'Push hydration booster promo for off-peak hours.', icon: 'bi-megaphone' },
      ]),
      statusList('manager-overview-status', 'Studios', [
        { label: 'Mayfair', value: '98% full', hint: 'Solar Club night', state: 'success' },
        { label: 'Shoreditch', value: 'Slots at 15:30', hint: 'Push waitlist', state: 'warning' },
        { label: 'Manchester', value: 'Lamp service 18:00', hint: 'Room 4 offline', state: 'danger' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.calendar',
    route: '/manager/calendar',
    title: 'Calendar & drag board',
    description: 'Drag-and-drop bookings, view lamp cooldown buffers, and auto-text guests when moving them.',
    sections: [
      timeline('manager-calendar-flow', 'Today’s flow', [
        { title: 'Dawn kit', time: '06:00', detail: 'Prep + cleaning due', state: 'info' },
        { title: 'Event block', time: '12:30', detail: 'Team from Neon Agency', state: 'warning' },
        { title: 'Lamp maintenance', time: '18:00', detail: 'Room 4 offline 20m', state: 'danger' },
      ]),
      actionGrid('manager-calendar-actions', 'Board controls', [
        { label: 'Flex slot', description: 'Expand or shrink exposures from board view.', icon: 'bi-arrows-expand' },
        { label: 'Bulk move', description: 'Select multiple bookings and shift in one action.', icon: 'bi-shuffle' },
        { label: 'Lock room', description: 'Prevent drag if lamp warming.', icon: 'bi-lock' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.waitlist',
    route: '/manager/waitlist',
    title: 'Waitlist intelligence',
    description: 'Rank guests by membership, tone goal, and predicted show-up probability.',
    sections: [
      dataTable('manager-waitlist-table', 'Active waitlists', [
        { label: 'Guest', key: 'guest' },
        { label: 'Studio', key: 'studio' },
        { label: 'Priority', key: 'priority' },
        { label: 'ETA', key: 'eta', align: 'right' },
      ], [
        { guest: 'Jade Lee', studio: 'Shoreditch', priority: 'Solar Club · VIP', eta: '10m' },
        { guest: 'Mia Ford', studio: 'Mayfair', priority: 'Glow Pro', eta: '15m' },
      ]),
      insights('manager-waitlist-logic', 'Automation rules', [
        { title: 'Health guardrails', description: 'Only auto-fill if cooldown met + lamp temperature < threshold.' },
        { title: 'Membership weighting', description: 'Solar Club > Glow Pro > Dawn, but upcoming birthdays override.' },
        { title: 'No-shows', description: 'Three strikes auto-downgrades priority until reviewed.' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.multiroom',
    route: '/manager/multiroom',
    title: 'Multi-room monitor',
    description: 'Live map of every bed, lamp, and staff assignment.',
    sections: [
      mapPanel('manager-multiroom-map', 'Studios map', [
        { title: 'Mayfair', detail: 'Occupancy 98%', lat: 51.511, lng: -0.143, state: 'success' },
        { title: 'Shoreditch', detail: 'Room 2 cooling', lat: 51.526, lng: -0.078, state: 'warning' },
        { title: 'Manchester', detail: 'Room 4 offline', lat: 53.484, lng: -2.242, state: 'danger' },
      ], 'Click through to drill into rooms.'),
      statusList('manager-multiroom-rooms', 'Room status', [
        { label: 'Room 1', value: 'Guest inside', hint: '5m remaining', state: 'success' },
        { label: 'Room 2', value: 'Cooling', hint: 'Ready in 4m', state: 'warning' },
        { label: 'Room 4', value: 'Maintenance', hint: 'Lamp swap scheduled', state: 'danger' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.courses',
    route: '/manager/courses',
    title: 'Course designer',
    description: 'Launch new bundles with exposure curves, pricing, and availability controls.',
    sections: [
      dataTable('manager-courses-table', 'Active courses', [
        { label: 'Course', key: 'course' },
        { label: 'Sessions', key: 'sessions' },
        { label: 'Price', key: 'price', align: 'right' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { course: 'Dawn Reset', sessions: '4', price: '£89', status: 'Live' },
        { course: 'Glow Pro 20', sessions: '8', price: '£169', status: 'Live' },
        { course: 'Solar Club', sessions: 'Unlimited', price: '£289', status: 'Waitlist' },
      ]),
      actionGrid('manager-courses-actions', 'Builder steps', [
        { label: 'Exposure curve', description: 'Define safe start/end minutes by skin tone.', icon: 'bi-activity' },
        { label: 'Bundles & perks', description: 'Attach hydration kit, playlists, scent packages.', icon: 'bi-bag-heart' },
        { label: 'Eligibility', description: 'Gate by membership, studio, or certification.', icon: 'bi-shield-check' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.bundles',
    route: '/manager/bundles',
    title: 'Product bundles & promotions',
    description: 'Mix retail, boosters, and courses into shoppable kits.',
    sections: [
      insights('manager-bundles-insights', 'Featured bundles', [
        { title: 'Hydration Lab', description: 'Serum + electrolyte kit auto-suggested for Dawn.' },
        { title: 'Creator Day', description: 'Guest pass + recorded content package with MUA.' },
      ]),
      checklist('manager-bundles-checklist', 'Launch requirements', [
        { label: 'Inventory par levels', detail: 'Ensure stock across studios before pushing live.' },
        { label: 'Commission split', detail: 'Set who earns what (staff vs HQ).' },
        { label: 'Tracking tags', detail: 'Tag campaign for analytics + payouts.' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.stock',
    route: '/manager/stock',
    title: 'Stock & lamp supplies',
    description: 'Real-time inventory, restock alerts, and vendor POs.',
    sections: [
      dataTable('manager-stock-table', 'Critical stock', [
        { label: 'Item', key: 'item' },
        { label: 'Location', key: 'location' },
        { label: 'On hand', key: 'qty', align: 'right' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { item: 'Hydration booster kits', location: 'Mayfair', qty: '23', status: 'Order pending' },
        { item: 'Lamp set LUX-220', location: 'All', qty: '12', status: 'Healthy' },
      ]),
      actionGrid('manager-stock-actions', 'Ops', [
        { label: 'Generate PO', description: 'Send vendor-ready PDF with lamp usage forecast.', icon: 'bi-receipt-cutoff' },
        { label: 'Transfer stock', description: 'Move inventory between studios with courier tracking.', icon: 'bi-arrow-left-right' },
        { label: 'Set par levels', description: 'Alert when boosters drop below threshold.', icon: 'bi-bell' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.compliance',
    route: '/manager/compliance',
    title: 'Compliance & health logs',
    description: 'Contraindication attestations, lamp audits, and regulator exports.',
    sections: [
      statusList('manager-compliance-items', 'Checklist', [
        { label: 'Consent forms', value: '100% captured', hint: 'Digital signatures per session', state: 'success' },
        { label: 'Lamp inspections', value: 'Room 4 overdue', hint: 'Swap scheduled', state: 'danger' },
        { label: 'Age verification', value: 'Auto via ID scan', hint: '24 flagged last month', state: 'warning' },
      ]),
      actionGrid('manager-compliance-actions', 'Exports & workflows', [
        { label: 'Generate exposure log', description: 'Send daily CSV to regulators.', icon: 'bi-file-earmark-spreadsheet' },
        { label: 'Audit trail', description: 'Timeline of overrides + approvals.', icon: 'bi-clock-history' },
        { label: 'Health alerts', description: 'Auto-notify when meds flagged for members.', icon: 'bi-heart-pulse' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.customers',
    route: '/manager/customers',
    title: 'Customer intelligence',
    description: 'Segment guests by plan, spend, and engagement.',
    sections: [
      dataTable('manager-customers-table', 'Members', [
        { label: 'Member', key: 'member' },
        { label: 'Plan', key: 'plan' },
        { label: 'Minutes left', key: 'minutes', align: 'right' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { member: 'Ava Kim', plan: 'Glow Pro', minutes: '82', status: 'Engaged' },
        { member: 'Marcus Rao', plan: 'Dawn', minutes: '12', status: 'Top-up due' },
      ]),
      insights('manager-customers-actions', 'Playbooks', [
        { title: 'Early upsell', description: 'Offer Solar Club to high CSAT + high usage members.' },
        { title: 'Save from churn', description: 'Auto-nudge when wallet idle for 21 days.' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.membership',
    route: '/manager/membership',
    title: 'Membership controls',
    description: 'Approve upgrades, manage perks, and view churn risk.',
    sections: [
      summary('manager-membership-stats', 'Membership numbers', [
        { label: 'Solar Club', value: '248', delta: '+12 MoM' },
        { label: 'Glow Pro', value: '1,642', delta: '+81' },
        { label: 'Dawn', value: '612', delta: '-5' },
      ]),
      actionGrid('manager-membership-actions', 'Admin actions', [
        { label: 'Approve upgrade', description: 'Review Solar Club waitlist apps.', icon: 'bi-check-circle' },
        { label: 'Pause request', description: 'Pause membership for travel/health with schedule.', icon: 'bi-pause-circle' },
        { label: 'Edit perks', description: 'Swap guest passes, refresh kits.', icon: 'bi-stars' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.marketing',
    route: '/manager/marketing',
    title: 'Marketing & campaigns',
    description: 'Broadcast promos, collaborations, and referral pushes.',
    sections: [
      insights('manager-marketing-campaigns', 'Active campaigns', [
        { title: 'Hydration May', description: '10% off boosters for midday slots.' },
        { title: 'Creator Residency', description: 'Influencer takeover with referral codes.' },
      ]),
      actionGrid('manager-marketing-actions', 'Launch pads', [
        { label: 'Segment push', description: 'Target by plan, waitlist, or minutes usage.', icon: 'bi-funnel' },
        { label: 'Collab hub', description: 'Manage brand & creator partnerships.', icon: 'bi-handshake' },
        { label: 'Measure impact', description: 'Realtime uplift tracked inside dashboard.', icon: 'bi-graph-up' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.staff',
    route: '/manager/staff',
    title: 'Staff roster & performance',
    description: 'Shift assignments, goals, and recognition all visible.',
    sections: [
      dataTable('manager-staff-table', 'Team roster', [
        { label: 'Guide', key: 'guide' },
        { label: 'Role', key: 'role' },
        { label: 'Shift', key: 'shift' },
        { label: 'Add-ons', key: 'addons', align: 'right' },
      ], [
        { guide: 'Maya', role: 'Lead', shift: '06:00–14:00', addons: '£180' },
        { guide: 'Leo', role: 'Guide', shift: '10:00–18:00', addons: '£95' },
      ]),
      actionGrid('manager-staff-actions', 'People ops', [
        { label: 'Assign mentor', description: 'Pair new hires with leads.', icon: 'bi-people' },
        { label: 'Recognise stars', description: 'Auto-notify owner when goals hit.', icon: 'bi-award' },
        { label: 'Shift swap', description: 'Approve swap with compliance checks.', icon: 'bi-arrow-left-right' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.schedules',
    route: '/manager/schedules',
    title: 'Schedules & staffing',
    description: 'Build schedules with lamp coverage, labour budgets, and skill tags.',
    sections: [
      timeline('manager-schedules-timeline', 'Week view', [
        { title: 'Mon – Prep day', time: 'Staff 4', detail: 'Lamp swaps + cleaning', state: 'info' },
        { title: 'Wed – Creator takeover', time: 'Staff 6', detail: 'Extra concierge needed', state: 'warning' },
        { title: 'Sat – Solar Club night', time: 'Staff 8', detail: 'All hands, triple-check stock', state: 'danger' },
      ]),
      checklist('manager-schedules-checks', 'Before publishing', [
        { label: 'Coverage vs bookings', detail: 'Ensure each slot has coverage + cooldown buffer.' },
        { label: 'Skill mix', detail: 'At least one Lead + one Glow Guide per shift.' },
        { label: 'Overtime alerts', detail: 'Auto-warn when hitting thresholds.' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.settlements',
    route: '/manager/settlements',
    title: 'Settlements & payouts',
    description: 'Track sales, refunds, payroll, and vendor payouts.',
    sections: [
      summary('manager-settlement-stats', 'Today', [
        { label: 'Sales', value: '£18.4k', delta: '+12% vs last Tue' },
        { label: 'Refunds', value: '£340', delta: '2 exposures cut short' },
        { label: 'Tips', value: '£610', delta: '+9%' },
      ]),
      dataTable('manager-settlement-table', 'Ledger', [
        { label: 'Type', key: 'type' },
        { label: 'Amount', key: 'amount', align: 'right' },
        { label: 'Status', key: 'status' },
      ], [
        { type: 'Stripe payout', amount: '£14.2k', status: 'Initiated' },
        { type: 'Klarna settlement', amount: '£2.1k', status: 'Pending' },
        { type: 'Vendor – Lamps', amount: '£850', status: 'Scheduled' },
      ]),
    ],
  }),
  createManagerPage({
    key: 'manager.settings',
    route: '/manager/settings',
    title: 'Studio settings',
    description: 'Policies, thresholds, notifications, and integrations.',
    sections: [
      actionGrid('manager-settings-actions', 'Configure', [
        { label: 'Exposure policies', description: 'Define cooldown, minutes caps, consent intervals.', icon: 'bi-shield-lock' },
        { label: 'Notifications', description: 'Who gets SMS vs push vs email for each event.', icon: 'bi-bell' },
        { label: 'Integrations', description: 'Connect POS, payroll, marketing tools.', icon: 'bi-plug' },
      ]),
      insights('manager-settings-brand', 'Branding touches', [
        { title: 'Playlists', description: 'Upload new curated sounds to the kiosks.' },
        { title: 'Scent + lighting', description: 'Control DMX + diffuser scenes per session type.' },
      ]),
    ],
  }),
]

export function registerManagerPages() {
  managerPages.forEach(page => definePage(page))
}
