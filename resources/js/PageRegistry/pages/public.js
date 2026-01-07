import { definePage } from '../registry'
import { summary, insights, timeline, actionGrid, tileGrid, pricingPlans, detailCard, checklist } from '../helpers'

const heroActions = [
  { label: 'Book a sun bed', href: '/book', variant: 'primary', icon: 'bi-lightning-charge' },
  { label: 'Browse courses', href: '/courses', variant: 'ghost', icon: 'bi-collection-play' },
]

const homeHero = {
  eyebrow: 'Lux Tanning Studios',
  headline: 'The operating system for sun bed studios and glow-obsessed guests.',
  subhead:
    'Minutes wallets, lamp telemetry, concierge messaging, and branded e-commerce come together in a single Lux experience.',
  body: [
    'Guests discover a course online, walk into a studio that already knows their hydration plan, and can see their remaining minutes from any device. Managers receive lamp health alerts and campaign levers the second occupancy shifts.',
  ],
  primaryAction: { label: 'Book a sun bed', href: '/book', icon: 'bi-lightning-charge' },
  secondaryAction: { label: 'See membership perks', href: '/membership', icon: 'bi-stars', variant: 'ghost' },
  microCopy: ['Live availability in 14 studios', 'Wallets update instantly', 'No more clipboards or guesswork'],
  highlights: [
    { title: 'Minutes wallet', description: 'Track exposures per guest with health guardrails already baked in.' },
    { title: 'Course designer', description: 'Spin up new bundles with pricing, exposure curves, and retail tie-ins in minutes.' },
  ],
  stat: { label: 'Glow club members', value: '4,217', meta: 'Average session rating 4.94 / 5' },
  fullBleed: true,
}

const courseTiles = [
  {
    label: 'Instant booking',
    status: 'Live',
    description: 'Guests select a studio, exposure goal, and add-ons from their phone or kiosk.',
    meta: 'Leads feed straight into staff shift views.',
    action: { label: 'Preview flow', href: '/book', icon: 'bi-play' },
  },
  {
    label: 'Minutes wallet',
    status: 'Synced',
    description: 'Wallets update from kiosk, manager override, or retail POS without double entry.',
    subtext: 'Auto-pauses when limits trigger.',
    state: 'success',
  },
  {
    label: 'Lamp telemetry',
    status: 'Monitored',
    description: 'Each bed surfaces lamp hours, temp, and service countdown inside the staff view.',
    meta: 'Triggers maintenance tickets when nearing limits.',
    state: 'warning',
  },
  {
    label: 'Retail attach',
    status: 'Boosted',
    description: 'Upsells display based on course goal and inventory in that location.',
    subtext: 'Automatic attribution for commission.',
    state: 'info',
  },
]

const guestJourney = [
  { title: 'Discover', time: 'T-0d', detail: 'Guest takes Lux tone quiz, sees recommended course, and loads a wallet.', state: 'info' },
  { title: 'Arrive', time: 'T+0m', detail: 'Studio tablet already has hydration reminders, contraindications, and playlist cues.', state: 'success' },
  { title: 'Glow', time: 'T+12m', detail: 'Lamp timers sync to the wallet, while staff see cooldown + retail prompts.', state: 'success' },
  { title: 'Aftercare', time: 'T+30m', detail: 'Auto-receipt includes exposure log, care ritual, and quick-top-up link.', state: 'info' },
]

const membershipPlans = [
  {
    name: 'Dawn',
    tagline: 'Starter course',
    price: '£89',
    period: 'per 4-session bundle',
    description: 'Perfect for event prep or a seasonal refresh. Includes hydration rituals and priority support.',
    features: ['60 tracked minutes', 'Concierge reminders', 'Complimentary serum'],
    cta: { label: 'Start Dawn', href: '/book?plan=dawn' },
  },
  {
    name: 'Glow Pro 20',
    tagline: 'Most loved',
    price: '£169',
    period: 'per 8-session bundle',
    description: 'Minute wallet with rollover, expedited waitlists, and handheld retail recommendations for every visit.',
    features: ['160 tracked minutes', 'Rollover + pause controls', 'Staff chat + quick support'],
    popular: true,
    cta: { label: 'Reserve Glow Pro', href: '/book?plan=glow-pro' },
  },
  {
    name: 'Solar Club',
    tagline: 'Invite only',
    price: '£289',
    period: '/month',
    description: 'Unlimited sessions with health guardrails, biometric entry, and quarterly lamp labs.',
    features: ['Biometric check-ins', 'Dedicated Glow Guide', 'Retail drops + guest passes'],
    cta: { label: 'Join the waitlist', href: '/membership?plan=solar-club' },
  },
]

const publicPages = [
  {
    key: 'public.home',
    route: '/',
    layout: 'public',
    role: 'guest',
    badge: 'Lux OS',
    title: 'The only sun bed platform that links booking, compliance, minutes, and merchandising.',
    description: 'Every guest journey — from tone quiz to aftercare text — sits on one Lux-branded stack.',
    hero: homeHero,
    actions: heroActions,
    sections: [
      summary(
        'public-home-stats',
        'Proof points from live studios',
        [
          { label: 'Studios live', value: '14', delta: '3 opening this quarter' },
          { label: 'Average CSAT', value: '4.94/5', delta: '6,102 verified reviews' },
          { label: 'Waitlist conversion', value: '62%', delta: 'with Lux automation' },
        ],
        'Operators tap Lux for visibility, compliance, and a premium guest brand.',
        { anchor: 'stats' },
      ),
      insights(
        'public-home-pillars',
        'Built for modern tanning brands',
        [
          { title: 'Predictable compliance', description: 'Minutes wallets, UV exposure logs, and health checks run in the background.' },
          { title: 'Retail-first', description: 'Attach aftercare kits, boosters, and hydration rituals per booking without spreadsheets.' },
          { title: 'Hospitality-grade CX', description: 'Concierge chat, playlists, and aromatics queue up automatically for each guest.' },
        ],
        'Guests feel cared for, while managers see lamp health, staffing, and revenue in one control room.',
        { anchor: 'pillars' },
      ),
      timeline(
        'public-home-journey',
        'Guest journey on Lux',
        guestJourney,
        'No bouncing between apps — Lux orchestrates every state transition to keep staff and members in sync.',
        { anchor: 'journey' },
      ),
      tileGrid(
        'public-home-tech',
        'Everything actually connected',
        'Minutes wallet, lamp telemetry, and store checkout live in a single stack.',
        courseTiles,
        { anchor: 'features' },
      ),
      pricingPlans(
        'public-home-plans',
        'Courses & memberships',
        'Guests can start with a bundle, move into Glow Pro, or unlock Solar Club once they qualify.',
        membershipPlans,
        { anchor: 'plans' },
      ),
      actionGrid(
        'public-home-cta',
        'Pick your next step',
        [
          { label: 'Book a sun bed', description: 'Load minutes and reserve a room in 60 seconds.', icon: 'bi-lightning-charge', href: '/book' },
          { label: 'Tour a studio', description: 'See how Lux runs in Mayfair, Shoreditch, and Manchester.', icon: 'bi-geo-alt', href: '/locations' },
          { label: 'Launch Lux at your shop', description: 'Operators can onboard within a week.', icon: 'bi-rocket', href: '/register' },
        ],
        'Need a human? Tap the concierge bubble in the corner.',
        { anchor: 'cta' },
      ),
    ],
  },
  {
    key: 'public.book',
    route: '/book',
    layout: 'public',
    role: 'guest',
    badge: 'Booking flow',
    title: 'Start a Lux sun bed course in under a minute.',
    description: 'Address lookup, exposure quiz, studio selection, add-ons, consent, and payment — all inside one guided surface.',
    actions: [
      { label: 'Launch booking', href: '/book', variant: 'primary', icon: 'bi-lightning-charge' },
      { label: 'Talk to a Glow Guide', href: '/#contact', variant: 'ghost', icon: 'bi-chat-dots' },
    ],
    sections: [
      checklist(
        'public-book-steps',
        'Booking stages',
        [
          { label: 'Exposure quiz', detail: 'Guests answer health + tone questions to set their safe range.' },
          { label: 'Studio + room', detail: 'Choose a location, bed type, and optional aroma/playlist.' },
          { label: 'Add-ons', detail: 'Attach serums, hydration boosts, or retail kits.' },
          { label: 'Consent & payment', detail: 'Digital signature plus Apple Pay, Klarna, or stored card.' },
        ],
        'Every state stores to the guest profile so staff see context automatically.',
      ),
      detailCard(
        'public-book-wallet',
        'Wallet automation',
        'Minutes wallets update when the bed starts, not when someone remembers to note it. Pause, rollover, and referrals all run from the same ledger.',
        { highlights: ['Works for pay-as-you-glow or memberships', 'Instant refunds if a session aborts', 'Managers can override with audit trail'] },
      ),
    ],
  },
  {
    key: 'public.courses',
    route: '/courses',
    layout: 'public',
    role: 'guest',
    badge: 'Courses',
    title: 'Crafted courses for every glow goal.',
    description: 'From curated bundles to unlimited memberships, Lux matches exposure science with hospitality.',
    sections: [
      pricingPlans('public-courses-plans', 'Ready-to-run bundles', 'Each course includes hydration rituals, playlists, and digital aftercare.', membershipPlans),
      insights(
        'public-courses-faq',
        'Frequently asked course questions',
        [
          { title: 'Can I pause mid-course?', description: 'Yes. Lux wallets pause instantly and pick back up whenever you return.' },
          { title: 'What about skin type rules?', description: 'The quiz locks in safe exposure windows and automatically enforces cool-down buffers.' },
          { title: 'Do minutes expire?', description: 'Glow Pro wallets roll over for 30 days; Solar Club never expires while membership is active.' },
        ],
      ),
    ],
  },
  {
    key: 'public.membership',
    route: '/membership',
    layout: 'public',
    role: 'guest',
    badge: 'Membership',
    title: 'Solar Club + Glow Pro perks',
    description: 'Concierge-level communications, guest passes, lamp labs, and first dibs on every product drop.',
    sections: [
      summary(
        'public-membership-perks',
        'Membership highlights',
        [
          { label: 'Guest passes / quarter', value: '4', delta: 'for Solar Club' },
          { label: 'Retail credit', value: '£25', delta: 'every renewal' },
          { label: 'Response time', value: '< 4 min', delta: 'Glow Guide chat' },
        ],
      ),
      actionGrid(
        'public-membership-actions',
        'Everything you unlock',
        [
          { label: 'Biometric entry', description: 'Walk straight in, wallet updates instantly.', icon: 'bi-fingerprint' },
          { label: 'Concierge chat', description: 'DM your Glow Guide anytime.', icon: 'bi-chat-heart' },
          { label: 'Retail drops', description: 'First access to boosters and labs.', icon: 'bi-bag-heart' },
        ],
      ),
    ],
  },
  {
    key: 'public.locations',
    route: '/locations',
    layout: 'public',
    role: 'guest',
    badge: 'Studios',
    title: 'Studios designed like boutique hotels.',
    description: 'Warm lighting, guided aromatherapy, and lamp telemetry in every room.',
    sections: [
      tileGrid(
        'public-locations-studios',
        'Live studio snapshot',
        'Lux OS keeps every studio, waitlist, and retail shelf in sync.',
        [
          { label: 'Mayfair Flagship', hint: '5 beds · Glow Lab', status: 'Waitlist 3', state: 'warning', description: 'Concierge experiences, vitamin boosters, private lounge.' },
          { label: 'Shoreditch Loft', hint: '4 beds · Sauna', status: 'Walk-ins open', state: 'success', description: 'Creative lab with playlists + creator pop-ups.' },
          { label: 'Manchester North', hint: '6 beds', status: 'Members only', state: 'danger', description: 'Retail lab + masterclasses weekly.' },
        ],
      ),
      insights(
        'public-locations-ops',
        'How operations stay smooth',
        [
          { title: 'Lamp analytics', description: 'Autoflag rooms approaching cycle limits so staff swap lamps before a guest notices.' },
          { title: 'Shift rituals', description: 'Open/close checklists live inside the staff PWA with timers and proof captures.' },
          { title: 'Wayfinding', description: 'Guests receive live ETA texts + hydration tips while commuting.' },
        ],
      ),
    ],
  },
  {
    key: 'public.shop',
    route: '/shop',
    layout: 'public',
    role: 'guest',
    badge: 'Retail',
    title: 'Aftercare and boosters shipped straight to you.',
    description: 'The Lux shop mirrors in-studio merchandising so guests maintain their glow between sessions.',
    sections: [
      summary(
        'public-shop-stats',
        'Retail moments',
        [
          { label: 'SKUs in stock', value: '112', delta: 'Auto synced to studios' },
          { label: 'Avg basket uplift', value: '+32%', delta: 'when tied to a course' },
          { label: 'Delivery speed', value: '1-2 days', delta: 'nationwide' },
        ],
      ),
      insights(
        'public-shop-kits',
        'What ships from the Glow Shop',
        [
          { title: 'Hydration Lab', description: 'Electrolyte + serum bundles that match your exposure curve.' },
          { title: 'Boost capsules', description: 'Single-use ampoules triggered by lamp telemetry data.' },
          { title: 'Home tech', description: 'At-home lamps + sensors still log back to your account.' },
        ],
      ),
    ],
  },
]

export function registerPublicPages() {
  publicPages.forEach(page => definePage(page))
}
