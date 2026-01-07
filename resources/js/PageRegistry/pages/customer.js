import { definePage } from '../registry'
import { summary, insights, dataTable, actionGrid, timeline, checklist, statusList } from '../helpers'

const walletSummary = [
  { label: 'Minutes remaining', value: '82', delta: 'Glow Pro wallet' },
  { label: 'Next booking', value: 'Thu · 18:20', delta: 'Mayfair · Room 3' },
  { label: 'Waitlist position', value: '#3', delta: 'Shoreditch Loft' },
]

const createCustomerPage = spec => ({
  key: spec.key,
  route: spec.route,
  layout: 'workspace',
  role: 'customer',
  badge: 'Member portal',
  title: spec.title,
  description: spec.description,
  sections: [
    ...(spec.skipSummary ? [] : [summary(`${spec.key}-summary`, 'Glow snapshot', spec.summaryCards || walletSummary)]),
    ...(spec.sections || []),
  ],
})

const customerPages = [
  createCustomerPage({
    key: 'customer.dashboard',
    route: '/customer/dashboard',
    title: 'Your glow plan',
    description: 'Track minutes, upcoming sessions, recommendations, and studio updates at a glance.',
    sections: [
      actionGrid('customer-dash-actions', 'Quick actions', [
        { label: 'Book a session', description: 'Pick a time in any studio.', icon: 'bi-lightning-charge', href: '/book' },
        { label: 'Share guest pass', description: 'Invite a friend with remaining minutes.', icon: 'bi-send' },
        { label: 'Chat with Glow Guide', description: 'Concierge replies within 4 min.', icon: 'bi-chat-dots' },
      ]),
      timeline('customer-dash-timeline', 'Upcoming timeline', [
        { title: 'Glow Pro — Room 3', time: 'Thu · 18:20', detail: 'Hydration kit ready · Playlist “Amber Drift”', state: 'success' },
        { title: 'Waitlist — Shoreditch', time: 'Fri · Flex', detail: 'You are #3, move up automatically if a spot opens.', state: 'info' },
        { title: 'Lamp lab invite', time: 'Sat · 11:00', detail: 'Solar Club masterclass + product drop.', state: 'warning' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.minutes',
    route: '/customer/minutes',
    title: 'Minutes wallet',
    description: 'All top-ups, exposure logs, and pause controls live here.',
    sections: [
      dataTable('customer-minutes-ledger', 'Wallet ledger', [
        { label: 'Event', key: 'event' },
        { label: 'Minutes', key: 'minutes', align: 'right' },
        { label: 'Studio', key: 'studio' },
        { label: 'Balance', key: 'balance', align: 'right' },
      ], [
        { event: 'Session · 9 May', minutes: '-18', studio: 'Mayfair · Room 2', balance: '82' },
        { event: 'Top-up · Glow Pro', minutes: '+160', studio: 'Online', balance: '100' },
      ]),
      checklist('customer-minutes-controls', 'Controls', [
        { label: 'Pause wallet', detail: 'Freeze usage for up to 45 days.' },
        { label: 'Transfer minutes', detail: 'Gift minutes to another member with approval.' },
        { label: 'Request refund', detail: 'Trigger review if a session was cut short.' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.courses',
    route: '/customer/courses',
    title: 'Courses & recommendations',
    description: 'Upgrade, mix, or add boosters depending on your goals.',
    sections: [
      insights('customer-courses-options', 'Course library', [
        { title: 'Dawn Reset', description: '4 sessions · hydration-focused plan for events.' },
        { title: 'Glow Pro 20', description: '8 sessions · rollover + concierge perks.' },
        { title: 'Solar Club', description: 'Unlimited with lamp labs and guest passes.' },
      ]),
      actionGrid('customer-courses-actions', 'Recommended next moves', [
        { label: 'Upgrade to Glow Pro', description: 'Instantly adds 160 minutes.', icon: 'bi-arrow-up-right' },
        { label: 'Add hydration boosters', description: 'Pair with your next booking.', icon: 'bi-droplet-half' },
        { label: 'Share referral', description: 'Earn 20 minutes when friends join.', icon: 'bi-gift' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.bookings',
    route: '/customer/bookings',
    title: 'Bookings & waitlists',
    description: 'Manage upcoming sessions, move times, or cancel with clear policies.',
    sections: [
      dataTable('customer-bookings-table', 'Your sessions', [
        { label: 'When', key: 'when' },
        { label: 'Studio', key: 'studio' },
        { label: 'Room', key: 'room' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { when: 'Thu · 18:20', studio: 'Mayfair', room: 'Lux 3', status: 'Confirmed' },
        { when: 'Fri · Flex', studio: 'Shoreditch', room: 'Lux 2', status: 'Waitlisted (#3)' },
      ]),
      statusList('customer-bookings-policy', 'Policy reminders', [
        { label: 'Cancellations', value: 'Free up to 6h', hint: 'Wallet auto-refills', state: 'info' },
        { label: 'Late arrival', value: '10 min grace', hint: 'Then waitlist engages', state: 'warning' },
        { label: 'Health flag', value: 'Contact concierge', hint: 'If medication changes', state: 'danger' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.membership',
    route: '/customer/membership',
    title: 'Membership controls',
    description: 'Swap plans, pause, or manage Solar Club perks.',
    sections: [
      summary('customer-membership-plan', 'Current plan', [
        { label: 'Plan', value: 'Glow Pro 20', delta: 'Renews 1 June' },
        { label: 'Wallet rollover', value: '30 days', delta: 'Auto applies' },
        { label: 'Guest passes', value: '2 left', delta: 'Refreshes quarterly' },
      ]),
      actionGrid('customer-membership-actions', 'Manage', [
        { label: 'Pause membership', description: 'Freeze for travel or health.', icon: 'bi-pause-circle' },
        { label: 'Upgrade to Solar Club', description: 'Unlock unlimited minutes.', icon: 'bi-stars' },
        { label: 'Update perks', description: 'Switch playlists, scents, hydration kit.', icon: 'bi-sliders' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.payments',
    route: '/customer/payments',
    title: 'Billing & receipts',
    description: 'Cards on file, Klarna schedules, and downloadable receipts.',
    sections: [
      dataTable('customer-payments-methods', 'Payment methods', [
        { label: 'Card', key: 'card' },
        { label: 'Type', key: 'type' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { card: 'Amex •••• 9004', type: 'Primary', status: 'Default' },
        { card: 'Klarna Pay in 3', type: 'Installment', status: 'Active' },
      ]),
      dataTable('customer-payments-invoices', 'Recent invoices', [
        { label: 'Invoice', key: 'invoice' },
        { label: 'Amount', key: 'amount', align: 'right' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { invoice: '#INV-2481', amount: '£169', status: 'Paid' },
        { invoice: '#INV-2475', amount: '£42', status: 'Refunded' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.preferences',
    route: '/customer/preferences',
    title: 'Experience preferences',
    description: 'Tell the studio exactly how you like to glow.',
    sections: [
      checklist('customer-pref-checklist', 'Session settings', [
        { label: 'Music palette', detail: 'Amber Drift · Vinyasa Flow · Off' },
        { label: 'Scent + aromatherapy', detail: 'Citrus dawn with eucalyptus' },
        { label: 'Hydration reminders', detail: 'SMS 30 min before + after' },
        { label: 'Contraindications', detail: 'Notes on skin care, medication, or allergies' },
      ]),
      insights('customer-pref-sharing', 'Sharing controls', [
        { title: 'Data with partners', description: 'Toggle which wellness partners can see your history.' },
        { title: 'Creator mode', description: 'Allow studio to tag you when sharing content.' },
        { title: 'Notifications', description: 'Choose push vs SMS for updates + promos.' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.documents',
    route: '/customer/documents',
    title: 'Consents & documents',
    description: 'Download waivers, lamp logs, and receipts in one vault.',
    sections: [
      dataTable('customer-docs-table', 'Files', [
        { label: 'Document', key: 'document' },
        { label: 'Updated', key: 'updated' },
        { label: 'Action', key: 'action', align: 'right' },
      ], [
        { document: 'Health consent', updated: '9 May 2025', action: 'Download' },
        { document: 'Lamp exposure log', updated: '9 May 2025', action: 'Download' },
        { document: 'Invoice #INV-2481', updated: '8 May 2025', action: 'Download' },
      ]),
    ],
  }),
  createCustomerPage({
    key: 'customer.support',
    route: '/customer/support',
    title: 'Get support',
    description: 'Message your Glow Guide, view open tickets, or book a call.',
    skipSummary: true,
    sections: [
      actionGrid('customer-support-actions', 'Support channels', [
        { label: 'Chat concierge', description: 'Replies in under 4 minutes.', icon: 'bi-chat-dots' },
        { label: 'Schedule call', description: 'Pick a time with the studio lead.', icon: 'bi-telephone' },
        { label: 'View tickets', description: 'Track open questions or incidents.', icon: 'bi-inbox' },
      ]),
      insights('customer-support-faq', 'Popular questions', [
        { title: 'How do I pause my wallet?', description: 'Use the Minutes tab, tap “Pause wallet”, choose a resume date.' },
        { title: 'Can I bring a friend?', description: 'Glow Pro + Solar Club include guest passes — share from Dashboard.' },
        { title: 'Refund timelines', description: 'Wallet credits apply instantly; card refunds take 3-5 business days.' },
      ]),
    ],
  }),
]

export function registerCustomerPages() {
  customerPages.forEach(page => definePage(page))
}
