import { definePage } from '../registry'
import { summary, dataTable, actionGrid, checklist, insights } from '../helpers'

const financeSummary = [
  { label: 'Gross sales (7d)', value: '£118k', delta: '+6% vs prior' },
  { label: 'Refunds', value: '£2.8k', delta: '2.4%' },
  { label: 'Fees', value: '£4.1k', delta: '3.5%' },
]

const createAccountantPage = spec => ({
  key: spec.key,
  route: spec.route,
  layout: 'workspace',
  role: 'accountant',
  badge: 'Finance suite',
  title: spec.title,
  description: spec.description,
  sections: [
    summary(`${spec.key}-summary`, 'Finance pulse', spec.summaryCards || financeSummary),
    ...(spec.sections || []),
  ],
})

const accountantPages = [
  createAccountantPage({
    key: 'accountant.overview',
    route: '/accountant/overview',
    title: 'Revenue pulse',
    description: 'Sales, refunds, tips, and exposure credits at a glance.',
    sections: [
      dataTable('accountant-overview-metrics', 'Breakdown', [
        { label: 'Stream', key: 'stream' },
        { label: 'Amount', key: 'amount', align: 'right' },
        { label: 'Trend', key: 'trend', align: 'right' },
      ], [
        { stream: 'Courses', amount: '£92k', trend: '+9%' },
        { stream: 'Retail & boosters', amount: '£14k', trend: '+12%' },
        { stream: 'Membership fees', amount: '£12k', trend: '+3%' },
      ]),
      actionGrid('accountant-overview-actions', 'Actions', [
        { label: 'Lock period', description: 'Close books for last week and generate summary.', icon: 'bi-lock' },
        { label: 'Push to ERP', description: 'Sync ledger entries to Xero/NetSuite.', icon: 'bi-cloud-arrow-up' },
        { label: 'Trigger forecast', description: 'Update cash model with latest run rate.', icon: 'bi-graph-up' },
      ]),
    ],
  }),
  createAccountantPage({
    key: 'accountant.payouts',
    route: '/accountant/payouts',
    title: 'Payouts',
    description: 'Stripe & Klarna settlements, payroll batches, and vendor wires.',
    sections: [
      dataTable('accountant-payouts-table', 'Upcoming payouts', [
        { label: 'Date', key: 'date' },
        { label: 'Type', key: 'type' },
        { label: 'Amount', key: 'amount', align: 'right' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { date: '10 May', type: 'Stripe', amount: '£42,100', status: 'Scheduled' },
        { date: '11 May', type: 'Payroll', amount: '£18,430', status: 'Draft' },
        { date: '12 May', type: 'Vendor – Lamps', amount: '£6,850', status: 'Needs approval' },
      ]),
      checklist('accountant-payouts-checklist', 'Before approving', [
        { label: 'Match to ledger', detail: 'Ensure payout reconciles to net sales.' },
        { label: 'Fees accounted', detail: 'Include Stripe/Klarna fees + adjustments.' },
        { label: 'Cash buffer', detail: 'Warn if payout would dip below policy.' },
      ]),
    ],
  }),
  createAccountantPage({
    key: 'accountant.reconciliation',
    route: '/accountant/reconciliation',
    title: 'Reconciliation',
    description: 'Match bank deposits to Lux ledgers, highlight gaps.',
    sections: [
      dataTable('accountant-recon-breaks', 'Breaks', [
        { label: 'Reference', key: 'ref' },
        { label: 'Amount', key: 'amount', align: 'right' },
        { label: 'Reason', key: 'reason' },
        { label: 'Status', key: 'status', align: 'right' },
      ], [
        { ref: 'PAYOUT-5881', amount: '£-312', reason: 'Pending refund', status: 'Resolving' },
        { ref: 'CARD-4428', amount: '£+64', reason: 'Tip included twice', status: 'Manual' },
      ]),
      actionGrid('accountant-recon-actions', 'Tools', [
        { label: 'Auto-match', description: 'Run machine match between bank + ledger.', icon: 'bi-magic' },
        { label: 'Export adjustments', description: 'Send diff file for review.', icon: 'bi-filetype-csv' },
        { label: 'Flag suspicious', description: 'Escalate to owner or support with context.', icon: 'bi-shield' },
      ]),
    ],
  }),
  createAccountantPage({
    key: 'accountant.fees',
    route: '/accountant/fees',
    title: 'Fees & taxes',
    description: 'Stripe/Klarna fees, VAT, payroll tax, and lamp depreciation modelling.',
    sections: [
      dataTable('accountant-fees-table', 'Current obligations', [
        { label: 'Type', key: 'type' },
        { label: 'Amount', key: 'amount', align: 'right' },
        { label: 'Due', key: 'due' },
      ], [
        { type: 'VAT (UK)', amount: '£24,110', due: '31 May' },
        { type: 'Stripe fees', amount: '£4,130', due: 'Settled daily' },
        { type: 'Lamp depreciation', amount: '£3,420', due: 'Non-cash' },
      ]),
      insights('accountant-fees-insights', 'Optimisations', [
        { title: 'Klarna incentives', description: 'Reduced fees when >£40k/mo processed.' },
        { title: 'Lamp write-off schedule', description: 'Align depreciation with new Solar Club lounge investment.' },
      ]),
    ],
  }),
  createAccountantPage({
    key: 'accountant.disputes',
    route: '/accountant/disputes',
    title: 'Disputes & refunds',
    description: 'Chargebacks, partial refunds, and goodwill credits.',
    sections: [
      dataTable('accountant-disputes-table', 'Open disputes', [
        { label: 'Case', key: 'case' },
        { label: 'Guest', key: 'guest' },
        { label: 'Amount', key: 'amount', align: 'right' },
        { label: 'Due', key: 'due' },
      ], [
        { case: 'CB-2218', guest: 'Isla Rose', amount: '£169', due: '12 May' },
        { case: 'CB-2219', guest: 'Kai Hart', amount: '£89', due: '14 May' },
      ]),
      actionGrid('accountant-disputes-actions', 'Resolution toolkit', [
        { label: 'Attach evidence', description: 'Pull consent + lamp logs automatically.', icon: 'bi-paperclip' },
        { label: 'Issue partial credit', description: 'Credit minutes or cash with approvals.', icon: 'bi-piggy-bank' },
        { label: 'Escalate to support', description: 'Loop in concierge for white-glove handling.', icon: 'bi-life-preserver' },
      ]),
    ],
  }),
  createAccountantPage({
    key: 'accountant.export',
    route: '/accountant/export',
    title: 'Exports & audit kit',
    description: 'Download journals, tax-ready summaries, and supporting docs.',
    sections: [
      actionGrid('accountant-export-actions', 'Available exports', [
        { label: 'Daily journal', description: 'CSV + JSON for ERP import.', icon: 'bi-file-earmark-spreadsheet' },
        { label: 'VAT summary', description: 'Per region with evidence links.', icon: 'bi-receipt' },
        { label: 'Audit bundle', description: 'Encrypted package with consents + payouts.', icon: 'bi-archive' },
      ]),
      checklist('accountant-export-checklist', 'Best practices', [
        { label: 'Rotate access keys', detail: 'Exports expire in 24h.' },
        { label: 'Track downloads', detail: 'All exports logged in audit trail.' },
      ]),
    ],
  }),
]

export function registerAccountantPages() {
  accountantPages.forEach(page => definePage(page))
}
