export const roleNav = {
  customer: {
    primary: [
      {
        label: 'Glow plan',
        items: [
          { label: 'Dashboard', href: '/customer/dashboard', icon: 'bi-sun' },
          { label: 'Minutes wallet', href: '/customer/minutes', icon: 'bi-hourglass-split' },
          { label: 'My courses', href: '/customer/courses', icon: 'bi-collection-play' },
          { label: 'Bookings', href: '/customer/bookings', icon: 'bi-calendar-heart' },
        ],
      },
      {
        label: 'Account',
        items: [
          { label: 'Membership', href: '/customer/membership', icon: 'bi-star' },
          { label: 'Payments', href: '/customer/payments', icon: 'bi-credit-card' },
          { label: 'Preferences', href: '/customer/preferences', icon: 'bi-sliders' },
          { label: 'Documents', href: '/customer/documents', icon: 'bi-file-earmark-text' },
          { label: 'Support', href: '/customer/support', icon: 'bi-life-preserver' },
        ],
      },
    ],
  },
  cleaner: {
    primary: [
      {
        label: 'Studio shift',
        items: [
          { label: 'Shift board', href: '/cleaner/today', icon: 'bi-sunrise' },
          { label: 'Client notes', href: '/cleaner/clients', icon: 'bi-journal-richtext' },
          { label: 'Courses & promos', href: '/cleaner/courses', icon: 'bi-bag-heart' },
          { label: 'Bed health', href: '/cleaner/bed-health', icon: 'bi-activity' },
        ],
      },
      {
        label: 'Me',
        items: [
          { label: 'Inbox', href: '/cleaner/inbox', icon: 'bi-chat-dots' },
          { label: 'Earnings', href: '/cleaner/earnings', icon: 'bi-cash-stack' },
          { label: 'Settings', href: '/cleaner/settings', icon: 'bi-gear' },
          { label: 'Offline kit', href: '/cleaner/offline', icon: 'bi-wifi-off' },
        ],
      },
    ],
  },
  manager: {
    primary: [
      {
        label: 'Studio ops',
        items: [
          { label: 'Overview', href: '/manager/overview', icon: 'bi-speedometer2' },
          { label: 'Calendar', href: '/manager/calendar', icon: 'bi-calendar3' },
          { label: 'Waitlist', href: '/manager/waitlist', icon: 'bi-people' },
          { label: 'Multi-room view', href: '/manager/multiroom', icon: 'bi-columns-gap' },
        ],
      },
      {
        label: 'Courses & minutes',
        items: [
          { label: 'Course designer', href: '/manager/courses', icon: 'bi-magic' },
          { label: 'Bundles', href: '/manager/bundles', icon: 'bi-gift' },
          { label: 'Stock & lamps', href: '/manager/stock', icon: 'bi-box-seam' },
          { label: 'Compliance', href: '/manager/compliance', icon: 'bi-shield-check' },
        ],
      },
      {
        label: 'Customers & growth',
        items: [
          { label: 'Customers', href: '/manager/customers', icon: 'bi-person-hearts' },
          { label: 'Memberships', href: '/manager/membership', icon: 'bi-stars' },
          { label: 'Marketing', href: '/manager/marketing', icon: 'bi-megaphone' },
        ],
      },
      {
        label: 'People & finance',
        items: [
          { label: 'Staff', href: '/manager/staff', icon: 'bi-person-badge' },
          { label: 'Schedules', href: '/manager/schedules', icon: 'bi-calendar2-week' },
          { label: 'Settlements', href: '/manager/settlements', icon: 'bi-bank' },
          { label: 'Settings', href: '/manager/settings', icon: 'bi-gear-wide-connected' },
        ],
      },
    ],
  },
  owner: {
    primary: [
      {
        label: 'Portfolio',
        items: [
          { label: 'Owner overview', href: '/owner/overview', icon: 'bi-stars' },
          { label: 'Studios', href: '/owner/portfolio', icon: 'bi-building' },
          { label: 'Performance', href: '/owner/performance', icon: 'bi-graph-up' },
        ],
      },
      {
        label: 'Brand & CX',
        items: [
          { label: 'Branding', href: '/owner/brand', icon: 'bi-palette' },
          { label: 'Experience kit', href: '/owner/experience', icon: 'bi-body-text' },
        ],
      },
      {
        label: 'Controls',
        items: [
          { label: 'Finance', href: '/owner/finance', icon: 'bi-piggy-bank' },
          { label: 'Security', href: '/owner/security', icon: 'bi-shield-lock' },
          { label: 'Integrations', href: '/owner/integrations', icon: 'bi-plug' },
          { label: 'Audit log', href: '/owner/audit', icon: 'bi-clipboard-data' },
        ],
      },
    ],
  },
  accountant: {
    primary: [
      {
        label: 'Finance',
        items: [
          { label: 'Revenue pulse', href: '/accountant/overview', icon: 'bi-cash-coin' },
          { label: 'Payouts', href: '/accountant/payouts', icon: 'bi-bank' },
          { label: 'Reconciliation', href: '/accountant/reconciliation', icon: 'bi-columns' },
          { label: 'Fees & taxes', href: '/accountant/fees', icon: 'bi-percent' },
          { label: 'Disputes', href: '/accountant/disputes', icon: 'bi-shield-exclamation' },
          { label: 'Exports', href: '/accountant/export', icon: 'bi-filetype-csv' },
        ],
      },
    ],
  },
  support: {
    primary: [
      {
        label: 'Care desk',
        items: [
          { label: 'Inbox', href: '/support/inbox', icon: 'bi-inbox' },
          { label: 'Customer lookup', href: '/support/customers', icon: 'bi-search' },
          { label: 'Studio feed', href: '/support/studios', icon: 'bi-broadcast-pin' },
          { label: 'Tools', href: '/support/tools', icon: 'bi-wrench' },
        ],
      },
    ],
  },
  glint: {
    primary: [
      {
        label: 'Platform control',
        items: [
          { label: 'Tenants', href: '/glint/tenants', icon: 'bi-diagram-3' },
          { label: 'Studios', href: '/glint/studios', icon: 'bi-house-door' },
          { label: 'Staff', href: '/glint/staff', icon: 'bi-person-badge' },
          { label: 'Customers', href: '/glint/customers', icon: 'bi-people' },
        ],
      },
      {
        label: 'Billing & policy',
        items: [
          { label: 'Plans', href: '/glint/billing', icon: 'bi-layers' },
          { label: 'Settlements', href: '/glint/settlements', icon: 'bi-bank' },
          { label: 'Templates', href: '/glint/templates', icon: 'bi-journal-richtext' },
        ],
      },
      {
        label: 'Reliability',
        items: [
          { label: 'Health', href: '/glint/health', icon: 'bi-activity' },
          { label: 'Incidents', href: '/glint/incidents', icon: 'bi-lightning-charge' },
          { label: 'Growth', href: '/glint/growth', icon: 'bi-graph-up-arrow' },
        ],
      },
    ],
  },
}
