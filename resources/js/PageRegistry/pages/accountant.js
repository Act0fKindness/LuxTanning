import { definePage } from '../registry'
import { summary, insights, dataTable, actionGrid } from '../helpers'

const summaryDefaults = [
  { label: 'Invoices due', value: '38', delta: '£12.4k outstanding' },
  { label: 'Payouts pending', value: '£28k', delta: 'Next run Fri' },
  { label: 'Disputes open', value: '7', delta: '3 due today' },
]

const createAccountantPage = input => ({
  key: input.key,
  route: input.route,
  layout: 'workspace',
  role: 'accountant',
  badge: 'Finance desk',
  title: input.title,
  description: input.description,
  sections: [
    summary(`${input.key}-summary`, 'Finance snapshot', summaryDefaults),
    insights(`${input.key}-insights`, 'Controls', input.highlights),
    ...(input.sections || []),
  ],
})

const accountantPages = [
  createAccountantPage({
    key: 'accountant.invoices',
    route: '/accountant/invoices',
    title: 'Invoices',
    description: 'List/filter invoices, resend, refund.',
    highlights: [
      { title: 'Filters & tags', description: 'Status, tenant, payment method, customer.' },
      { title: 'Bulk resend', description: 'Select invoices to resend to contacts.' },
      { title: 'Refund shortcuts', description: 'Initiate partial/full refunds with approvals.' },
    ],
    sections: [
      dataTable('accountant-invoice-table', 'Recent invoices', [
        { label: 'Invoice', key: 'id' },
        { label: 'Customer', key: 'customer' },
        { label: 'Status', key: 'status' },
        { label: 'Amount', key: 'amount', align: 'right' },
      ], [
        { id: '#INV-1093', customer: 'Priya', status: 'Paid', amount: '£142.00' },
        { id: '#INV-1092', customer: 'Marcus', status: 'Due', amount: '£98.00' },
      ]),
    ],
  }),
  createAccountantPage({
    key: 'accountant.payments',
    route: '/accountant/payments',
    title: 'Payments',
    description: 'Transactions, declines, retries.',
    highlights: [
      { title: 'Live ledger', description: 'All card/bank transactions with status.' },
      { title: 'Retry controls', description: 'Trigger retries or send pay links.' },
      { title: 'Decline reasons', description: 'Group by failure reason to fix underlying issues.' },
    ],
  }),
  createAccountantPage({
    key: 'accountant.payouts',
    route: '/accountant/payouts',
    title: 'Payouts',
    description: 'Reconciliation with Stripe transfers.',
    highlights: [
      { title: 'Transfer view', description: 'Match jobs to Stripe transfers.' },
      { title: 'Fees detail', description: 'Break out platform fees, refunds, adjustments.' },
      { title: 'Export', description: 'Push to accounting system.' },
    ],
  }),
  createAccountantPage({
    key: 'accountant.taxes',
    route: '/accountant/taxes',
    title: 'Taxes',
    description: 'VAT rates, reports, exports.',
    highlights: [
      { title: 'Rate library', description: 'Configure per jurisdiction.' },
      { title: 'Return builder', description: 'Aggregate taxable revenue by period.' },
      { title: 'Evidence kit', description: 'Store exemption evidence or customer VAT IDs.' },
    ],
  }),
  createAccountantPage({
    key: 'accountant.adjustments',
    route: '/accountant/adjustments',
    title: 'Adjustments',
    description: 'Credits/debits ledger.',
    highlights: [
      { title: 'Ledger filters', description: 'Find adjustments by user or customer.' },
      { title: 'Bulk adjustments', description: 'Apply credits to groups.' },
      { title: 'Audit log', description: 'Every change tracked with approvals.' },
    ],
  }),
  createAccountantPage({
    key: 'accountant.disputes',
    route: '/accountant/disputes',
    title: 'Disputes',
    description: 'Chargeback handling and evidence upload.',
    highlights: [
      { title: 'Deadline tracker', description: 'Keep track of response deadlines.' },
      { title: 'Evidence templates', description: 'Checklist for photos, checklists, comms.' },
      { title: 'Coordinate with owners', description: 'Loop in owner for high-value disputes.' },
    ],
  }),
  createAccountantPage({
    key: 'accountant.exports',
    route: '/accountant/exports',
    title: 'Exports',
    description: 'Generate CSVs for accounting systems.',
    highlights: [
      { title: 'Scheduled exports', description: 'Automate weekly/monthly pushes.' },
      { title: 'QuickBooks/Xero', description: 'Direct integration support.' },
      { title: 'Custom fields', description: 'Map Glint data to accounting fields.' },
    ],
    sections: [
      actionGrid('accountant-export-actions', 'Export templates', [
        { label: 'Sales journal', description: 'Invoices + payments summary.', icon: 'bi-file-earmark-spreadsheet' },
        { label: 'Payout reconciliation', description: 'Stripe transfers vs. ledger.', icon: 'bi-bank' },
        { label: 'Tax report', description: 'VAT-ready CSV.', icon: 'bi-percent' },
      ]),
    ],
  }),
]

export function registerAccountantPages() {
  accountantPages.forEach(page => definePage(page))
}
