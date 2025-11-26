import { definePage } from '../registry'
import { summary, insights, dataTable, actionGrid, brandCustomizer, timeline, detailCard, tileGrid, codeSnippet, quoteGenerator } from '../helpers'

const defaultOwnerSummary = [
  { label: 'MRR', value: '£212k', delta: '+6.3% MoM' },
  { label: 'Churn', value: '3.2%', delta: 'Goal <4%' },
  { label: 'CSAT', value: '4.84/5', delta: 'Last 30 days' },
]

const createOwnerPage = input => {
  const sections = []

  if (input.includeSummary !== false) {
    sections.push(summary(`${input.key}-summary`, 'Business KPIs', input.summaryCards || defaultOwnerSummary))
  }

  if (input.includeInsights !== false && (input.highlights?.length || 0) > 0) {
    sections.push(insights(`${input.key}-insights`, 'Controls', input.highlights))
  }

  if (input.sections?.length) {
    sections.push(...input.sections)
  }

  return {
    key: input.key,
    route: input.route,
    layout: 'workspace',
    role: 'owner',
    badge: 'Owner suite',
    title: input.title,
    description: input.description,
    hero: input.hero,
    quickLinks: input.quickLinks,
    actions: input.actions || [],
    sections,
  }
}

const ownerPages = [
  createOwnerPage({
    key: 'owner.overview',
    route: '/owner/overview',
    title: 'Company overview',
    description: 'One-glance snapshot of readiness across profile, plan, domains and controls.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Owner HQ',
      eyebrow: 'Company overview',
      headline: 'Know if AOK World is ready to sell today',
      subhead: 'Branding, billing, domains and team health stitched together for owners.',
      body: [
        'Use this panel as your morning system check. Resolve plan or domain drift before dispatch opens.',
        'Jump directly into branding, domains, billing or management without digging through menus.',
      ],
      primaryAction: { label: 'Edit company profile', href: '/owner/branding', icon: 'bi-pencil-square' },
      secondaryAction: { label: 'Invite manager', href: '/owner/roles?invite=manager', icon: 'bi-person-plus' },
      highlights: [
        { title: 'Setup health', description: 'Branding + domains verified.' },
        { title: 'Money status', description: '£18.4k payouts scheduled this week.' },
        { title: 'Comms', description: 'Email/SMS healthy last 30 days.' },
      ],
      stat: { label: 'Jobs today', value: '74', meta: '+12% vs. average weekday' },
      microCopy: ['Tenant · AOK World', 'Last sync 2 minutes ago'],
    },
    sections: [
      summary(
        'owner-overview-health',
        'Company health snapshot',
        [
          { label: 'Setup completion', value: '92%', delta: 'Branding, billing + security complete' },
          { label: 'Systems online', value: '5 / 5', delta: 'Domains, comms, tracking all green' },
          { label: 'Payouts ready', value: '£18.4k', delta: 'Last sync 40 minutes ago' },
          { label: 'Revenue today', value: '£5.42k', delta: '+14% vs. last week' },
        ],
        'High-level readiness signals before you dive into details.',
      ),
      detailCard('owner-overview-profile', 'Company profile', 'Identity + support details powering invoices, emails and the portal.', {
        fields: [
          {
            label: 'Business / trading name',
            value: '{{ company.name }}',
            hint: 'Read-only once branding is locked.',
            badge: 'Managed in Branding',
            action: { label: 'Edit in Branding', href: '/owner/branding', icon: 'bi-arrow-up-right' },
          },
          { label: 'Legal name', value: '{{ company.profile.legal_name }}', hint: 'Shown on contracts + invoices.' },
          { label: 'Company registration number', value: '{{ company.profile.registration_number }}', hint: 'Optional but keeps invoices compliant.' },
          { label: 'VAT / tax ID', value: '{{ company.profile.vat_number }}', hint: 'Displayed to customers on every invoice.' },
          { label: 'Primary timezone', value: '{{ company.profile.timezone }}' },
          { label: 'Currency', value: '{{ company.profile.currency }}', badge: 'Locked', badgeVariant: 'warning', hint: 'Lock engages after first payment.' },
          {
            label: 'Head office address',
            lines: ['{{ company.profile.address_line1 }}', '{{ company.profile.address_line2 }}', '{{ company.profile.city }} {{ company.profile.postal_code }}', '{{ company.profile.country }}'],
            hint: 'Used on invoices, legal docs and portal footers.',
          },
          {
            label: 'Support email',
            value: '{{ company.profile.support_email }}',
            hint: 'Customers see this in the portal + booking flows.',
            action: { label: 'Send test email', href: 'mailto:{{ company.profile.support_email }}', icon: 'bi-send' },
          },
          { label: 'Support phone', value: '{{ company.profile.support_phone }}' },
          { label: 'Support hours', value: '{{ company.profile.support_hours }}', meta: 'Shown on booking pages + transactional emails.' },
          {
            label: 'Sync to invoices/emails',
            value: '{{ company.profile.sync_support_details_label }}',
            state: 'success',
            hint: 'Flip off if you need temporary divergence.',
            action: { label: 'Manage sync', href: '/owner/branding', icon: 'bi-shuffle' },
          },
        ],
        actions: [
          { label: 'Edit company profile', href: '/owner/branding', icon: 'bi-pencil-square', variant: 'primary' },
          { label: 'View public site', href: '{{ company.marketing_url }}', icon: 'bi-box-arrow-up-right', variant: 'ghost' },
        ],
        footer: 'Everything here cascades into invoices, emails, workspace shells and embeds.',
      }),
      detailCard('owner-overview-plan', 'Account & plan', 'What you pay for, how many seats are used and what bills are next.', {
        fields: [
          { label: 'Plan name', value: 'Glint Pro', badge: 'Annual billing', hint: 'Includes multi-branch, live tracking and invoice automation.' },
          { label: 'Status', value: 'Active', state: 'success', meta: 'Renewed 1 Jun 2024' },
          { label: 'Seats', value: '12 / 15 used', hint: 'Managers, support + accountants count toward plan.', action: { label: 'View seats', href: '/owner/staff', icon: 'bi-person-badge' } },
          { label: 'Billing via', value: 'Stripe customer · CUS-8732', meta: 'Card •••• 4242 on file', action: { label: 'Open Stripe customer', href: 'https://dashboard.stripe.com/customers/CUS8732', icon: 'bi-box-arrow-up-right' } },
          { label: 'Next invoice', value: '1 Jul 2024 · £1,250', meta: 'Includes £320 usage overage' },
          {
            label: 'Current usage',
            list: [
              { label: 'Active subscriptions', value: '146 homes' },
              { label: 'Jobs booked this month', value: '382 (↑18%)' },
              { label: 'Locations / branches', value: '3 live' },
            ],
          },
        ],
        actions: [
          { label: 'Manage billing & invoices', href: '/owner/billing/stripe', icon: 'bi-receipt', variant: 'primary' },
          { label: 'Upgrade / downgrade plan', href: '/owner/pricing', icon: 'bi-arrow-repeat', variant: 'ghost' },
        ],
      }),
      detailCard('owner-overview-defaults', 'Operational defaults', 'What every booking inherits until a manager overrides it.', {
        fields: [
          { label: 'Default visit length', value: '2h 00m', hint: 'Quote builder + dispatcher start here.' },
          { label: 'Default arrival window', value: '± 45 minutes', meta: 'Shown to customers + staff', action: { label: 'Edit window', href: '/owner/policies#arrival', icon: 'bi-arrows-expand' } },
          {
            label: 'Default service area',
            lines: ['ME1–ME20 postcodes', 'TN1–TN5 within 20 miles of Maidstone'],
            action: { label: 'Edit routes', href: '/owner/dispatch/routes', icon: 'bi-signpost-split' },
          },
          { label: 'Default cancellation policy', value: 'Cancel < 24h charged 50%', action: { label: 'Edit in Policies', href: '/owner/policies', icon: 'bi-shield-check' } },
          { label: 'Default reschedule policy', value: 'Move < 12h converts to credit only', action: { label: 'Adjust policy', href: '/owner/policies', icon: 'bi-arrow-clockwise' } },
          { label: 'Customer reminders', value: 'SMS + email 24h before', meta: 'Managed under Notifications' },
        ],
        actions: [{ label: 'Edit operational defaults', href: '/owner/policies', icon: 'bi-sliders', variant: 'primary' }],
      }),
      tileGrid('owner-overview-integrations', 'Integrations & status', 'Check whether revenue + communications rails are connected.', [
        {
          label: 'Stripe Connect',
          status: 'Connected',
          description: 'Last payout Tue · 4 Jun · £8,120',
          subtext: 'Requirements up to date',
          action: { label: 'Open Stripe Connect settings', href: '/owner/billing/stripe', icon: 'bi-plug' },
          state: 'success',
        },
        {
          label: 'Customer portal & website',
          status: 'Website set',
          description: 'https://www.aokworld.co.uk',
          subtext: 'Customer portal: https://aok-world.glintlabs.com',
          action: { label: 'View domain & widget instructions', href: '/owner/domains', icon: 'bi-globe' },
          state: 'info',
        },
        {
          label: 'Customer portal',
          status: 'Enabled',
          description: 'Self-serve portal reachable + branded',
          action: { label: 'Open portal', href: 'https://aokworld.glintcustomers.com', icon: 'bi-box-arrow-up-right' },
          state: 'success',
        },
        {
          label: 'Live tracking',
          status: 'On',
          description: 'Customers can watch cleaners during active jobs',
          action: { label: 'Configure tracking', href: '/owner/policies#tracking', icon: 'bi-geo-alt' },
          state: 'info',
        },
        {
          label: 'Email/SMS sending',
          status: 'Healthy',
          description: 'No delivery errors in the last 30 days',
          meta: 'Last error 28 May · resolved',
          subtext: 'SMS credits: 84% remaining',
          action: { label: 'View delivery log', href: '/owner/integrations', icon: 'bi-chat-dots' },
          state: 'success',
        },
      ]),
      detailCard('owner-overview-team', 'Team snapshot', 'Who can access the tenant + what they touched recently.', {
        fields: [
          {
            label: 'Role counts',
            list: [
              { label: 'Owners', value: '1' },
              { label: 'Managers', value: '4' },
              { label: 'Accountants', value: '2' },
              { label: 'Support', value: '3' },
              { label: 'Cleaners', value: '68 active' },
            ],
            hint: 'Cleaners live in Staff roster but we surface the volume here.',
          },
          {
            label: 'Recent logins',
            list: [
              { label: 'Sarah Bloom · Owner', value: 'Last seen 3h ago' },
              { label: 'Tom Avery · Manager', value: 'Last seen 52m ago' },
              { label: 'Yasmin Li · Accountant', value: 'Last seen 2h ago' },
            ],
            hint: 'Helpful when auditing sensitive actions.',
          },
          { label: 'Security posture', value: '2FA enforced for Owners + Managers', meta: '4 pending invites must enable before access.', state: 'info' },
        ],
        actions: [
          { label: 'Invite manager', href: '/owner/roles?invite=manager', icon: 'bi-person-plus', variant: 'primary' },
          { label: 'Invite accountant', href: '/owner/roles?invite=accountant', icon: 'bi-person-vcard' },
          { label: 'Go to Staff', href: '/owner/staff', icon: 'bi-people', variant: 'ghost' },
        ],
      }),
      timeline(
        'owner-overview-activity',
        'Activity & audit highlights',
        [
          { title: 'Tom Avery issued £30 refund', detail: 'Cleaner spill on Job #4832 (Manager override)', time: '09:18 today', state: 'warning', meta: ['Invoices', 'Refund'] },
          { title: 'New domain bookings.aokworld.co verified', detail: 'SSL active + pointing to customer portal', time: '08:02 today', state: 'success', meta: ['Domains'] },
          { title: 'Tracking disabled by Sarah Bloom', detail: 'Paused live tracking for Crew B until 12:00', time: 'Yesterday · 18:44', state: 'danger', meta: ['Policies', 'Tracking'] },
          { title: 'Plan upgraded to Glint Pro', detail: 'Owner upgrade by Tom Avery', time: 'Mon · 15:30', state: 'info', meta: ['Billing'] },
          { title: 'Sync to invoices enabled', detail: 'Branding footer now live on emails + invoices', time: 'Mon · 09:05', state: 'success', meta: ['Branding'] },
        ],
        'Last 5 actions touching billing, domains or controls.',
      ),
    ],
  }),
  createOwnerPage({
    key: 'owner.billing.stripe',
    route: '/owner/billing/stripe',
    title: 'Stripe Connect',
    description: 'Connect/manage Stripe accounts, platform fees view.',
    highlights: [
      { title: 'Connect onboarding', description: 'Walk through Connect standard or Express onboarding.' },
      { title: 'Fee breakdown', description: 'See platform fees vs. payouts.' },
      { title: 'Account status', description: 'Verify requirements, pending info, capabilities.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.invoices',
    route: '/owner/invoices',
    title: 'Owner invoices',
    description: 'View all invoices per tenant; export/resend.',
    highlights: [
      { title: 'Sortable ledger', description: 'Filter by tenant, status, amount.' },
      { title: 'Resend', description: 'Send invoice to alternate recipients.' },
      { title: 'Bulk export', description: 'CSV or accounting integration push.' },
    ],
    sections: [
      dataTable('owner-invoices-table', 'Invoices', [
        { label: 'Invoice', key: 'id' },
        { label: 'Tenant', key: 'tenant' },
        { label: 'Status', key: 'status' },
        { label: 'Amount', key: 'amount', align: 'right' },
      ], [
        { id: '#TEN-5401', tenant: 'Sparkle Co', status: 'Paid', amount: '£8,420' },
        { id: '#TEN-5400', tenant: 'BrightNest', status: 'Due', amount: '£3,120' },
      ]),
    ],
  }),
  createOwnerPage({
    key: 'owner.payouts',
    route: '/owner/payouts',
    title: 'Payouts',
    description: 'Reconciliations, pending/paid payouts.',
    highlights: [
      { title: 'Upcoming payouts', description: 'Timeline of transfers from Stripe Connect.' },
      { title: 'Adjustments', description: 'Show platform fees, refunds, chargebacks deducted.' },
      { title: 'Bank accounts', description: 'Manage receiving accounts per currency.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.taxes',
    route: '/owner/taxes',
    title: 'Taxes',
    description: 'VAT settings, reports, HMRC export.',
    highlights: [
      { title: 'Rates per region', description: 'Configure VAT per locale or service type.' },
      { title: 'Return prep', description: 'Generate HMRC-ready reports.' },
      { title: 'Evidence storage', description: 'Store exemption proofs or certificates.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.chargebacks',
    route: '/owner/chargebacks',
    title: 'Chargebacks',
    description: 'Disputes queue with evidence upload support.',
    highlights: [
      { title: 'Queue', description: 'List of chargebacks sorted by deadline.' },
      { title: 'Evidence kit', description: 'Upload photos, contact logs, signed checklists.' },
      { title: 'Outcome tracking', description: 'Win/loss analytics.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.roles',
    route: '/owner/roles',
    title: 'Management',
    description: 'Company roles, invites, security guardrails and escalation contacts.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Owner controls',
      eyebrow: 'Management',
      headline: 'Control who can touch money, customers and policies',
      subhead: 'Invite office roles, enforce security, and keep escalation contacts current.',
      primaryAction: { label: 'Invite team member', href: '/owner/roles?invite=team', icon: 'bi-person-plus' },
      secondaryAction: { label: 'Open audit log', href: '/owner/audit-log', icon: 'bi-clipboard-data' },
      highlights: [
        { title: 'Never be ownerless', description: 'We block demoting the last owner automatically.' },
        { title: 'Security defaults', description: '2FA + IP controls travel with the tenant.' },
        { title: 'Escalation ready', description: 'Glint knows who to call when payouts fail.' },
      ],
      stat: { label: 'Pending invites', value: '3', meta: '2 expiring within 48h' },
    },
    sections: [
      summary(
        'owner-management-snapshot',
        'Office team snapshot',
        [
          { label: 'Owners', value: '1', delta: 'Always keep a backup owner' },
          { label: 'Managers', value: '4', delta: '2 with dispatch rights' },
          { label: 'Accountants', value: '2', delta: 'Handle payouts + adjustments' },
          { label: 'Pending invites', value: '3', delta: '2 expiring in 2 days', badge: 'Action' },
        ],
        'Non-cleaner roles with access to billing, policies and sensitive data.',
      ),
      dataTable(
        'owner-management-office-team',
        'Company roles & access',
        [
          { label: 'Name', key: 'name' },
          { label: 'Email', key: 'email' },
          { label: 'Role(s)', key: 'roles' },
          { label: 'Status', key: 'status' },
          { label: 'Last login', key: 'lastLogin' },
          { label: '2FA', key: 'twofa' },
          { label: 'Actions', key: 'actions', align: 'right' },
        ],
        [
          { name: 'Sarah Bloom', email: 'sarah@aokworld.co', roles: 'Owner', status: 'Active', lastLogin: '3h ago', twofa: 'On', actions: 'Edit · Send magic link' },
          { name: 'Tom Avery', email: 'tom@aokworld.co', roles: 'Manager · Dispatch', status: 'Active', lastLogin: '52m ago', twofa: 'On', actions: 'Change role · Suspend' },
          { name: 'Yasmin Li', email: 'yasmin@aokworld.co', roles: 'Accountant', status: 'Active', lastLogin: '2h ago', twofa: 'Off (pending)', actions: 'Remind 2FA' },
          { name: 'Maya Ortiz', email: 'maya@aokworld.co', roles: 'Support', status: 'On leave', lastLogin: 'Jun 02', twofa: 'On', actions: 'Reinstate · Suspend' },
        ],
        'Owner + manager permissions live here. Cleaners stay under Staff roster.',
      ),
      actionGrid(
        'owner-management-actions',
        'Team controls',
        [
          { label: 'Invite team member', description: 'Name, email, role + optional permission groups.', icon: 'bi-person-plus' },
          { label: 'Send magic link', description: 'Instant, secure login link to an existing user.', icon: 'bi-lightning-charge' },
          { label: 'Suspend user', description: 'Revokes access, tokens and portal visibility immediately.', icon: 'bi-shield-x' },
          { label: 'Audit role change', description: 'Jump into the audit log filtered by user and action.', icon: 'bi-clipboard-data' },
        ],
        'Owners can demote anyone except themselves if they are the last owner.',
      ),
      dataTable(
        'owner-management-invites',
        'Invites & pending users',
        [
          { label: 'Email', key: 'email' },
          { label: 'Role', key: 'role' },
          { label: 'Invited by', key: 'invitedBy' },
          { label: 'Invited on', key: 'invitedOn' },
          { label: 'Status', key: 'status' },
          { label: 'Actions', key: 'actions', align: 'right' },
        ],
        [
          { email: 'ops@fieldnorth.co', role: 'Manager', invitedBy: 'Sarah Bloom', invitedOn: 'Jun 03', status: 'Pending · expires in 2 days', actions: 'Resend · Cancel' },
          { email: 'accounts@aokworld.co', role: 'Accountant', invitedBy: 'Yasmin Li', invitedOn: 'Jun 02', status: 'Pending · accepted policies', actions: 'Copy invite · Resend' },
          { email: 'dispatch@northspark.co', role: 'Support', invitedBy: 'Tom Avery', invitedOn: 'May 31', status: 'Expired', actions: 'Reinvite' },
        ],
        'Resend, cancel or copy onboarding links without leaving the page.',
      ),
      detailCard('owner-management-security', 'Security settings', 'High-level controls for authentication and sensitive actions.', {
        fields: [
          {
            label: 'Require 2FA for',
            list: [
              { label: 'Owners', value: 'Required' },
              { label: 'Managers', value: 'Required' },
              { label: 'Accountants', value: 'Optional (weekly prompt)' },
            ],
          },
          { label: 'Allowed sign-in methods', tags: ['Email magic link', 'SMS magic link', 'Password'], meta: 'Disable SMS if you run out of credits.' },
          { label: 'Session timeout', value: '2 hours idle', hint: 'Owner + manager shells auto-lock faster than staff.', state: 'info' },
          { label: 'IP restrictions', value: 'Refunds + payouts limited to 2 trusted IPs', meta: '54.22.18.93 · 34.102.101.7', action: { label: 'Manage IP list', href: '/owner/security', icon: 'bi-wifi' } },
          { label: 'Audit log', value: 'Every role change tracked with actor + IP', action: { label: 'View full audit log', href: '/owner/audit-log', icon: 'bi-box-arrow-up-right' } },
        ],
        actions: [{ label: 'Edit security settings', href: '/owner/security', icon: 'bi-shield-lock', variant: 'primary' }],
      }),
      detailCard('owner-management-contacts', 'Contacts & escalation', 'Let Glint and automation know who to reach when something breaks.', {
        fields: [
          {
            label: 'Primary account owner',
            lines: ['Sarah Bloom', 'sarah@aokworld.co', '+44 20 7630 4411'],
            hint: 'We text/call here for payouts or compliance issues.',
          },
          { label: 'Billing contact', lines: ['Yasmin Li', 'billing@aokworld.co'], hint: 'Receives invoices + failed payment alerts.' },
          { label: 'Operations / dispatch contact', lines: ['Tom Avery', '+44 7500 111 220'], hint: 'Appears in SMS/email footers for urgent reschedules.' },
          { label: 'Emergency / out-of-hours', lines: ['Ops hotline', '+44 20 7630 4400'], meta: 'Used when the job system is down.' },
          { label: 'Public website', value: 'https://www.aokworld.co.uk', hint: 'Shown on support tickets + “Back to website” buttons.', action: { label: 'Open site', href: 'https://www.aokworld.co.uk', icon: 'bi-box-arrow-up-right' } },
          { label: 'Customer portal', value: 'https://aok-world.glintlabs.com', hint: 'Customer login & tracking URL referenced inside alerts.', action: { label: 'Open portal', href: 'https://aok-world.glintlabs.com', icon: 'bi-box-arrow-up-right' } },
          {
            label: 'Notification preferences',
            list: [
              { label: 'System outage alerts', value: 'Sarah Bloom + Ops hotline' },
              { label: 'Payment issues', value: 'Yasmin Li + Sarah Bloom', meta: 'Escalate if unresolved after 30m' },
              { label: 'Compliance alerts', value: 'Ops inbox only' },
            ],
          },
        ],
        actions: [{ label: 'Edit contacts', href: '/owner/contacts', icon: 'bi-telephone', variant: 'primary' }],
      }),
      detailCard('owner-management-service', 'Service areas & hours (summary)', 'High-level defaults before dispatch dives into detailed routes.', {
        fields: [
          { label: 'Operating days', tags: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'], hint: 'Sunday stays closed for deep cleans + maintenance.' },
          { label: 'Standard business hours', value: '08:00 – 18:00 (Fri til 20:00)', meta: 'Shown on booking portal + customer emails.' },
          {
            label: 'Service area summary',
            lines: ['ME1–ME20 core', 'TN1–TN5 surcharge zone', '20-mile radius around Maidstone'],
            action: { label: 'Configure detailed routes', href: '/owner/dispatch/routes', icon: 'bi-signpost-split' },
          },
          {
            label: 'Customer booking rules',
            list: [
              { label: 'Max days in advance', value: '90 days' },
              { label: 'Min notice for new bookings', value: '24 hours' },
              { label: 'Self-serve cancellations', value: 'Allowed until 24h prior' },
            ],
            action: { label: 'Edit booking rules in Policies', href: '/owner/policies#rules', icon: 'bi-journal-text' },
          },
        ],
      }),
      detailCard('owner-management-danger', 'Danger zone', 'High-impact operations are locked behind owner confirmation.', {
        status: 'Owner confirmation required',
        statusVariant: 'danger',
        fields: [
          { label: 'Export company data', value: 'Available', hint: 'Jobs, customers and invoices emailed as a secure export within 24h.', action: { label: 'Request export', href: '/owner/export', icon: 'bi-cloud-arrow-down' } },
          { label: 'Request account closure', value: 'Manual review', state: 'warning', hint: 'Cancels jobs, freezes payouts until settlements clear.', action: { label: 'Start closure request', href: '/owner/closure', icon: 'bi-door-closed' } },
          { label: 'Delete test data', value: 'Only for sandbox tenants', hint: 'Removes customers/jobs created before 01 Jun 2024.', action: { label: 'Delete test data', href: '/owner/cleanup', icon: 'bi-trash' } },
        ],
        actions: [{ label: 'Talk to Glint support', href: '/owner/support', icon: 'bi-life-preserver', variant: 'ghost' }],
      }),
    ],
  }),
  createOwnerPage({
    key: 'owner.dispatch.board',
    route: '/owner/dispatch/board',
    title: 'Dispatch board',
    description: 'Live control room for today and near-future jobs.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Operations',
      eyebrow: 'Dispatch board',
      headline: null,
      subhead: null,
      hideCopy: true,
      heroClass: 'hero-banner--dispatch',
      mapPanel: { dataKey: 'owner.dispatch_board.jobs' },
    },
    sections: [
      {
        id: 'owner-dispatch-board',
        component: 'OwnerDispatchBoard',
        fullWidth: true,
        props: {
          dataKey: 'owner.dispatch_board',
        },
      },
    ],

  }),
  createOwnerPage({
    key: 'owner.routes',
    route: '/owner/dispatch/routes',
    title: 'Routes',
    description: 'Define rounds, areas and auto-assignment logic for recurring work.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Operations',
      eyebrow: 'Routes',
      headline: 'Colour-coded rounds that drive auto-assignment',
      subhead: 'Keep service day, area and capacity rules in one place.',
      primaryAction: { label: 'Create route', href: '/owner/dispatch/routes/new', icon: 'bi-plus-circle' },
      secondaryAction: { label: 'Duplicate best route', href: '/owner/dispatch/routes?duplicate=latest', icon: 'bi-copy' },
      highlights: [
        { title: 'Service patterns', description: 'Days of week + cadences (4/6/8 weekly).' },
        { title: 'Geo filters', description: 'Postcodes plus draw-on-map polygons.' },
        { title: 'Capacity + automation', description: 'Auto assign bookings within limits.' },
      ],
    },
    sections: [
      summary(
        'owner-routes-kpis',
        'Route health',
        [
          { label: 'Active routes', value: '12', delta: '2 paused' },
          { label: 'Avg capacity used', value: '78%', delta: 'Across next run' },
          { label: 'Routes auto-assigning', value: '9', delta: '3 manual only' },
          { label: 'Upcoming runs today', value: '4', delta: 'Next run 08:00' },
        ],
      ),
      dataTable(
        'owner-routes-table',
        'Routes list',
        [
          { label: 'Route', key: 'route' },
          { label: 'Type', key: 'type' },
          { label: 'Service days', key: 'days' },
          { label: 'Area', key: 'area' },
          { label: 'Primary cleaner', key: 'cleaner' },
          { label: 'Capacity used', key: 'capacity' },
          { label: 'Next run', key: 'nextRun' },
          { label: 'Subscriptions', key: 'subscriptions' },
          { label: 'Status', key: 'status' },
        ],
        [
          { route: 'Tuesday – Maidstone West', type: 'Regular', days: 'Tue / Fri', area: 'ME17, ME18', cleaner: 'Amelia Blake', capacity: '14 / 18 jobs', nextRun: 'Tue 11 Jun', subscriptions: '22 active', status: 'Active' },
          { route: 'Thursday Deep Clean', type: 'Ad-hoc', days: 'Thu', area: 'ME10–ME12', cleaner: 'Leo Gomez', capacity: '6 / 8 jobs', nextRun: 'Thu 13 Jun', subscriptions: '5 active', status: 'Paused' },
        ],
        'Row actions: Open · Edit · Duplicate · Archive.',
      ),
      detailCard('owner-routes-builder', 'Create / edit route', 'Everything needed to define a round.', {
        fields: [
          { label: 'Route name & colour', value: 'e.g. Tuesday – Maidstone West with colour chip used on board + map.' },
          { label: 'Service pattern', lines: ['Days of week (Mon–Sun checkboxes)', 'Cadence compatibility: 4 / 6 / 8 weekly'], hint: 'Controls which subscriptions auto-fit.' },
          { label: 'Time window', value: 'Start + end time per run.' },
          { label: 'Service area', list: [{ label: 'Postcodes', value: 'Comma list' }, { label: 'Map polygon/radius', value: 'Draw to fine tune' }], hint: 'Any matching booking qualifies.' },
          { label: 'Default cleaner/team', value: 'Primary + backup for auto-assignment and map icons.' },
          { label: 'Capacity & rules', list: [{ label: 'Max jobs per run', value: 'Number or hours' }, { label: 'Auto-assign toggle', value: 'Send new bookings straight to this route if criteria match' }], hint: 'Warn when full.' },
          { label: 'Notes', value: 'Internal remarks, e.g. “Avoid high street on market day”.' },
        ],
      }),
      detailCard('owner-routes-detail', 'Route detail view', 'Give owners tabs for overview, jobs/subscriptions, schedule and performance.', {
        fields: [
          { label: 'Overview', list: [{ label: 'Upcoming runs', value: 'Next 4 dates with job counts' }, { label: 'Map', value: 'Markers for next run' }, { label: 'Stats', value: 'Avg jobs/run, on-time %, CSAT' }] },
          { label: 'Jobs & subscriptions', value: 'Table of jobs with move/pause actions.' },
          { label: 'Schedule', value: 'Timeline for selected date with drag-to-reorder stops updating suggested times.' },
          { label: 'Performance', value: 'Average travel time, time per job, missed/late, cancellations per run.' },
        ],
      }),
    ],
  }),
  createOwnerPage({
    key: 'owner.jobs',
    route: '/owner/jobs',
    title: 'Jobs',
    description: 'Source of truth for every job, past or future.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Operations',
      eyebrow: 'Jobs',
      headline: 'Search, filter and edit any job in seconds',
      subhead: 'Powerful filters plus a detailed job drawer keep everything auditable.',
      primaryAction: { label: 'Create job', href: '/owner/jobs/new', icon: 'bi-plus-circle' },
      secondaryAction: { label: 'Open dispatch board', href: '/owner/dispatch/board', icon: 'bi-kanban' },
      highlights: [
        { title: 'Rich filters', description: 'Status, route, cleaner, area, source, payment.' },
        { title: 'Timeline + audit', description: 'See who changed what and when.' },
        { title: 'Refund + reschedule controls', description: 'Owners can handle Stripe money without leaving Glint.' },
      ],
    },
    sections: [
      summary(
        'owner-jobs-kpis',
        'Jobs snapshot',
        [
          { label: 'Jobs this week', value: '86 scheduled', delta: '12 completed / 4 cancelled' },
          { label: 'Prepaid vs pending', value: '64 prepaid · 8 pending · 14 pay-on-day', delta: 'Stripe heavy' },
          { label: 'Payment issues', value: '2 failed · 1 partial refund', delta: 'Triage from here' },
          { label: 'Source mix', value: '60% online · 25% portal · 10% manual · 5% API', delta: 'Last 7 days' },
        ],
      ),
      detailCard('owner-jobs-filters', 'Search & filters', 'Slice the ledger by status, payment, route or source.', {
        fields: [
          { label: 'Global search', value: 'Matches customer, address, Job ID, subscription ID, Stripe charge / PaymentIntent / Checkout Session IDs.' },
          { label: 'Date filters', value: 'Today, Next 7 days, Last 7 days, All upcoming/past, or custom range with date_from/date_to.' },
          { label: 'Status filters', list: [
            { label: 'Job status', value: 'Draft, Awaiting payment, Scheduled, Cleaner en route, On site, Completed, Cancelled (customer/company), Failed / No access.' },
            { label: 'Payment status', value: 'Prepaid (Stripe), Pending Checkout, Subscription, Pay-on-day, Invoiced, Refunded, Partially refunded, Payment failed, Chargeback.' },
          ] },
          { label: 'Other filters', list: [
            { label: 'Route + cleaner', value: 'Colour chips, multi-select with avatars.' },
            { label: 'Job type', value: 'One-off, Subscription visit (4/6/8-week), Legacy/import.' },
            { label: 'Area / postcode', value: 'Prefix + tags.' },
            { label: 'Source', value: 'Online booking, Portal, Manual job, Import/API.' },
          ] },
          { label: 'Sort', value: 'Date, Status, Route, Cleaner, Payment status, Created at.' },
        ],
      }),
      dataTable(
        'owner-jobs-table',
        'Jobs list',
        [
          { label: 'Date & time', key: 'when' },
          { label: 'Status', key: 'status' },
          { label: 'Customer', key: 'customer' },
          { label: 'Address', key: 'address' },
          { label: 'Route', key: 'route' },
          { label: 'Cleaner', key: 'cleaner' },
          { label: 'Job type', key: 'type' },
          { label: 'Price', key: 'price' },
          { label: 'Payment', key: 'payment' },
          { label: 'Source', key: 'source' },
          { label: 'Updated', key: 'updated' },
        ],
        [],
        'Row actions: View · Reschedule · Assign/Reassign · Open dispatch · Cancel (with refund options) · Mark completed · Issue refund · Duplicate · Request payment.',
        null,
        { rowsKey: 'owner.jobs.table' },
      ),
      detailCard('owner-jobs-payments', 'Payment & Stripe states', 'Keep money + status in the same row.', {
        fields: [
          { label: 'Payment badges', value: 'Prepaid (Stripe), Subscription (Stripe Billing), Pending (Checkout), Pay-on-day, Invoiced, Refunded, Partially refunded, Payment failed, Chargeback.' },
          { label: 'Popover contents', value: 'Checkout Session, Payment Intent, Charge, Subscription, Invoice IDs with quick “View in Stripe”.' },
          { label: 'Webhook mapping', value: 'checkout.session.completed / payment_intent.succeeded → mark Scheduled + Prepaid; payment_intent.payment_failed → Payment failed; charge.dispute.created → Chargeback.' },
          { label: 'Bulk actions', list: [{ label: 'Assign / move route', value: 'Multi-select drag or dropdown actions.' }, { label: 'Cancel jobs', value: 'Choose keep payment vs auto-refund (full/partial).' }, { label: 'Mark completed', value: 'For missed taps in cleaner app.' }, { label: 'Export selected', value: 'CSV includes payment metadata.' }] },
        ],
      }),
      detailCard('owner-jobs-detail', 'Job detail blueprint', 'Use sections to mirror the operational workflow.', {
        fields: [
          { label: 'Job summary', list: [{ label: 'Status timeline', value: 'Draft → Awaiting payment → Scheduled → Completed' }, { label: 'Duration', value: 'Scheduled vs actual' }, { label: 'Price & payment', value: 'Breakdown + Stripe IDs' }] },
          { label: 'Customer & address', value: 'Name/email/phone with View customer link, plus property details, tags and notes.' },
          { label: 'Scheduling & route', value: 'Original vs current window, route dropdown, assignment, reschedule controls with policy helper.' },
          { label: 'Subscription link', value: 'Show cadence, next visit, quick actions (open, skip).' },
          { label: 'Payments & invoices', list: [
            { label: 'Payment status badge', value: 'Prepaid / Pending Checkout / Pay-on-day / Refunded / Chargeback etc.' },
            { label: 'Details', value: 'Total, tax, tips, amount paid, refunds, card info.' },
            { label: 'Stripe IDs', value: 'Checkout Session, Payment Intent, Charge, Subscription, Invoice.' },
            { label: 'Actions', value: 'Issue refund (full/partial), Request payment link, Mark unpaid, View in Stripe.' },
            { label: 'History', value: 'Payment attempts, refunds, chargebacks with actor + timestamp.' },
          ] },
          { label: 'Checklist & media', value: 'Tasks, before/after photos, internal vs customer notes.' },
          { label: 'Timeline & audit', value: 'Assignments, tracking events, payment/refund history.' },
          { label: 'Actions panel', value: 'Cancel job (keep payment vs auto-refund), issue partial refund, rebook, resend confirmation, copy tracking link.' },
        ],
      }),
      detailCard('owner-jobs-create', 'Create job (manual) with payment options', 'Owners choose how the job gets paid up front.', {
        fields: [
          { label: 'Steps 1–3', value: 'Customer & address → Job details → Schedule & assignment.' },
          { label: 'Step 4 · Pricing & payment', list: [
            { label: 'Prepaid now – card (Stripe)', value: 'Create Checkout Session / Payment Link; job stays Awaiting payment until webhook success.' },
            { label: 'Pay-on-day', value: 'No Stripe object yet. Later request payment via link/terminal.' },
            { label: 'Invoice (Stripe or external)', value: 'Mark as invoiced, optionally attach Stripe invoice ID.' },
            { label: 'Attach to subscription', value: 'Link to stripe_subscription_id for off-schedule visits.' },
          ] },
          { label: 'Post-create actions', value: 'Copy/send payment link, auto-assign warnings, policy-aware cancellation + refunds.' },
        ],
      }),
    ],
  }),
  createOwnerPage({
    key: 'owner.jobs.new',
    route: '/owner/jobs/new',
    title: 'Create job',
    description: 'Owners get the booking calculator plus scheduling, cadence and payment controls in one place.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Operations',
      eyebrow: 'Manual job composer',
      headline: 'Create a job, pick dates and lock in payment in minutes',
      subhead: 'Start with the /book calculator, pull in CRM context, override pricing if needed and schedule the run before dispatch sees it.',
      primaryAction: { label: 'Back to jobs', href: '/owner/jobs', icon: 'bi-arrow-left' },
      secondaryAction: { label: 'Open dispatch board', href: '/owner/dispatch/board', icon: 'bi-kanban' },
    },
    sections: [
      quoteGenerator(
        'owner-jobs-new-composer',
        'Manual job composer',
        'Owners start with the /book calculator and can flip on "Advanced pricing" to hand-type per-visit price, monthly total, deposit or duration overrides before saving.',
        { anchor: 'composer', props: { hideHeroLinks: true, hideStreetMeta: true } },
      ),
      detailCard('owner-jobs-new-override', 'Manual price & duration override', 'Advanced controls sit beside the quote card so owners can edit the maths directly.', {
        fields: [
          { label: 'Enable override', value: 'Toggle reveals numeric inputs for “Per visit price”, “Monthly total”, “Deposit” + “Duration minutes”.' },
          { label: 'Reason logging', value: 'Owners must add an override reason; Glint logs actor, timestamp, previous/new price for audit + dispatch.' },
          { label: 'Auto-recalc tips', value: 'When entering a new per-visit price the monthly + deposit fields update unless the owner locks them manually.' },
          { label: 'First visit uplift', value: 'Optional checkbox to apply a % uplift only on the first clean when quoting deep cleans.' },
        ],
      }),
      detailCard('owner-jobs-new-schedule', 'Schedule & cadence picker', 'Inline date + time inputs let owners set the first visit and preview future anchors instantly.', {
        fields: [
          { label: 'Calendar panel', value: 'Full-width date picker with availability dots; clicking a day opens time chips (e.g. 09:00–11:00).' },
          { label: 'Timezone hints', value: 'Shows company timezone + warns if the customer’s preferred window conflicts with working hours.' },
          { label: 'Cadence selector', value: 'Choosing 4/6/8-week updates the “Next visits preview” below in real-time so owners can confirm capacity.' },
          { label: 'Route & cleaner', value: 'Dropdown suggestions sorted by postcode proximity + load meter. Owners can leave as “Unassigned” with notes.' },
        ],
      }),
      dataTable(
        'owner-jobs-new-cadence-preview',
        'Next visits preview',
        [
          { label: 'Visit', key: 'label' },
          { label: 'Date', key: 'date' },
          { label: 'Window', key: 'window' },
          { label: 'Route suggestion', key: 'route' },
        ],
        [
          { label: 'Anchor', date: 'Tue 02 Jul', window: '09:00–11:00', route: 'Tue – Maidstone' },
          { label: '+1 cadence', date: 'Tue 30 Jul', window: '09:00–11:00', route: 'Tue – Maidstone' },
          { label: '+2 cadence', date: 'Tue 27 Aug', window: '09:00–11:00', route: 'Tue – Maidstone' },
          { label: '+3 cadence', date: 'Tue 24 Sep', window: '09:00–11:00', route: 'Tue – Maidstone' },
          { label: '+4 cadence', date: 'Tue 22 Oct', window: '09:00–11:00', route: 'Tue – Maidstone' },
        ],
        'Updates live when cadence pills change. Hidden automatically when "One-off" is selected.',
      ),
    ],
  }),
  createOwnerPage({
    key: 'owner.customers',
    route: '/owner/customers',
    title: 'Customers',
    description: 'Manage people, households, and every property they own.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Operations',
      eyebrow: 'Customers',
      headline: 'Household-centric CRM for cleaners',
      subhead: 'Search, see lifetime spend, send portal links and keep notes tight.',
      primaryAction: { label: 'Add customer', href: '/owner/customers/new', icon: 'bi-person-plus' },
      secondaryAction: { label: 'Send portal link', href: '/owner/customers?send=portal', icon: 'bi-send' },
      highlights: [
        { title: 'Property aware', description: 'Addresses can exist before the person does.' },
        { title: 'Portal magic links', description: 'Owners can send login links instantly.' },
        { title: 'Comms log', description: 'Track email/SMS/call notes in one tab.' },
      ],
    },
    sections: [
      summary(
        'owner-customers-kpis',
        'Customer metrics',
        [
          { label: 'Active customers', value: '412', delta: '38 paused/inactive' },
          { label: 'With subscriptions', value: '220', delta: '53% of base' },
          { label: 'Lifetime spend > £1k', value: '96', delta: '+6 MoM' },
          { label: 'Portal invites sent this week', value: '28', delta: '14 opened' },
        ],
      ),
      dataTable(
        'owner-customers-table',
        'Customers list',
        [
          { label: 'Customer', key: 'customer' },
          { label: 'Email', key: 'email' },
          { label: 'Phone', key: 'phone' },
          { label: 'Primary address', key: 'address' },
          { label: 'Subscriptions', key: 'subscriptions' },
          { label: 'Last visit', key: 'lastVisit' },
          { label: 'Lifetime spend', key: 'lifetime' },
          { label: 'Tags', key: 'tags' },
          { label: 'Status', key: 'status' },
        ],
        [
          { customer: 'Sarah Bloom', email: 'sarah@example.com', phone: '+44 20 7000 5555', address: '20 Electric Blvd SW11', subscriptions: '2 active', lastVisit: '05 Jun', lifetime: '£4,820', tags: 'VIP, Always home', status: 'Active' },
          { customer: 'Address-only (ME16)', email: '—', phone: '—', address: '12 Rose Walk ME16', subscriptions: '0', lastVisit: '—', lifetime: '£180', tags: 'Needs owner approval', status: 'Lead' },
        ],
        'Actions: View · Create job · Start subscription · Send portal link · Merge.',
      ),
      detailCard('owner-customers-detail', 'Customer detail blueprint', 'Tabs keep owners from drowning in data.', {
        fields: [
          { label: 'Overview', list: [{ label: 'Contact info', value: 'Name, salutation, preferred contact method' }, { label: 'Primary address map', value: 'Pin + quick directions' }, { label: 'Tags + status', value: 'Active / Inactive / Do not serve' }, { label: 'Portal links', value: 'Copy or send magic link' }, { label: 'Metrics', value: 'Lifetime spend, jobs completed, avg frequency, last/next visit' }] },
          { label: 'Properties & jobs', value: 'List addresses with tags, last/next visit, route. Selecting property shows job table with rebook/convert options.' },
          { label: 'Subscriptions', value: 'Table of subscriptions with cadence, next visit, price, quick actions (open, pause, cancel).' },
          { label: 'Notes & comms', value: 'Private notes plus log of emails, SMS, manual calls. Actions: Add note, log call, resend confirmation.' },
        ],
      }),
    ],
  }),
  createOwnerPage({
    key: 'owner.subscriptions',
    route: '/owner/subscriptions',
    title: 'Subscriptions',
    description: 'Recurring cleans with cadence, billing and skip controls.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Operations',
      eyebrow: 'Subscriptions',
      headline: 'Keep recurring revenue tidy',
      subhead: 'Filter by status, cadence or payment issues and change cadence with safety rails.',
      primaryAction: { label: 'Create subscription', href: '/owner/subscriptions/new', icon: 'bi-repeat' },
      secondaryAction: { label: 'View Stripe customer', href: '/owner/billing/stripe', icon: 'bi-credit-card' },
      highlights: [
        { title: 'Cadence aware', description: '4 / 6 / 8 weekly anchors with skip previews.' },
        { title: 'Stripe linked', description: 'Show subscription + payment state inline.' },
        { title: 'Controls for pause/change', description: 'Owners can pause, resume, change price with history.' },
      ],
    },
    sections: [
      summary(
        'owner-subscriptions-kpis',
        'Subscription overview',
        [
          { label: 'Active', value: '220', delta: '8 paused' },
          { label: 'Cadence mix', value: '60% 4-week · 30% 6-week · 10% 8-week', delta: 'Auto-updated nightly' },
          { label: 'Next 7 days', value: '124 visits', delta: '12 at risk (weather)' },
          { label: 'Payment issues', value: '3', delta: 'Show past-due in Stripe' },
        ],
      ),
      dataTable(
        'owner-subscriptions-table',
        'Subscriptions list',
        [
          { label: 'Subscription', key: 'id' },
          { label: 'Customer', key: 'customer' },
          { label: 'Address', key: 'address' },
          { label: 'Cadence', key: 'cadence' },
          { label: 'Anchor day/time', key: 'anchor' },
          { label: 'Next visit', key: 'nextVisit' },
          { label: 'Price', key: 'price' },
          { label: 'Route', key: 'route' },
          { label: 'Status', key: 'status' },
          { label: 'Payment', key: 'payment' },
          { label: 'Source', key: 'source' },
        ],
        [
          { id: 'SUB-2041', customer: 'Tom & Ana Roe', address: '4 High St ME17', cadence: '4-weekly', anchor: 'Tue 09:00–11:00', nextVisit: '11 Jun', price: '£136', route: 'Tue – Maidstone', status: 'Active', payment: 'Stripe · Active', source: 'Online booking' },
          { id: 'SUB-1880', customer: 'Luma Ltd', address: 'Unit 5 Blueprint Park', cadence: '6-weekly', anchor: 'Thu 12:00–14:00', nextVisit: '13 Jun', price: '£220', route: 'Thu – Commercial', status: 'Paused', payment: 'Past due', source: 'Manager created' },
        ],
        'Row actions: View · Pause · Cancel · Skip next visit · Change cadence · Change price.',
      ),
      detailCard('owner-subscriptions-create', 'Create / edit subscription', 'Step-by-step builder mirrored in UI.', {
        fields: [
          { label: 'Customer + property', value: 'Search existing, or start from address-first and attach a customer later.' },
          { label: 'Cadence & anchor', value: '4 / 6 / 8-week options with anchor day/time window.' },
          { label: 'Route suggestions', value: 'Auto-suggest route based on day + postcode.' },
          { label: 'Start date & first job', value: 'Choose first clean, optionally create the first job immediately.' },
          { label: 'Price & add-ons', value: 'Per-visit price plus add-ons with qty/price columns.' },
          { label: 'Duration', value: 'Ongoing or end after N visits/date.' },
          { label: 'Billing', value: 'Link to Stripe subscription/customer, include button “Create subscription in Stripe now”.' },
        ],
        footer: 'Actions: Save subscription · Create first job now if start date is today.'
      }),
      detailCard('owner-subscriptions-detail', 'Subscription detail view', 'Give owners a control center per recurring contract.', {
        fields: [
          { label: 'Summary', value: 'Customer/property, cadence, anchor, route, status, price, Stripe sub ID with Open in Stripe button.' },
          { label: 'Schedule preview', value: 'List next X visits with Skip / Move controls, show recalculated dates when cadence changes.' },
          { label: 'Controls', value: 'Pause/resume with reason + resume date, change cadence/day/time, change price/add-ons, cancel (immediate vs end of period).' },
          { label: 'Jobs history', value: 'Log of visits with status + job link.' },
          { label: 'Activity log', value: 'Created by, cadence/price changes, customer self-serve actions.' },
        ],
      }),
    ],
  }),
  createOwnerPage({
    key: 'owner.staff',
    route: '/owner/staff',
    title: 'Staff',
    description: 'Manage cleaners and office staff—their roles, availability and app access.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Operations',
      eyebrow: 'Staff',
      headline: 'Know who is on the team and what they can do',
      subhead: 'Cleaner rosters, office access, device info and onboarding sit here—not in Management.',
      primaryAction: { label: 'Add staff member', href: '/owner/staff/new', icon: 'bi-person-plus' },
      secondaryAction: { label: 'Send login links', href: '/owner/staff?bulk=login', icon: 'bi-send' },
      highlights: [
        { title: 'Cleaner focus', description: 'Routes, areas, vehicles and capacity live per profile.' },
        { title: 'App telemetry', description: 'Device info + last login for troubleshooting.' },
        { title: 'Role-aware permissions', description: 'See at-a-glance what a user can access.' },
      ],
    },
    sections: [
      summary(
        'owner-staff-kpis',
        'Staff snapshot',
        [
          { label: 'Active cleaners', value: '68', delta: '5 off this week' },
          { label: 'Office team', value: '9', delta: '3 managers · 2 accountants' },
          { label: 'Invited', value: '4 pending', delta: '2 expiring within 48h', badge: 'Action' },
          { label: 'Not seen >30 days', value: '3', delta: 'Send nudges' },
        ],
      ),
      dataTable(
        'owner-staff-list',
        'Staff directory',
        [
          { label: 'Name', key: 'name' },
          { label: 'Primary role', key: 'role' },
          { label: 'All roles', key: 'roles' },
          { label: 'Email', key: 'email' },
          { label: 'Phone', key: 'phone' },
          { label: 'Status', key: 'status' },
          { label: 'Routes / areas', key: 'routes' },
          { label: 'Work pattern', key: 'pattern' },
          { label: 'Last login', key: 'lastLogin' },
        ],
        [
          { name: 'Amelia Blake', role: 'Cleaner', roles: 'Cleaner · Trainer', email: 'amelia@aokworld.co', phone: '+44 7520 111 200', status: 'Active', routes: 'Tue Maidstone / Thu West', pattern: 'Mon–Fri 08:00–16:00', lastLogin: '3m ago' },
          { name: 'Jordan Malik', role: 'Manager', roles: 'Manager · Support', email: 'jordan@aokworld.co', phone: '+44 20 7123 9000', status: 'Active', routes: 'Dispatch board', pattern: 'Mon–Sat 07:00–15:00', lastLogin: '8m ago' },
          { name: 'Priya Shah', role: 'Cleaner', roles: 'Cleaner', email: 'priya@aokworld.co', phone: '+44 7890 123 456', status: 'Invited', routes: 'Southbank', pattern: 'Wed–Sun 09:00–17:00', lastLogin: '—' },
        ],
        'Filters: Role, status, working days, route/area, last seen.',
      ),
      detailCard('owner-staff-profile', 'Staff profile blueprint', 'Profiles adapt based on whether the person is field or office staff.', {
        fields: [
          { label: 'Basic info', value: 'Name, email, phone, photo, roles (checkbox list), status (Active / Invited / Suspended).' },
          { label: 'Work & availability', value: 'Work days, standard hours, max jobs/hours, primary routes, area postcodes, vehicle info, internal notes.' },
          { label: 'App & login', value: 'Latest device (platform, OS, app version), last login, 2FA status, buttons to send login link or show QR.' },
          { label: 'Pay & rates', value: 'Optional block for hourly/per-job rate, pay type, overtime rules (placeholder for finance tie-in).' },
          { label: 'Permissions', value: 'Readable summary: “Sees assigned jobs only”, “Can’t view customer contact”, “Can’t issue refunds”.' },
          { label: 'History & usage', value: 'Jobs completed, rating averages, no-show count, disciplinary notes.' },
        ],
      }),
      detailCard('owner-staff-actions', 'Key actions', 'Keep owner-ready shortcuts up top.', {
        fields: [
          { label: 'Send app invite / magic link', value: 'Push login via SMS/email in one click.' },
          { label: 'Suspend / delete', value: 'Suspend instantly, delete only when no active jobs are assigned.' },
          { label: 'Assign routes', value: 'Bulk assign cleaners to route templates or remove them when on leave.' },
        ],
        actions: [
          { label: 'Add staff member', href: '/owner/staff/new', icon: 'bi-person-plus', variant: 'primary' },
          { label: 'Export directory', href: '/owner/staff/export', icon: 'bi-cloud-arrow-down' },
        ],
      }),
    ],
  }),
  createOwnerPage({
    key: 'owner.staff.roster',
    route: '/owner/staff/roster',
    title: 'Staff roster',
    description: 'Plan the week ahead by staff or route, then push schedules out.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Operations',
      eyebrow: 'Staff roster',
      headline: 'Plan the next week without spreadsheets',
      subhead: 'Drag jobs onto cleaners, auto-assign gaps and export route sheets.',
      primaryAction: { label: 'Auto-assign unassigned jobs', href: '/owner/staff/roster?auto=1', icon: 'bi-magic' },
      secondaryAction: { label: 'Send schedules', href: '/owner/staff/roster#notify', icon: 'bi-send' },
      highlights: [
        { title: 'Staff vs route views', description: 'Switch perspectives instantly.' },
        { title: 'Drag and drop', description: 'Assign jobs or reorder stops with conflict warnings.' },
        { title: 'Exports', description: 'Print/export route sheets for semi-offline teams.' },
      ],
    },
    sections: [
      summary(
        'owner-roster-kpis',
        'Roster snapshot',
        [
          { label: 'Week viewed', value: '10–16 Jun', delta: '42 jobs scheduled' },
          { label: 'Unassigned jobs', value: '5', delta: 'Drag from panel to assign', badge: 'Action' },
          { label: 'Over-capacity staff', value: '2', delta: 'Warn owners before locking schedule' },
          { label: 'Schedules sent', value: '12/18', delta: 'Push notifications pending for 6' },
        ],
      ),
      detailCard('owner-roster-view', 'Views & filters', 'Roster can show Day or Week, by staff or by route.', {
        fields: [
          { label: 'View switch', value: 'Day / Week plus vertical orientation for tablets.' },
          { label: 'Perspective toggle', value: 'By staff (columns = cleaners) or by route (columns = routes).', hint: 'Persist user preference per account.' },
          { label: 'Filters', list: [{ label: 'Staff', value: 'Multi-select' }, { label: 'Route', value: 'Colour-coded' }, { label: 'Area', value: 'Postcode tags' }, { label: 'Job type', value: 'One-off / subscription' }], hint: 'Same filter pill UI as dispatch.' },
          { label: 'Top controls', value: 'Date picker, Auto-assign button, Print/export route sheets, Send schedules to staff.' },
        ],
      }),
      detailCard('owner-roster-staff-view', 'Roster · By staff', 'Columns show cleaners, rows show the day or timeline.', {
        fields: [
          { label: 'Job blocks', value: 'Show time window, customer, short address, route colour strip, status icon.' },
          { label: 'Unassigned panel', value: 'Left-hand list of jobs waiting for assignment. Drag job -> drop on staff/time slot.' },
          { label: 'Conflict handling', value: 'Warn if outside working hours or overlapping windows. Optionally auto-adjust times.' },
          { label: 'Per-staff summary', value: 'Jobs count, scheduled hours, estimated travel, capacity warnings at top of column.' },
        ],
      }),
      detailCard('owner-roster-route-view', 'Roster · By route', 'Columns = routes, rows = time/order.', {
        fields: [
          { label: 'Stop ordering', value: 'Drag to reorder stops without changing staff, or confirm if reassigning.' },
          { label: 'Cleaner context', value: 'Avatar on each job block plus tooltip with working hours.' },
        ],
      }),
      detailCard('owner-roster-interactions', 'Job & staff interactions', 'Clicks open quick controls.', {
        fields: [
          { label: 'Job drawer', value: 'Slide-out summary with actions: Open job, change cleaner, change date/time, move route.' },
          { label: 'Staff header', value: 'Shows jobs/hours for period, routes they’re on, button to edit working hours.' },
        ],
      }),
      detailCard('owner-roster-comms', 'Notifications & export', 'Owners still needing paper get it without leaving the page.', {
        fields: [
          { label: 'Send schedules', value: 'Choose channel (push, SMS, email), pick staff, send summary for next X days.' },
          { label: 'Print/export route sheets', value: 'Generate PDF/CSV with job order, addresses, notes, contact numbers.' },
        ],
        actions: [
          { label: 'Send schedules', href: '/owner/staff/roster#notify', icon: 'bi-send' },
          { label: 'Export routes', href: '/owner/staff/roster/export', icon: 'bi-printer' },
        ],
      }),
    ],
  }),
  createOwnerPage({
    key: 'owner.audit-log',
    route: '/owner/audit-log',
    title: 'Audit log',
    description: 'Sensitive actions log (who/when/what).',
    highlights: [
      { title: 'Search by user', description: 'Filter events by actor, resource, IP.' },
      { title: 'Export JSON', description: 'Send to SIEM or export JSON/CSV.' },
      { title: 'Retention controls', description: 'Align with /owner/data-retention policies.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.data-retention',
    route: '/owner/data-retention',
    title: 'Data retention',
    description: 'Configure location ping TTL, log retention, anonymisation.',
    highlights: [
      { title: 'Retention sliders', description: 'Set days for photos, locations, messages.' },
      { title: 'Anonymise tools', description: 'Bulk anonymise data per request.' },
      { title: 'Policy attestation', description: 'Track who approved retention rules.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.legal',
    route: '/owner/legal',
    title: 'Legal',
    description: 'Policies, DPA, privacy branding.',
    highlights: [
      { title: 'Document templates', description: 'Host custom DPAs, NDAs, policies.' },
      { title: 'Branding', description: 'Inject company logo/colors into legal docs.' },
      { title: 'E-sign support', description: 'Send updated terms for signature.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.branding',
    route: '/owner/branding',
    title: 'Branding',
    description: 'Logos, workspace shells, embeds and comms themes stay in sync here.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Brand lab',
      eyebrow: 'Branding',
      headline: 'Make every Glint surface feel like AOK World',
      subhead: 'Upload assets, toggle widgets and preview customer comms before shipping.',
      primaryAction: { label: 'Save branding', href: '/owner/branding#lab', icon: 'bi-save' },
      secondaryAction: { label: 'Reset to defaults', href: '/owner/branding/reset', icon: 'bi-arrow-counterclockwise' },
      highlights: [
        { title: 'Workspace shells', description: 'Owner, staff and customer portals share tokens.' },
        { title: 'Embeds', description: 'Quote widget + booking badge ready in seconds.' },
        { title: 'Comms theme', description: 'Emails + SMS mirror your palette automatically.' },
      ],
    },
    sections: [
      brandCustomizer('owner-branding-lab', 'Brand lab', 'Upload logos, tune colors, and push updates to every workspace shell.', {
        props: {
          saveUrl: '/owner/branding/update',
          showColorPanel: true,
        },
      }),
      detailCard('owner-branding-widgets', 'Feature badges & widgets', 'Toggle embeds customers see on your marketing site.', {
        fields: [
          { label: 'Quote widget', value: 'Enabled', state: 'success', hint: 'Live pricing embed on aokworld.co/pricing.', action: { label: 'Get embed code', href: '/owner/branding/widgets#quote', icon: 'bi-code-slash' } },
          { label: 'Status badge', value: 'Enabled', hint: 'Shows uptime + response time beside your CTA.', action: { label: 'Preview badge', href: '/owner/branding/widgets#status', icon: 'bi-eye' } },
          { label: 'Support chat', value: 'Disabled', state: 'warning', hint: 'Switch on concierge chat once ops is ready.', action: { label: 'Configure support chat', href: '/owner/integrations', icon: 'bi-chat-dots' } },
          { label: 'Booking flow', value: 'Enabled', meta: 'Connected to book.aokworld.co', action: { label: 'Configure booking flow', href: '/owner/domains', icon: 'bi-arrow-right-circle' } },
        ],
        footer: 'Each toggle controls JS embed availability + workspace entry points.',
        actions: [{ label: 'Manage embeds', href: '/owner/branding/widgets', icon: 'bi-braces' }],
      }),
      detailCard('owner-branding-assets', 'Logo & favicon', 'Asset health + previews before publishing.', {
        fields: [
          { label: 'Primary logo', value: 'aokworld-logo.svg', hint: 'SVG/PNG up to 3MB.', meta: 'Updated 04 Jun 2024', action: { label: 'Replace logo', href: '/owner/branding#logo', icon: 'bi-upload' } },
          { label: 'Favicon / icon', value: 'favicon-32.png', meta: 'Updated 04 Jun 2024', action: { label: 'Replace icon', href: '/owner/branding#favicon', icon: 'bi-upload' } },
          {
            label: 'Preview modes',
            list: [
              { label: 'Light mode', value: 'Pass' },
              { label: 'Dark mode', value: 'Pass' },
            ],
            hint: 'See how assets look inside the portal + system emails.',
          },
          { label: 'Accessibility checks', value: 'Contrast 7.2:1', state: 'success', meta: 'WCAG AA on buttons + nav', action: { label: 'Re-run check', href: '/owner/branding#a11y', icon: 'bi-check2-circle' } },
        ],
      }),
      detailCard('owner-branding-shells', 'Back to site link & workspace shells', 'Control the button customers click + how each shell feels.', {
        fields: [
          { label: 'Back to site link', value: 'https://www.aokworld.co', hint: 'Drives “Back to website” buttons and Domains page widget examples.', action: { label: 'Open link', href: 'https://www.aokworld.co', icon: 'bi-box-arrow-up-right' } },
          {
            label: 'Workspace shells',
            list: [
              { label: 'Owner', value: 'Midnight indigo (#120a3a → #1e165a)' },
              { label: 'Staff', value: 'Blue spruce (#0e2f45 → #134560)' },
              { label: 'Customer', value: 'Teal pulse (#014c3c → #00806a)' },
            ],
            hint: 'Sidebar gradients match each workspace and can be tuned independently.',
          },
        ],
        actions: [{ label: 'Adjust shells', href: '/owner/branding#shells', icon: 'bi-palette' }],
      }),
      detailCard('owner-branding-comm', 'Email & SMS theme', 'Preview transactional comms before they hit your customers.', {
        fields: [
          { label: 'Primary brand color', value: '#0f172a', meta: 'Headings, dividers + invoice frames.' },
          { label: 'Button color', value: '#4fe1c1', hint: 'Used on emails + booking CTAs.' },
          { label: 'SMS sender ID', value: 'AOKWORLD', meta: 'Uses Ofcom-compliant sender ID.' },
          { label: 'Preview', lines: ['Quote acceptance email', 'Reminder + tracking SMS'], hint: 'Update colors to refresh preview instantly.' },
        ],
        actions: [
          { label: 'Save branding', href: '/owner/branding/update', icon: 'bi-save', variant: 'primary' },
          { label: 'Reset to Glint defaults', href: '/owner/branding/reset', icon: 'bi-arrow-counterclockwise', variant: 'ghost' },
        ],
      }),
    ],
  }),
  createOwnerPage({
    key: 'owner.pricing',
    route: '/owner/pricing',
    title: 'Pricing',
    description: 'Rate cards, add-on pricing, coupons/promos.',
    highlights: [
      { title: 'Rate tables', description: 'Differentiate weekdays, weekends, deep cleans.' },
      { title: 'Promo codes', description: 'Create, limit, expire coupons.' },
      { title: 'Market adjustments', description: 'Add surcharges per zone or season.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.policies',
    route: '/owner/policies',
    title: 'Policies (owner level)',
    description: 'Tenant-level defaults, surcharge rules, overrides for manager policies.',
    highlights: [
      { title: 'Global defaults', description: 'Define overarching rules managers inherit.' },
      { title: 'Overrides', description: 'Allow exceptions for VIP customers or contracts.' },
      { title: 'Surcharge logic', description: 'Configure travel or premium surcharges.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.domains',
    route: '/owner/domains',
    title: 'Domains & website',
    description: 'Tell us your website once, then copy the Glint URLs + widget snippet we host for you.',
    includeSummary: false,
    includeInsights: false,
    hero: {
      badge: 'Routing',
      eyebrow: 'Domains & website',
      headline: 'Paste one URL, copy one script',
      subhead: 'We host every booking, login and tracking page at glintlabs.com so you never touch DNS.',
      primaryAction: { label: 'Save website', href: '/owner/branding', icon: 'bi-save' },
      secondaryAction: { label: 'Copy widget snippet', href: '#owner-domains-widget-script', icon: 'bi-clipboard' },
      highlights: [
        { title: 'Website aware', description: 'Branding + Domains both pull the same URL.' },
        { title: 'Hosted by Glint', description: 'Customer portal + login never require DNS work.' },
        { title: 'Copy-paste widgets', description: 'One script, one div, one login button.' },
      ],
    },
    sections: [
      detailCard('owner-domains-website', 'Connect your website', 'Paste your public site once and we route everything else for you.', {
        fields: [
          {
            label: 'Your website URL',
            value: 'https://www.aokworld.co.uk',
            hint: 'Used for “Back to website” buttons and widget instructions.',
            actions: [
              { label: 'Save website', icon: 'bi-save' },
              { label: 'Open site', href: 'https://www.aokworld.co.uk', icon: 'bi-box-arrow-up-right' },
            ],
          },
          {
            label: 'Company slug',
            value: 'aok-world',
            hint: 'Generated once from your business name. Powers every Glint URL.',
            action: { label: 'Copy slug', icon: 'bi-clipboard' },
          },
          {
            label: 'What we do with it',
            lines: [
              'Back to website buttons pull this URL automatically.',
              'Widget snippets on this page are prefilled with your slug + tenant ID.',
              'No CNAME, TXT, or DNS wizardry needed.',
            ],
          },
        ],
        footer: 'Editing the website here also updates the Branding page input.',
      }),
      detailCard('owner-domains-urls', 'Customer URLs powered by Glint', 'Share these hosted links with customers or staff.', {
        fields: [
          {
            label: 'Customer login & tracking',
            value: 'https://aok-world.glintlabs.com',
            hint: 'Send this to customers so they can log in, see upcoming cleans and track their cleaner.',
            actions: [
              { label: 'Copy URL', icon: 'bi-clipboard' },
              { label: 'Open', href: 'https://aok-world.glintlabs.com', icon: 'bi-box-arrow-up-right' },
            ],
          },
          {
            label: 'Booking page',
            value: 'https://aok-world.glintlabs.com/book',
            hint: 'Direct link to your booking + quote flow inside the customer portal.',
            actions: [
              { label: 'Copy URL', icon: 'bi-clipboard' },
              { label: 'Open', href: 'https://aok-world.glintlabs.com/book', icon: 'bi-box-arrow-up-right' },
            ],
          },
          {
            label: 'Manage subscription',
            value: 'https://aok-world.glintlabs.com/manage',
            hint: 'Customers can adjust schedules, cards and add-ons.',
            actions: [
              { label: 'Copy URL', icon: 'bi-clipboard' },
              { label: 'Open', href: 'https://aok-world.glintlabs.com/manage', icon: 'bi-box-arrow-up-right' },
            ],
          },
        ],
        footer: 'We host every route on glintlabs.com so SSL, routing and uptime are on us.',
      }),
      detailCard('owner-domains-widget-intro', 'Add Glint to your website', 'One script, one div, one login button for the full booking + quote widget.', {
        fields: [
          { label: 'Step 1', value: 'Include the script just before </body> on your site.', hint: 'We preload tenant + slug IDs for you.' },
          { label: 'Step 2', value: 'Place the widget container where you want the booking form to appear.', hint: 'The script mounts onto #glint-booking-widget.' },
          { label: 'Step 3', value: 'Add a Customer login / tracking button that links to your Glint portal.', hint: 'Style it however you like—just keep the href.' },
        ],
        actions: [{ label: 'Copy everything', icon: 'bi-clipboard' }],
      }),
      codeSnippet(
        'owner-domains-widget-script',
        'Step 1 · Include the script',
        'Paste this near the bottom of your page so we can boot the widget.',
        `<!-- Glint booking & quote widget -->\n<script\n  src="https://widgets.glintlabs.com/quote.js"\n  data-glint-tenant-id="TEN-49218"\n  data-glint-company-slug="aok-world"\n  async\n></script>`,
      ),
      codeSnippet(
        'owner-domains-widget-container',
        'Step 2 · Place the widget container',
        'Drop this div wherever you want the booking flow to appear.',
        `<!-- Where the booking widget should appear -->\n<div id="glint-booking-widget"></div>`,
      ),
      codeSnippet(
        'owner-domains-widget-button',
        'Step 3 · Add a customer login & tracking button',
        'Link straight to your hosted portal. Style however you like.',
        `<!-- Customer login & tracking button -->\n<a\n  href="https://aok-world.glintlabs.com"\n  class="glint-login-button"\n>\n  Customer login & tracking\n</a>`,
      ),
      detailCard('owner-domains-status', 'Widget status', 'We watch for widget calls so you know if it’s live.', {
        fields: [
          { label: 'Last seen on website', value: 'Loaded from https://www.aokworld.co.uk · 2 hours ago', hint: 'Based on widget pings referencing document.referrer.', state: 'success' },
          { label: 'Detected pages', list: [{ label: 'Homepage', value: '/' }, { label: 'Cleaning services', value: '/cleaning-services/' }], hint: 'We’ll list up to 5 recent paths.' },
          { label: 'If we haven’t seen it yet', value: 'This card will remind you to add the script until we detect traffic. No DNS troubleshooting required.', state: 'warning' },
        ],
      }),
    ],
  }),
  createOwnerPage({
    key: 'owner.api-keys',
    route: '/owner/api-keys',
    title: 'API keys',
    description: 'Tenant API access (scoped).',
    highlights: [
      { title: 'Key scopes', description: 'Limit keys to read-only, bookings, or billing access.' },
      { title: 'Rotation reminders', description: 'Auto-expiry + remind to rotate.' },
      { title: 'Usage logs', description: 'Track which integrations call the API.' },
    ],
  }),
  createOwnerPage({
    key: 'owner.integrations',
    route: '/owner/integrations',
    title: 'Integrations',
    description: 'Accounting (Xero/QB), webhooks, Zapier connector.',
    highlights: [
      { title: 'Xero/QB connectors', description: 'OAuth flows + sync settings.' },
      { title: 'Zapier templates', description: 'Pre-built zaps for bookings/invoices.' },
      { title: 'Webhook hub', description: 'Global settings for event delivery + signatures.' },
    ],
  }),
]

export function registerOwnerPages() {
  ownerPages.forEach(page => definePage(page))
}
