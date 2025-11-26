
export const roleNav = {
  customer: {
    primary: [
      {
        label: 'My Cleans',
        items: [
          { label: 'Dashboard', href: '/customer/dashboard', icon: 'bi-speedometer2' },
          { label: 'All cleans', href: '/customer/cleans', icon: 'bi-list-check' },
          { label: 'Track live', href: '/customer/track/next', icon: 'bi-map' },
        ],
      },
      {
        label: 'Account',
        items: [
          { label: 'Addresses', href: '/customer/addresses', icon: 'bi-geo' },
          { label: 'Billing', href: '/customer/billing', icon: 'bi-credit-card' },
          { label: 'Invoices', href: '/customer/invoices', icon: 'bi-receipt' },
          { label: 'Preferences', href: '/customer/preferences', icon: 'bi-sliders' },
          { label: 'Security', href: '/customer/security', icon: 'bi-shield-lock' },
          { label: 'Support', href: '/customer/support', icon: 'bi-life-preserver', feature: 'support' },
        ],
      },
    ],
  },
  cleaner: {
    primary: [
      {
        label: 'Today',
        items: [
          { label: 'Route timeline', href: '/cleaner/today', icon: 'bi-route' },
          { label: 'Job history', href: '/cleaner/history', icon: 'bi-clock-history' },
          { label: 'Inbox', href: '/cleaner/inbox', icon: 'bi-inbox' },
        ],
      },
      {
        label: 'My account',
        items: [
          { label: 'Earnings', href: '/cleaner/earnings', icon: 'bi-cash-stack' },
          { label: 'Settings', href: '/cleaner/settings', icon: 'bi-gear' },
          { label: 'Offline sync', href: '/cleaner/offline', icon: 'bi-cloud-arrow-up' },
        ],
      },
    ],
  },
  manager: {
    primary: [
      {
        label: 'Dispatch',
        items: [
          { label: 'Board', href: '/manager/dispatch/board', icon: 'bi-kanban' },
          { label: 'Routes', href: '/manager/dispatch/routes', icon: 'bi-signpost-split' },
          { label: 'Exceptions', href: '/manager/dispatch/exceptions', icon: 'bi-exclamation-octagon' },
          { label: 'Bulk actions', href: '/manager/dispatch/bulk', icon: 'bi-grid' },
        ],
      },
      {
        label: 'Jobs & customers',
        items: [
          { label: 'Jobs', href: '/manager/jobs', icon: 'bi-briefcase' },
          { label: 'New job', href: '/manager/jobs/new', icon: 'bi-plus-circle' },
          { label: 'Checklists', href: '/manager/checklists', icon: 'bi-ui-checks' },
          { label: 'Add-ons', href: '/manager/addons', icon: 'bi-bag-plus' },
          { label: 'Customers', href: '/manager/customers', icon: 'bi-people' },
          { label: 'Subscriptions', href: '/manager/subscriptions', icon: 'bi-repeat' },
        ],
      },
      {
        label: 'People & finance',
        items: [
          { label: 'Staff roster', href: '/manager/staff', icon: 'bi-person-badge' },
          { label: 'Shifts', href: '/manager/shifts', icon: 'bi-calendar2-week' },
          { label: 'Announcements', href: '/manager/announcements', icon: 'bi-megaphone' },
          { label: 'Refunds', href: '/manager/refunds', icon: 'bi-arrow-counterclockwise' },
          { label: 'Adjustments', href: '/manager/adjustments', icon: 'bi-journal-plus' },
        ],
      },
      {
        label: 'Insights',
        items: [
          { label: 'Live map', href: '/manager/live/map', icon: 'bi-geo-alt' },
          { label: 'Live timeline', href: '/manager/live/timeline', icon: 'bi-activity' },
          { label: 'Ops report', href: '/manager/reports/operations', icon: 'bi-graph-up' },
          { label: 'Quality report', href: '/manager/reports/quality', icon: 'bi-star' },
          { label: 'Volume report', href: '/manager/reports/volume', icon: 'bi-diagram-3' },
        ],
      },
      {
        label: 'Settings',
        items: [
          { label: 'Policies', href: '/manager/settings/policies', icon: 'bi-shield' },
          { label: 'Notifications', href: '/manager/settings/notifications', icon: 'bi-chat-square-text' },
          { label: 'Integrations', href: '/manager/settings/integrations', icon: 'bi-plug' },
        ],
      },
    ],
  },
  owner: {
    primary: [
      {
        label: 'Company',
        items: [
          { label: 'Company overview', href: '/owner/overview', icon: 'bi-speedometer2' },
          { label: 'Branding', href: '/owner/branding', icon: 'bi-palette' },
          { label: 'Domains', href: '/owner/domains', icon: 'bi-globe' },
          { label: 'Management', href: '/owner/roles', icon: 'bi-people' },
        ],
      },
      {
        label: 'Operations',
        items: [
          { label: 'Dispatch board', href: '/owner/dispatch/board', icon: 'bi-kanban' },
          { label: 'Routes', href: '/owner/dispatch/routes', icon: 'bi-signpost-split' },
          { label: 'Jobs', href: '/owner/jobs', icon: 'bi-briefcase' },
          { label: 'Customers', href: '/owner/customers', icon: 'bi-people' },
          { label: 'Subscriptions', href: '/owner/subscriptions', icon: 'bi-repeat' },
          { label: 'Staff', href: '/owner/staff', icon: 'bi-person-badge' },
          { label: 'Staff roster', href: '/owner/staff/roster', icon: 'bi-calendar2-week' },
        ],
      },
      {
        label: 'Finance',
        items: [
          { label: 'Stripe Connect', href: '/owner/billing/stripe', icon: 'bi-credit-card' },
          { label: 'Invoices', href: '/owner/invoices', icon: 'bi-receipt' },
          { label: 'Payouts', href: '/owner/payouts', icon: 'bi-bank' },
          { label: 'Taxes', href: '/owner/taxes', icon: 'bi-percent' },
          { label: 'Chargebacks', href: '/owner/chargebacks', icon: 'bi-shield-exclamation' },
        ],
      },
      {
        label: 'Controls',
        items: [
          { label: 'Policies', href: '/owner/policies', icon: 'bi-journal-text' },
          { label: 'API keys', href: '/owner/api-keys', icon: 'bi-code-slash' },
          { label: 'Integrations', href: '/owner/integrations', icon: 'bi-plug' },
          { label: 'Legal', href: '/owner/legal', icon: 'bi-bag-check' },
          { label: 'Audit log', href: '/owner/audit-log', icon: 'bi-clipboard-data' },
          { label: 'Data retention', href: '/owner/data-retention', icon: 'bi-database' },
        ],
      },
    ],
  },
  accountant: {
    primary: [
      {
        label: 'Finance',
        items: [
          { label: 'Invoices', href: '/accountant/invoices', icon: 'bi-receipt' },
          { label: 'Payments', href: '/accountant/payments', icon: 'bi-credit-card2-back' },
          { label: 'Payouts', href: '/accountant/payouts', icon: 'bi-bank' },
          { label: 'Taxes', href: '/accountant/taxes', icon: 'bi-percent' },
          { label: 'Adjustments', href: '/accountant/adjustments', icon: 'bi-journal-plus' },
          { label: 'Disputes', href: '/accountant/disputes', icon: 'bi-shield-exclamation' },
          { label: 'Exports', href: '/accountant/exports', icon: 'bi-cloud-arrow-down' },
        ],
      },
    ],
  },
  support: {
    primary: [
      {
        label: 'Care desk',
        items: [
          { label: 'Tickets', href: '/support/tickets', icon: 'bi-inbox' },
          { label: 'Customer lookup', href: '/support/customers/latest', icon: 'bi-search' },
          { label: 'Job lookup', href: '/support/jobs/last', icon: 'bi-geo' },
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
          { label: 'Companies', href: '/glint/companies', icon: 'bi-buildings' },
          { label: 'Customers', href: '/glint/customers', icon: 'bi-people' },
          { label: 'Staff', href: '/glint/staff', icon: 'bi-person-badge' },
          { label: 'Jobs', href: '/glint/jobs', icon: 'bi-briefcase' },
          { label: 'Platform overview', href: '/glint/platform', icon: 'bi-diagram-3' },
        ],
      },
      {
        label: 'Health',
        items: [
          { label: 'Queues', href: '/glint/health/queues', icon: 'bi-server' },
          { label: 'Webhooks', href: '/glint/health/webhooks', icon: 'bi-wifi' },
          { label: 'Services', href: '/glint/health/services', icon: 'bi-hdd-network' },
          { label: 'Incidents', href: '/glint/incidents', icon: 'bi-lightning' },
        ],
      },
      {
        label: 'Billing',
        items: [
          { label: 'Plans', href: '/glint/billing/plans', icon: 'bi-layers' },
          { label: 'Fees', href: '/glint/billing/fees', icon: 'bi-cash-coin' },
          { label: 'Settlements', href: '/glint/billing/settlements', icon: 'bi-bank' },
        ],
      },
      {
        label: 'Compliance',
        items: [
          { label: 'Audit', href: '/glint/audit', icon: 'bi-clipboard-data' },
          { label: 'SAR', href: '/glint/gdpr/sar', icon: 'bi-envelope-open' },
          { label: 'Data exports', href: '/glint/data-exports', icon: 'bi-cloud-arrow-down' },
          { label: 'Retention', href: '/glint/retention', icon: 'bi-archive' },
          { label: 'Security', href: '/glint/security', icon: 'bi-shield-lock' },
          { label: 'Abuse', href: '/glint/abuse', icon: 'bi-shield-exclamation' },
        ],
      },
      {
        label: 'Features',
        items: [
          { label: 'Feature flags', href: '/glint/feature-flags', icon: 'bi-toggle2-on' },
          { label: 'Templates', href: '/glint/templates', icon: 'bi-card-text' },
          { label: 'Checklists', href: '/glint/checklists', icon: 'bi-ui-checks' },
          { label: 'Maps', href: '/glint/maps', icon: 'bi-geo-alt' },
          { label: 'CMS', href: '/glint/cms', icon: 'bi-newspaper' },
          { label: 'Metrics', href: '/glint/metrics', icon: 'bi-graph-up' },
          { label: 'Logs', href: '/glint/logs', icon: 'bi-terminal' },
        ],
      },
    ],
  },
}
