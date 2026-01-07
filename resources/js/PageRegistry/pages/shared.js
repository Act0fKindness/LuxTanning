import { definePage } from '../registry'
import { summary, insights, dataTable, actionGrid, statusList } from '../helpers'

const sharedPages = [
  {
    key: 'shared.me',
    route: '/me',
    layout: 'workspace',
    role: 'shared',
    badge: 'Glow profile',
    title: 'Manage your Lux identity, notifications, and devices.',
    description: 'Every role — from member to Glow Guide — keeps language, consent, and device security tidy from here.',
    sections: [
      summary('shared-me-summary', 'Account snapshot', [
        { label: 'Role', value: '{{role || "multi-role"}}', delta: 'Auto derived from membership + staff invites' },
        { label: 'Active studios', value: 'Mayfair · Shoreditch', delta: 'Tap to switch default' },
        { label: 'Trusted devices', value: '3', delta: 'Last new login 2 min ago' },
      ]),
      actionGrid('shared-me-actions', 'Quick actions', [
        { label: 'Update photo & pronouns', description: 'Show up the way you prefer on staff boards.', icon: 'bi-person-badge' },
        { label: 'Notification mix', description: 'Choose SMS, push, or email per alert (minutes, promos, incidents).', icon: 'bi-bell' },
        { label: 'Manage magic links', description: 'Revoke, resend, or generate guest passes instantly.', icon: 'bi-link-45deg' },
      ]),
      insights('shared-me-security', 'Security recommendations', [
        { title: 'Biometric unlock', description: 'Require Face ID on kiosks before showing wallets.' },
        { title: 'Device posture', description: 'Force re-auth on rooted devices or old browsers.' },
        { title: 'Data portability', description: 'Export personal data for GDPR within 72h.' },
      ]),
    ],
  },
  {
    key: 'shared.help',
    route: '/help',
    layout: 'public',
    role: 'guest',
    tenantFacing: true,
    badge: 'Help centre',
    title: 'Find answers fast or chat with a Glow Guide.',
    description: 'Member FAQs, staff playbooks, compliance checklists, and concierge contact routes in one searchable hub.',
    sections: [
      actionGrid('shared-help-actions', 'How can we help?', [
        { label: 'Member FAQs', description: 'Waitlists, wallets, and aftercare guides.', icon: 'bi-journal-richtext' },
        { label: 'Studio operations', description: 'Shift rituals, lamp swaps, compliance.', icon: 'bi-person-workspace' },
        { label: 'Talk to Lux', description: 'Live chat, SMS, or phone with SLA badges.', icon: 'bi-life-preserver' },
      ]),
      dataTable('shared-help-articles', 'Featured articles', [
        { label: 'Article', key: 'article' },
        { label: 'Audience', key: 'audience' },
        { label: 'Updated', key: 'updated', align: 'right' },
      ], [
        { article: 'How minutes wallets roll over', audience: 'Members', updated: '12 May 2025' },
        { article: 'Lamp cooldown policy', audience: 'Staff', updated: '5 May 2025' },
        { article: 'Handling VAT on gift cards', audience: 'Finance', updated: '28 Apr 2025' },
      ]),
      insights('shared-help-escalation', 'Escalation paths', [
        { title: 'Health & safety', description: 'Flag contraindications to on-call managers instantly.' },
        { title: 'Billing', description: 'Route Stripe disputes and Klarna holds to the finance queue with context.' },
        { title: 'Accessibility', description: 'Dedicated concierge team for mobility + sensory accommodations.' },
      ]),
    ],
  },
  {
    key: 'shared.status',
    route: '/status',
    layout: 'public',
    role: 'guest',
    tenantFacing: true,
    badge: 'Status board',
    title: 'Realtime Lux platform health.',
    description: 'Studios rely on booking, lamp telemetry, notifications, and payments staying green. Here is the live feed.',
    sections: [
      statusList('shared-status-components', 'Component status', [
        { label: 'Booking + wallet', value: 'Operational', hint: 'All regions', state: 'success' },
        { label: 'Studio dashboards', value: 'Minor delays', hint: 'Timeline refresh every 3 min', state: 'warning' },
        { label: 'SMS + push', value: 'Investigating', hint: 'Carrier throughput in EU', state: 'danger' },
      ]),
      insights('shared-status-incidents', 'Active incidents', [
        { title: 'SMS queue congestion', description: 'Carrier rate limits causing up to 4m delays. Routing overflow to WhatsApp + email.' },
        { title: 'Lamp telemetry lag', description: 'Edge collectors behind in Manchester; fallback to manual checks triggered.' },
      ]),
      dataTable('shared-status-maint', 'Recent maintenance', [
        { label: 'Window', key: 'window' },
        { label: 'Impact', key: 'impact' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { window: 'May 09 · 01:00 UTC', impact: 'Payments provider upgrade', status: 'Complete' },
        { window: 'Apr 21 · 23:30 UTC', impact: 'Lamp telemetry patch', status: 'Complete' },
      ]),
    ],
  },
  {
    key: 'shared.privacy',
    route: '/privacy',
    layout: 'public',
    role: 'guest',
    tenantFacing: true,
    badge: 'Policy',
    title: 'Privacy & data handling at Lux.',
    description: 'We explain what information we capture, why we capture it, and how to exercise your rights.',
    sections: [
      insights('shared-privacy-scope', 'What we collect', [
        { title: 'Member data', description: 'Contact info, course history, health consents, device metadata.' },
        { title: 'Studio data', description: 'Lamp metrics, staff notes, inventory counts.' },
        { title: 'Payments', description: 'Tokenised references via Stripe/Klarna; never store raw card numbers.' },
      ]),
      insights('shared-privacy-rights', 'Your rights', [
        { title: 'Access & portability', description: 'Request exports from /me or privacy@luxtanning.com.' },
        { title: 'Deletion', description: 'Close accounts anytime; backups purge within 30 days.' },
        { title: 'Marketing choices', description: 'Granular opt-in/out for campaigns and Glow Guide messages.' },
      ]),
    ],
  },
  {
    key: 'shared.terms',
    route: '/terms',
    layout: 'public',
    role: 'guest',
    tenantFacing: true,
    badge: 'Policy',
    title: 'Lux Tanning terms of service.',
    description: 'The legal bits covering memberships, cancellations, retail, and liability.',
    sections: [
      insights('shared-terms-highlights', 'Highlights', [
        { title: 'Membership renewals', description: 'Courses renew automatically until cancelled; pause anytime inside /customer/membership.' },
        { title: 'Cancellations', description: 'Cancel or move bookings up to 6 hours prior without penalty.' },
        { title: 'Contraindications', description: 'False disclosures void refunds for safety reasons.' },
      ]),
      insights('shared-terms-disputes', 'Dispute handling', [
        { title: 'Chargebacks', description: 'Finance team receives evidence packets automatically when disputes arise.' },
        { title: 'Incident credits', description: 'Guests receive wallet credits within 2h when service misses SLAs.' },
      ]),
    ],
  },
  {
    key: 'shared.404',
    route: '/404',
    layout: 'public',
    role: 'guest',
    tenantFacing: true,
    badge: 'Error page',
    title: 'The glow you’re after is not here.',
    description: 'We could not find that page. Jump back into the Lux experience below.',
    actions: [
      { label: 'Visit homepage', href: '/', variant: 'primary', icon: 'bi-house-door' },
      { label: 'Open help centre', href: '/help', variant: 'ghost', icon: 'bi-question-circle' },
    ],
    sections: [
      actionGrid('shared-404-links', 'Popular destinations', [
        { label: 'Book a sun bed', description: 'Start a new booking in seconds.', icon: 'bi-lightning-charge', href: '/book' },
        { label: 'Track my minutes', description: 'Jump into the member portal.', icon: 'bi-hourglass-split', href: '/customer/dashboard' },
        { label: 'Tour a studio', description: 'See locations + waitlists.', icon: 'bi-geo-alt', href: '/locations' },
      ]),
    ],
  },
  {
    key: 'shared.403',
    route: '/403',
    layout: 'public',
    role: 'guest',
    tenantFacing: true,
    badge: 'Error page',
    title: 'Access not allowed.',
    description: 'This link requires a different Lux role or studio permission.',
    sections: [
      insights('shared-403-remedy', 'Try this', [
        { title: 'Switch studio', description: 'Glow Guides assigned to multiple studios must pick the right one at login.' },
        { title: 'Request access', description: 'Owners can grant permissions inside /owner/roles.' },
        { title: 'Use the right email', description: 'Minutes wallets stick to the email you registered with.' },
      ]),
    ],
  },
  {
    key: 'shared.500',
    route: '/500',
    layout: 'public',
    role: 'guest',
    tenantFacing: true,
    badge: 'Error page',
    title: 'Lux hit a snag.',
    description: 'If this keeps happening, send us the incident ID so we can chase it down.',
    sections: [
      summary('shared-500-meta', 'Incident details', [
        { label: 'Incident ID', value: '#LUX-248', delta: 'Copied to clipboard' },
        { label: 'Heartbeat', value: 'All green', delta: 'See /status' },
      ]),
      actionGrid('shared-500-actions', 'Keep moving', [
        { label: 'Retry action', description: 'Give it another go.', icon: 'bi-arrow-clockwise' },
        { label: 'Open chat', description: 'Ping Lux Concierge with the ID.', icon: 'bi-chat-dots' },
        { label: 'Visit status', description: 'Check if we already flagged it.', icon: 'bi-activity', href: '/status' },
      ]),
    ],
  },
]

export function registerSharedPages() {
  sharedPages.forEach(page => definePage(page))
}
