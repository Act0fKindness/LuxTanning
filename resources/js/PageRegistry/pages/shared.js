import { definePage } from '../registry'
import { summary, insights, dataTable, actionGrid, statusList } from '../helpers'

const sharedPages = [
  {
    key: 'shared.me',
    route: '/me',
    layout: 'workspace',
    role: 'shared',
    badge: 'Profile & preferences',
    title: 'Manage your profile, language, and notifications in one place.',
    description: 'Supports every role; surfaces session history, magic-link devices, and language/timezone preferences.',
    sections: [
      summary('shared-me-summary', 'Account snapshot', [
        { label: 'Role', value: '{{role || "multi-role"}}', delta: 'Derived from membership' },
        { label: 'Notification channels', value: 'Email · SMS · Push', delta: 'Toggle per event' },
        { label: 'Active sessions', value: '3 devices', delta: 'Last refresh 2 mins ago' },
      ]),
      actionGrid('shared-me-actions', 'Quick actions', [
        { label: 'Change language', description: 'Localise UI + comms instantly.', icon: 'bi-translate' },
        { label: 'Manage magic links', description: 'Revoke or resend existing links.', icon: 'bi-link-45deg' },
        { label: 'Review devices', description: 'Kill old sessions or require sign-in.', icon: 'bi-phone' },
      ]),
      insights('shared-me-policies', 'Security recommendations', [
        { title: 'Session expiry', description: 'Cleaner PWAs expire nightly; managers/owners can opt into 12h idle timeout.' },
        { title: 'Notifications', description: 'Mute non-critical push messages but keep incident alerts on for compliance.' },
        { title: 'Data export', description: 'Download personal data to stay GDPR compliant.' },
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
    title: 'FAQs, quick-start guides, and ways to reach support.',
    description: 'Searchable knowledge base grouped by role with linked contact methods when self-serve fails.',
    sections: [
      actionGrid('shared-help-actions', 'Get help fast', [
        { label: 'FAQs', description: 'Top questions for customers + cleaners.', icon: 'bi-question-circle' },
        { label: 'Contact support', description: 'Email, live chat, or phone with SLA badges.', icon: 'bi-headset' },
        { label: 'Report an incident', description: 'Escalate urgent cleaning issues straight to managers.', icon: 'bi-exclamation-diamond' },
      ]),
      dataTable('shared-help-faqs', 'Featured articles', [
        { label: 'Article', key: 'article' },
        { label: 'Audience', key: 'audience' },
        { label: 'Updated', key: 'updated', align: 'right' },
      ], [
        { article: 'How do magic links work?', audience: 'All roles', updated: '1 May 2025' },
        { article: 'Cleaner route troubleshooting', audience: 'Cleaner', updated: '27 Apr 2025' },
        { article: 'Stripe billing changes', audience: 'Owner + Accountant', updated: '18 Apr 2025' },
      ]),
      insights('shared-help-escalation', 'Escalation paths', [
        { title: 'Ops urgent', description: 'Phone hotline with incident tag to wake the on-call manager.' },
        { title: 'Billing questions', description: 'Accountants route to finance queue with invoice context.' },
        { title: 'Accessibility support', description: 'Dedicated email for customers requiring adjustments.' },
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
    title: 'System status & incident history.',
    description: 'Real-time uptime per surface (booking, dispatch, payments, notifications) plus incident timeline.',
    sections: [
      statusList('shared-status-components', 'Component status', [
        { label: 'Booking flow', value: 'Operational', hint: 'All regions', state: 'success' },
        { label: 'Dispatch board', value: 'Degraded', hint: 'Slow drag events', state: 'warning' },
        { label: 'Notifications', value: 'Partial outage', hint: 'SMS delays with Twilio', state: 'danger' },
      ]),
      insights('shared-status-incidents', 'Active incidents', [
        { title: 'SMS delays', description: 'Carrier throughput limits causing up to 5m delays. Mitigation: re-route via backup provider.' },
        { title: 'Route optimisation lag', description: 'Background jobs catching up; board still updates but slower than SLA.' },
      ]),
      dataTable('shared-status-history', 'Recent maintenance windows', [
        { label: 'Window', key: 'window' },
        { label: 'Impact', key: 'impact' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { window: 'Apr 29 · 02:00 UTC', impact: 'Database upgrades', status: 'Completed' },
        { window: 'Apr 15 · 23:00 UTC', impact: 'Notification provider swap', status: 'Completed' },
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
    title: 'Privacy policy',
    description: 'Static doc covering data collection, retention, and GDPR rights.',
    sections: [
      insights('shared-privacy-scope', 'Scope', [
        { title: 'Customer data', description: 'Addresses, booking metadata, payment references.' },
        { title: 'Cleaner data', description: 'Location pings, route telemetry, payout info.' },
        { title: 'Legal basis', description: 'Contractual necessity + legitimate interest with opt-out for marketing.' },
      ]),
      insights('shared-privacy-rights', 'Your rights', [
        { title: 'Data access', description: 'Use /glint/gdpr/sar to request exports.' },
        { title: 'Deletion', description: 'Submit via support; backups purge within 30 days.' },
        { title: 'Questions', description: 'privacy@glintlabs.com' },
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
    title: 'Terms of service',
    description: 'Defines service commitments, cancellation policies, and platform usage rules.',
    sections: [
      insights('shared-terms-highlights', 'Highlights', [
        { title: 'Service windows', description: 'Arrival windows not arrival times; refunds triggered after SLA breach.' },
        { title: 'Payments', description: 'Card on file required; recurring plans auto-renew until cancelled.' },
        { title: 'Liability', description: 'Limited to service fee; third-party damage handled through insurance partners.' },
      ]),
      insights('shared-terms-disputes', 'Dispute process', [
        { title: 'Report issues', description: '72h window to report quality issues for re-clean or credit.' },
        { title: 'Chargebacks', description: 'Owners receive alerts inside /owner/chargebacks to respond with evidence.' },
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
    title: 'We could not find that page.',
    description: 'Friendly 404 with quick links back to booking, tracking, or workspace dashboards.',
    actions: [
      { label: 'Go home', href: '/', variant: 'primary', icon: 'bi-house-door' },
      { label: 'Contact support', href: '/help', variant: 'ghost', icon: 'bi-life-preserver' },
    ],
    sections: [
      actionGrid('shared-404-links', 'Where to next?', [
        { label: 'Book a clean', description: 'Start the booking flow again.', icon: 'bi-calendar2-plus' },
        { label: 'Track a clean', description: 'Enter tracking ID to follow progress.', icon: 'bi-geo' },
        { label: 'Open help centre', description: 'Read FAQs or chat with us.', icon: 'bi-question-circle' },
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
    title: 'You don’t have permission to view this resource.',
    description: 'Used when a role tries to access an admin-only console or an expired link.',
    sections: [
      insights('shared-403-remedy', 'How to resolve', [
        { title: 'Switch tenants', description: 'Owners may need to impersonate the correct company from /glint/tenants.' },
        { title: 'Request access', description: 'Use /owner/roles to grant Manager/Accountant permissions.' },
        { title: 'Sign in again', description: 'Magic-link tokens expire after 15 minutes.' },
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
    title: 'Something went wrong on our side.',
    description: 'Fallback when Laravel or Vue throws unexpected errors; includes incident ID and support contact.',
    sections: [
      summary('shared-500-meta', 'Diagnostics', [
        { label: 'Incident ID', value: '#A1B2', delta: 'Share this with support' },
        { label: 'Heartbeat', value: 'Systems online', delta: 'Status page below' },
      ]),
      actionGrid('shared-500-actions', 'Next steps', [
        { label: 'Retry', description: 'Attempt the action again.', icon: 'bi-arrow-clockwise' },
        { label: 'Visit status', description: 'Check if there is an ongoing incident.', icon: 'bi-activity' },
        { label: 'Open ticket', description: 'Send context to support automatically.', icon: 'bi-inbox' },
      ]),
    ],
  },
]

export function registerSharedPages() {
  sharedPages.forEach(page => definePage(page))
}
