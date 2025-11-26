import { definePage } from '../registry'
import { summary, insights, timeline, mapPanel, dataTable, actionGrid, quoteGenerator, brandCustomizer, pricingPlans } from '../helpers'

const heroActions = [
  { label: 'Book a clean', href: '/book', variant: 'primary', icon: 'bi-lightning-charge' },
  { label: 'Track a booking', href: '/track', variant: 'ghost', icon: 'bi-geo' },
]

const homeHero = {
  headline: 'Run your business, not after it.',
  subhead:
    'Say goodbye to dunning, chasing payments, and endless admin. Glint automates everything you hate doing — from quoting to re-scheduling — while giving your customers an experience they\'ll rave about.',
  body: [
    'With instant property imaging and a built-in quote generator, you can price jobs accurately in seconds and spot issues before your cleaners even arrive.',
    'Track your team in real time, let customers see their cleaner en route, and watch your revenue grow — automatically.',
  ],
  fullBleed: true,
  primaryAction: {
    label: 'Take back your time — automate with Glint.',
    href: '/register',
    icon: 'bi-lightning-charge',
  },
  secondaryAction: {
    label: 'See how Glint works',
    href: '/book',
    icon: 'bi-play-circle',
    variant: 'ghost',
  },
  microCopy: ['No setup required', 'No contracts', 'Just cleaner, faster growth.'],
  highlights: [
    {
      title: 'Instant property imaging',
      description:
        'With instant property imaging and a built-in quote generator, you can price jobs accurately in seconds and spot issues before your cleaners even arrive.',
    },
    {
      title: 'Live ops visibility',
      description: 'Track your team in real time, let customers see their cleaner en route, and watch your revenue grow — automatically.',
    },
  ],
  stat: {
    label: 'Time reclaimed weekly',
    value: '12+ hours',
    meta: 'Average across teams using Glint automations.',
  },
}

const publicPages = [
  {
    key: 'public.home',
    route: '/',
    layout: 'public',
    role: 'guest',
    badge: 'Glint platform',
    title: 'Run cleaning, routing, billing and support inside one operating system.',
    description: 'Marketing homepage summarising the value prop, navigation to book/track/manage, and a surface for the latest reliability stats.',
    hero: homeHero,
    actions: heroActions,
    sections: [
      summary(
        'public-home-metrics',
        'Platform proof points',
        [
          { label: 'Cities covered', value: '42', delta: '+7 added this quarter' },
          { label: 'Avg ETA accuracy', value: '92%', delta: '±6 min MAE' },
          { label: 'Customer CSAT', value: '4.9/5', delta: '2.1k public reviews' },
        ],
        null,
        { anchor: 'features' },
      ),
      quoteGenerator(
        'public-home-quote',
        'Quote generator: show prospects the math live',
        'Switch between property sizes, cadence and extras to instantly see how pricing, hours and savings update. Perfect for discovery calls or embedding on marketing pages.',
        { anchor: 'quote-demo', props: { hideStreetMeta: true } },
      ),
      brandCustomizer(
        'public-home-brand',
        'Skin Glint to your brand in 60 seconds',
        'Paste in your URL, pick accents, and download the embed + booking hub already matched to your fonts, colours and tone.',
        { anchor: 'customise', badge: 'Brand lab' },
      ),
      insights(
        'public-home-screens',
        'Screens & flows',
        [
          { title: 'Owner control room', description: 'Cross-tenant dashboard with live ETAs, payouts, incidents, and broadcast tooling.', meta: ['Owner OS'] },
          { title: 'Cleaner route app', description: 'Offline-ready navigation, checklists, and payouts built for the field.', meta: ['Cleaner PWA'] },
          { title: 'Customer magic link hub', description: 'Track, reschedule or pay without logging in — branded to each tenant.', meta: ['Customer'] },
        ],
        'Give prospects a peek at the actual product before the sales call.',
        { anchor: 'screens' },
      ),
      actionGrid(
        'public-home-cta',
        'Primary customer journeys',
        [
          { label: 'Book a recurring clean', description: '4/6/8 week cadence with custom add-ons.', icon: 'bi-calendar3', href: '/book' },
          { label: 'Find my booking', description: 'Self-service lookups by surname + postcode.', icon: 'bi-search', href: '/find' },
          { label: 'Check status page', description: 'Incident banners and maintenance windows.', icon: 'bi-activity', href: '/status' },
        ],
      ),
      pricingPlans(
        'public-home-pricing',
        'Plans built for every cleaning business',
        'Start for free, then add automation, routing, and live ops visibility once your team scales.',
        [
          {
            name: 'Starter',
            tagline: 'Launch fast',
            price: '$0',
            period: '/location',
            description: 'Capture bookings, send quotes, and keep customers updated from one hub.',
            features: [
              'Unlimited quotes + bookings',
              'Magic-link customer portal',
              'Live chat + email support',
            ],
            cta: { label: 'Start free', href: '/register?plan=starter' },
          },
          {
            name: 'Growth',
            tagline: 'Most popular',
            price: '$89',
            period: '/location / month',
            description: 'Automations, dispatch intelligence, and proactive revenue tooling.',
            features: [
              'Everything in Starter',
              'Automated reminders + dunning',
              'Route optimisation + live ops room',
              'Stripe, Slack + Zapier integrations',
            ],
            popular: true,
            cta: { label: 'Scale with Growth', href: '/register?plan=growth' },
          },
          {
            name: 'Scale',
            tagline: 'For multi-city teams',
            price: 'Custom',
            period: 'talk to us',
            description: 'Dedicated success engineer, custom workflows, and enterprise support.',
            features: [
              'Dedicated success engineer',
              'SSO + advanced analytics',
              'Priority routing + incident desk',
              'Custom integrations + SLAs',
            ],
            cta: { label: 'Book an enterprise demo', href: '/book?type=enterprise' },
          },
        ],
        { anchor: 'pricing' },
      ),
      insights(
        'public-home-faq',
        'Questions we get a lot',
        [
          { title: 'Can we cancel or reschedule anytime?', description: 'Yes. Magic links + customer portals let you pause, skip, or cancel with clear policy reminders.' },
          { title: 'Do you cover multi-city teams?', description: 'Glint is multi-tenant by default — each city or franchise can run its own branding, pricing, and staff.' },
          { title: 'How fast is onboarding?', description: 'Most owners are live in under a week using our Stripe + Google integrations and data import templates.' },
        ],
        'Still curious? Chat with the team — response times average under 4 minutes.',
        { anchor: 'faq' },
      ),
      actionGrid(
        'public-home-contact',
        'Talk to a human',
        [
          { label: 'Live chat in product', description: 'Support is online 7am–10pm UTC.', icon: 'bi-chat-dots', href: '/support/tickets' },
          { label: 'Book a 20 min demo', description: 'Pick a slot that suits your team.', icon: 'bi-calendar3', href: '/pricing#demo' },
          { label: 'Status + incidents', description: 'Always-on transparency for your ops teams.', icon: 'bi-activity', href: '/status' },
        ],
        null,
        { anchor: 'contact' },
      ),
    ],
  },
  {
    key: 'public.book',
    route: '/book',
    tenantFacing: true,
    layout: 'public',
    role: 'guest',
    badge: 'Booking flow',
    title: 'Enter address → choose services → confirm cadence + checkout.',
    description: 'Single-booking experience that handles one-off or recurring cleans, collects property details, and sets expectations before payment.',
    actions: [
      { label: 'Start booking', variant: 'primary', icon: 'bi-lightning-charge' },
      { label: 'Talk to support', variant: 'ghost', icon: 'bi-chat-dots' },
    ],
    sections: [
      quoteGenerator(
        'public-book-quote',
        'Instant pricing',
        'Send me this plan with the same “Live demo” quote workflow you see on the homepage — address lookup, window count slider, cadence pills, property size, add-ons, and the live estimate summary.',
        { anchor: 'instant-pricing', props: { hideHeroLinks: true, hideStreetMeta: true } },
      ),
      timeline(
        'public-book-steps',
        'Flow overview',
        [
          { title: 'Address & property profile', time: 'Step 1', detail: 'Postcode lookup, entry instructions, residents, pets, and parking context collected up front.', state: 'info' },
          { title: 'Services + quote builder', time: 'Step 2', detail: 'Select package, add-ons, deep clean boosts, and see live pricing with duration estimates.', state: 'info' },
          { title: 'Checkout & confirmation', time: 'Step 3', detail: 'Secure payment via Stripe Checkout with policy reminders and optional account creation.', state: 'success' },
        ],
      ),
      summary(
        'public-book-assurances',
        'Conversion boosters',
        [
          { label: 'Quote accuracy', value: '±5%', delta: 'based on property inputs' },
          { label: 'Slots refreshed', value: 'Every 30s', delta: 'real dispatch availability' },
          { label: 'Cancellation window', value: '24h', delta: 'policy reinforced pre-pay' },
        ],
      ),
      insights(
        'public-book-insights',
        'Key UX details',
        [
          { title: 'Access-friendly forms', description: 'Two-column design that works on mobile, supports copy/paste of buzz codes, and flags incomplete fields early.' },
          { title: 'Quote clarity', description: 'Glassmorphism summary card with breakdown of labour vs. addons vs. taxes so there are zero billing surprises.' },
          { title: 'Plan flexibility', description: 'Recurring slider toggles frequency and autop recalculates price/time while showing commitment-free messaging.' },
          { title: 'Street-level verification', description: 'Service address autocomplete now locks a pano and captures left/front/right Street View so cleaners see access routes instantly.' },
          { title: 'Tidy accordion inputs', description: 'Cadence, window count, property size, and add-ons collapse into lightweight accordions, keeping the funnel focused and scannable.' },
        ],
      ),
    ],
  },
  {
    key: 'public.checkout',
    route: '/checkout',
    tenantFacing: true,
    layout: 'public',
    role: 'guest',
    badge: 'Stripe hand-off',
    title: 'Secure payment capture with clear return state.',
    description: 'Guides the guest into Stripe Checkout or Payment Links, shows what to expect after paying, and handles webhook confirmations gracefully.',
    actions: [
      { label: 'Retry payment', variant: 'primary', icon: 'bi-arrow-clockwise' },
      { label: 'Contact support', variant: 'ghost', icon: 'bi-chat-dots' },
    ],
    sections: [
      summary(
        'public-checkout-sla',
        'Checkout SLAs',
        [
          { label: 'Stripe status', value: 'Operational', delta: 'updated just now' },
          { label: '3DS success', value: '98.1%', delta: 'last 7 days' },
          { label: 'Refund lead time', value: '3-5 days', delta: 'per network' },
        ],
      ),
      insights(
        'public-checkout-insights',
        'Supportive microcopy',
        [
          { title: 'Policy reminders', description: 'Inline summary of cancellation + reschedule rules before redirecting to Stripe to avoid disputes.' },
          { title: 'Post-pay guidance', description: 'Explains that confirmation email plus manage link will arrive instantly even if card screens take a moment.' },
          { title: 'Failure states', description: 'Displays BIN-specific decline tips (insufficient funds vs. 3DS failure) and offers magic-link manage option.' },
        ],
      ),
      actionGrid(
        'public-checkout-help',
        'Need help finishing checkout?',
        [
          { label: 'Switch to bank transfer', description: 'Trigger manual invoice with ACH/BACS instructions.', icon: 'bi-bank' },
          { label: 'Use saved card', description: 'Return to magic-link manage hub and reuse vaulted payment.', icon: 'bi-safe' },
          { label: 'Chat to live agent', description: 'Route to support with context about which step failed.', icon: 'bi-headset' },
        ],
      ),
    ],
  },
  {
    key: 'public.booking-confirmed',
    route: '/booking/confirmed/:sessionId',
    tenantFacing: true,
    layout: 'public',
    role: 'guest',
    badge: 'Post-checkout',
    title: 'Confirmation page with manage link and referral CTA.',
    description: 'Shows summary of booking, session ID, and encourages the user to save their manage link while downstream jobs spin up.',
    actions: [
      { label: 'Go to manage hub', variant: 'primary', icon: 'bi-box-arrow-up-right' },
      { label: 'Add to calendar', variant: 'ghost', icon: 'bi-calendar2' },
    ],
    sections: [
      summary(
        'public-confirmed-summary',
        'Your clean is set',
        [
          { label: 'Reference ID', value: '{{sessionId}}', delta: 'Share this with support if needed' },
          { label: 'Window', value: 'Wed · 10:00-12:00', delta: 'Exact ETA arrives day-of' },
          { label: 'Manage link sent to', value: 'Your booking email + SMS fallback', delta: 'Use magic link to reschedule/cancel' },
        ],
      ),
      insights(
        'public-confirmed-next',
        'What happens next',
        [
          { title: 'Cleaner assignment', description: 'Dispatch locks the optimal pro based on skills, travel time, and preferences.' },
          { title: 'Pre-visit checklist', description: 'Night-before reminders with prep tips, parking, and access pin storage.' },
          { title: 'Live tracking link', description: 'Push + email a live map once the cleaner is en-route.' },
        ],
      ),
      actionGrid(
        'public-confirmed-share',
        'Share or upgrade',
        [
          { label: 'Refer a friend', description: 'Generate referral link for credits.', icon: 'bi-gift' },
          { label: 'Upgrade to plan', description: 'Lock 4/6/8 week cadence and save 10%.', icon: 'bi-repeat' },
          { label: 'Manage from portal', description: 'Create account to unlock invoicing + chat.', icon: 'bi-door-open' },
        ],
      ),
    ],
  },
  {
    key: 'public.find',
    route: '/find',
    tenantFacing: true,
    layout: 'public',
    role: 'guest',
    badge: 'Lookups',
    title: 'Find my booking via surname + postcode or email.',
    description: 'Self-serve search that validates user data, rate-limits attempts, and points to manage hub or support.',
    actions: [
      { label: 'Search bookings', variant: 'primary', icon: 'bi-search' },
      { label: 'Use magic link', variant: 'ghost', icon: 'bi-magic' },
    ],
    sections: [
      summary(
        'public-find-stats',
        'Lookup performance',
        [
          { label: 'Matches resolved', value: '87%', delta: 'without agent help' },
          { label: 'Rate-limit', value: '5 attempts', delta: 'per hour per device' },
          { label: 'Fields required', value: 'Surname + postcode', delta: 'or email / phone' },
        ],
      ),
      insights(
        'public-find-ux',
        'Experience notes',
        [
          { title: 'Multi-tenant aware', description: 'If an email is tied to multiple companies, present tenant selector before showing bookings.' },
          { title: 'Security guardrails', description: 'Captcha + throttle + anonymised result states to avoid leaking customer data.' },
          { title: 'Escalation path', description: 'One-click escalate to support with hashed search context when the guest still can’t find the booking.' },
        ],
      ),
    ],
  },
  {
    key: 'public.manage',
    route: '/manage/:token',
    tenantFacing: true,
    layout: 'public',
    role: 'guest',
    badge: 'Magic-link hub',
    title: 'Reschedule, edit details, or cancel within policy.',
    description: 'Tokenised access for guests without an account. Every action double-confirms policy compliance and logs audit events.',
    actions: [
      { label: 'Reschedule', variant: 'primary', icon: 'bi-calendar-event' },
      { label: 'Update details', variant: 'secondary', icon: 'bi-pencil' },
    ],
    sections: [
      summary(
        'public-manage-overview',
        'Session snapshot',
        [
          { label: 'Token', value: '{{token}}', delta: 'expires after 30 mins idle' },
          { label: 'Policy window', value: 'Free changes >24h', delta: 'Automated fees otherwise' },
          { label: 'Available actions', value: 'Reschedule · Edit · Cancel', delta: 'Some require OTP' },
        ],
      ),
      insights(
        'public-manage-actions',
        'Most common actions',
        [
          { title: 'Reschedule/skip', description: 'Calendar with dispatch availability and policy guardrails.', meta: ['Drag & drop', 'Next 60 days'] },
          { title: 'Edit occupants', description: 'Update entry notes, alarm codes, parking, and pet info with inline diff preview.', meta: ['Audit logged'] },
          { title: 'Cancel plan', description: 'Policy-aware cancellation with fee preview and ability to convert to pause instead.' },
        ],
      ),
      dataTable(
        'public-manage-history',
        'Recent changes',
        [
          { label: 'Action', key: 'action' },
          { label: 'When', key: 'when' },
          { label: 'Outcome', key: 'status', align: 'right' },
        ],
        [
          { action: 'Skipped recurring clean', when: 'Today · 09:14', status: 'Confirmed' },
          { action: 'Updated door code', when: 'Yesterday', status: 'Shared with cleaner' },
          { action: 'Requested refund', when: 'Sun', status: 'Under review' },
        ],
      ),
    ],
  },
  {
    key: 'public.track',
    route: '/track/:trackingId',
    tenantFacing: true,
    layout: 'public',
    role: 'guest',
    badge: 'Live tracking',
    title: 'Watch the cleaner progress in real time.',
    description: 'Lightweight map for guests that shows en-route, arrival, and completion events with ETA updates.',
    actions: [
      { label: 'Share link', variant: 'secondary', icon: 'bi-share' },
      { label: 'Contact support', variant: 'ghost', icon: 'bi-chat-dots' },
    ],
    sections: [
      mapPanel(
        'public-track-map',
        'Live map',
        [
          { title: 'Cleaner', detail: 'Currently en-route', lat: 51.518, lng: -0.081, state: 'info' },
          { title: 'Home', detail: 'Arrival window 10:05-10:35', lat: 51.505, lng: -0.09, state: 'success' },
        ],
        'Embed using Google Maps JS API with tenant-specific key.',
        12,
      ),
      timeline(
        'public-track-timeline',
        'Tracking session',
        [
          { title: 'En-route', time: '09:42', detail: 'Cleaner left previous job, ETA 36 min', state: 'info' },
          { title: 'Arriving soon', time: '10:08', detail: '2 mins away · dispatch notified', state: 'warning' },
          { title: 'On site', time: '10:12', detail: 'Clocked in with location proof', state: 'success' },
        ],
      ),
    ],
  },
  {
    key: 'public.receipt',
    route: '/receipt/:id',
    tenantFacing: true,
    layout: 'public',
    role: 'guest',
    badge: 'Printable receipt',
    title: 'Download or print your receipt at any time.',
    description: 'Public-friendly receipt view with service breakdown, card brand, VAT summary, and company info.',
    actions: [
      { label: 'Download PDF', variant: 'primary', icon: 'bi-file-earmark-arrow-down' },
      { label: 'Send to email', variant: 'ghost', icon: 'bi-envelope' },
    ],
    sections: [
      summary(
        'public-receipt-breakdown',
        'Invoice snapshot',
        [
          { label: 'Receipt ID', value: '#{{id}}', delta: 'Matches Stripe payment_intent' },
          { label: 'Total', value: '£142.00', delta: 'Incl. VAT £23.67' },
          { label: 'Card', value: '•••• 4242', delta: 'Charged by Glint Labs' },
        ],
      ),
      dataTable(
        'public-receipt-lines',
        'Line items',
        [
          { label: 'Description', key: 'desc' },
          { label: 'Qty', key: 'qty' },
          { label: 'Amount', key: 'amount', align: 'right' },
        ],
        [
          { desc: 'Recurring clean (2h base)', qty: '1', amount: '£120.00' },
          { desc: 'Inside fridge add-on', qty: '1', amount: '£15.00' },
          { desc: 'Tip', qty: '1', amount: '£7.00' },
        ],
        'VAT summary + payment terms pinned below.',
      ),
      insights(
        'public-receipt-footers',
        'Policy footers',
        [
          { title: 'Cancellation terms', description: '24h free cancellation then 50% fee, automatically applied before payout.' },
          { title: 'Need help?', description: 'Link to support with auto-populated receipt ID.' },
          { title: 'Ledger export', description: 'Encourage accountant login for CSV/QuickBooks export.' },
        ],
      ),
    ],
  },
]

export function registerPublicPages() {
  publicPages.forEach(page => definePage(page))
}
