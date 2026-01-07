import { definePage } from '../registry'
import { summary, insights, dataTable, actionGrid, checklist, statusList, timeline } from '../helpers'

const ownerSummary = [
  { label: 'Studios live', value: '14', delta: '3 opening this quarter' },
  { label: 'Members', value: '2,502', delta: '+142 this month' },
  { label: 'Net revenue', value: '£612k', delta: '+18% QoQ' },
]

const createOwnerPage = spec => ({
  key: spec.key,
  route: spec.route,
  layout: 'workspace',
  role: 'owner',
  badge: 'Portfolio HQ',
  title: spec.title,
  description: spec.description,
  sections: [
    summary(`${spec.key}-summary`, 'Portfolio pulse', spec.summaryCards || ownerSummary),
    ...(spec.sections || []),
  ],
})

const ownerPages = [
  createOwnerPage({
    key: 'owner.overview',
    route: '/owner/overview',
    title: 'Portfolio overview',
    description: 'Studios, members, lamp uptime, churn risk, and marketing health.',
    sections: [
      statusList('owner-overview-status', 'Studios', [
        { label: 'London (3)', value: '98% occupancy', hint: 'Solar Club weekend', state: 'success' },
        { label: 'Manchester (2)', value: 'Lamp work tonight', hint: 'Budget allocated', state: 'warning' },
        { label: 'Birmingham (1)', value: 'Lease negotiations', hint: 'Decision due', state: 'info' },
      ]),
      actionGrid('owner-overview-actions', 'Exec actions', [
        { label: 'Approve CapEx', description: 'Sign lamp + scent upgrades instantly.', icon: 'bi-pencil-square' },
        { label: 'Review expansion', description: 'See pro forma for new studios.', icon: 'bi-building-add' },
        { label: 'Broadcast owner note', description: 'Share direction across all teams.', icon: 'bi-megaphone' },
      ]),
    ],
  }),
  createOwnerPage({
    key: 'owner.portfolio',
    route: '/owner/portfolio',
    title: 'Studios & leases',
    description: 'Track leases, design upgrades, and local partnerships.',
    sections: [
      dataTable('owner-portfolio-table', 'Studios', [
        { label: 'Studio', key: 'studio' },
        { label: 'Lease', key: 'lease' },
        { label: 'GM', key: 'gm' },
        { label: 'Next milestone', key: 'milestone' },
      ], [
        { studio: 'Mayfair Flagship', lease: 'Expires 2028', gm: 'Priya', milestone: 'Solar Club lounge refresh' },
        { studio: 'Manchester North', lease: 'Expires 2029', gm: 'Leo', milestone: 'Retail lab expansion' },
      ]),
      checklist('owner-portfolio-checks', 'Governance', [
        { label: 'Renewal reminders', detail: 'Auto ping 12/9/6 months out.' },
        { label: 'Local compliance', detail: 'City-specific health audit tasks auto-populate.' },
      ]),
    ],
  }),
  createOwnerPage({
    key: 'owner.performance',
    route: '/owner/performance',
    title: 'Performance & KPIs',
    description: 'Revenue, occupancy, attach rate, NPS, and campaign lift.',
    sections: [
      dataTable('owner-performance-table', 'KPIs', [
        { label: 'Metric', key: 'metric' },
        { label: 'Value', key: 'value' },
        { label: 'Target', key: 'target' },
        { label: 'Variance', key: 'variance', align: 'right' },
      ], [
        { metric: 'Occupancy', value: '94%', target: '92%', variance: '+2%' },
        { metric: 'Retail attach', value: '31%', target: '28%', variance: '+3%' },
        { metric: 'Net new members', value: '142', target: '120', variance: '+22' },
      ]),
      insights('owner-performance-insights', 'Signals', [
        { title: 'Churn drivers', description: 'Glow Pro members with idle wallets >21d.' },
        { title: 'Campaign lift', description: 'Hydration promo lifted lunchtime occupancy +14%.' },
      ]),
    ],
  }),
  createOwnerPage({
    key: 'owner.brand',
    route: '/owner/brand',
    title: 'Brand & identity',
    description: 'Fonts, palette, scent/lighting cues, and collateral for every studio.',
    sections: [
      actionGrid('owner-brand-actions', 'Brand controls', [
        { label: 'Update palette', description: 'Push new colors to kiosk, PWA, email.', icon: 'bi-palette' },
        { label: 'Media kit', description: 'Download logos, photography, playlists.', icon: 'bi-collection' },
        { label: 'Scent + lighting scenes', description: 'Program DMX + diffuser profiles centrally.', icon: 'bi-lamp' },
      ]),
      insights('owner-brand-guardrails', 'Guardrails', [
        { title: 'Creator usage', description: 'Time-box external logos + require approvals.' },
        { title: 'White label partners', description: 'Allow B2B pop-ups with partial Lux branding.' },
      ]),
    ],
  }),
  createOwnerPage({
    key: 'owner.experience',
    route: '/owner/experience',
    title: 'Experience toolkit',
    description: 'Aromas, playlists, hydration labs, and in-room rituals.',
    sections: [
      timeline('owner-experience-journey', 'Signature journey', [
        { title: 'Arrival', time: 'T-15', detail: 'Hydration shot + quick skin scan.', state: 'info' },
        { title: 'Glow', time: 'T+0', detail: 'Lamp cues + music + scent align automatically.', state: 'success' },
        { title: 'Aftercare', time: 'T+10', detail: 'Serum ritual + referral CTA on kiosk.', state: 'warning' },
      ]),
      actionGrid('owner-experience-actions', 'Iterate quickly', [
        { label: 'Clone ritual', description: 'Copy a signature experience to new studio.', icon: 'bi-copy' },
        { label: 'Pilot playlist', description: 'Beta playlists with selected members.', icon: 'bi-music-note' },
        { label: 'Measure CSAT', description: 'Correlate rituals with NPS + revenue.', icon: 'bi-emoji-laughing' },
      ]),
    ],
  }),
  createOwnerPage({
    key: 'owner.finance',
    route: '/owner/finance',
    title: 'Finance & treasury',
    description: 'Cash positions, payouts, debt, and investment budgets.',
    sections: [
      summary('owner-finance-stats', 'Cash & runway', [
        { label: 'Cash on hand', value: '£1.8m', delta: '+£220k vs last month' },
        { label: 'Stripe reserve', value: '£140k', delta: 'Clears in 7 days' },
        { label: 'CapEx pipeline', value: '£320k', delta: 'Lamps + new lounge' },
      ]),
      dataTable('owner-finance-ledger', 'Ledger highlights', [
        { label: 'Item', key: 'item' },
        { label: 'Amount', key: 'amount', align: 'right' },
        { label: 'Status', key: 'status' },
      ], [
        { item: 'Solar Club upgrade', amount: '£72k', status: 'Approved' },
        { item: 'Shoreditch rent', amount: '£41k', status: 'Paid' },
        { item: 'Manchester fit-out', amount: '£110k', status: 'Forecast' },
      ]),
    ],
  }),
  createOwnerPage({
    key: 'owner.security',
    route: '/owner/security',
    title: 'Security & compliance',
    description: 'MFA enforcement, audit log, data retention, and vendor posture.',
    sections: [
      statusList('owner-security-status', 'Controls', [
        { label: 'MFA coverage', value: '100% owner + finance', hint: 'SMS + TOTP', state: 'success' },
        { label: 'Vendor reviews', value: '2 overdue', hint: 'Klarna + courier', state: 'warning' },
        { label: 'Incident queue', value: '0 open', hint: 'Good to go', state: 'success' },
      ]),
      checklist('owner-security-checklist', 'Next actions', [
        { label: 'Rotate API keys', detail: 'Quarterly for POS + automation partners.' },
        { label: 'Purge expired consents', detail: 'Auto-run per GDPR.' },
      ]),
    ],
  }),
  createOwnerPage({
    key: 'owner.integrations',
    route: '/owner/integrations',
    title: 'Integrations & automation',
    description: 'POS, payroll, marketing, CRM, and compliance systems wired together.',
    sections: [
      dataTable('owner-integrations-table', 'Connected systems', [
        { label: 'Integration', key: 'integration' },
        { label: 'Scope', key: 'scope' },
        { label: 'Status', key: 'status' },
      ], [
        { integration: 'Stripe', scope: 'Payments + payouts', status: 'Healthy' },
        { integration: 'Klarna', scope: 'Installments', status: 'Healthy' },
        { integration: 'Shopify', scope: 'Retail', status: 'Syncing' },
      ]),
      actionGrid('owner-integrations-actions', 'Add more', [
        { label: 'New POS', description: 'Connect FOH kiosk or pop-up hardware.', icon: 'bi-tablet' },
        { label: 'Marketing API', description: 'Send segments to ESP, IG, TikTok.', icon: 'bi-share' },
        { label: 'Payroll', description: 'Sync hours + commission to Gusto/Deel.', icon: 'bi-cash-stack' },
      ]),
    ],
  }),
  createOwnerPage({
    key: 'owner.audit',
    route: '/owner/audit',
    title: 'Audit log & retention',
    description: 'Immutable record of every change across Lux OS.',
    sections: [
      dataTable('owner-audit-table', 'Recent events', [
        { label: 'When', key: 'when' },
        { label: 'Actor', key: 'actor' },
        { label: 'Action', key: 'action' },
        { label: 'Object', key: 'object' },
      ], [
        { when: '09:22', actor: 'Priya', action: 'Updated course pricing', object: 'Glow Pro 20' },
        { when: '08:50', actor: 'System', action: 'Revoked kiosk token', object: 'Shoreditch iPad' },
      ]),
      checklist('owner-audit-controls', 'Retention & exports', [
        { label: 'Retention policy', detail: 'Keep logs 7 years; auto-purge after.' },
        { label: 'External export', detail: 'Send encrypted bundle to auditors.' },
      ]),
    ],
  }),
]

export function registerOwnerPages() {
  ownerPages.forEach(page => definePage(page))
}
