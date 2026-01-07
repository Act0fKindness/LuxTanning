import { definePage } from '../registry'
import { summary, insights, checklist, timeline } from '../helpers'

const authPages = [
  {
    key: 'auth.magic-link',
    route: '/auth/magic-link',
    layout: 'auth',
    role: 'public',
    tenantFacing: true,
    badge: 'Passwordless entry',
    title: 'Send yourself a Lux login link via SMS or email.',
    description: 'Customers, Glow Guides, and owners authenticate with branded magic links that already know their studio + role.',
    sections: [
      summary('auth-magic-stats', 'Delivery health', [
        { label: 'Links this week', value: '12,482', delta: '0.3% failures' },
        { label: 'SMS vs email', value: '64 / 36%', delta: 'auto-picks preferred channel' },
        { label: 'Abuse throttles', value: '48 blocks', delta: 'device posture checks' },
      ]),
      checklist('auth-magic-flow', 'Flow checklist', [
        { label: 'Identify guest', detail: 'Email, phone, or membership ID — Lux maps to the right tenant automatically.' },
        { label: 'Choose channel', detail: 'Prefers the last successful channel; falls back to OTP if inbox looks risky.' },
        { label: 'Brand the invite', detail: 'Logo, color, and studio nickname render in the link so guests trust it immediately.' },
      ]),
      insights('auth-magic-ux', 'Experience polish', [
        { title: 'Minutes preview', description: 'Customers see their remaining minutes before even tapping the link.' },
        { title: 'Studio switcher', description: 'Staff who work across studios pick the right location before logging in.' },
        { title: 'Invite capture', description: 'Owners can send invites to new staff directly from this form.' },
      ]),
    ],
  },
  {
    key: 'auth.verify',
    route: '/auth/verify',
    layout: 'auth',
    role: 'public',
    tenantFacing: true,
    badge: 'Token verification',
    title: 'Confirm the device, pick a studio, and step into the right workspace.',
    description: 'Every verification re-checks exposure permissions, membership tiers, and staff policies before dropping you into Lux OS.',
    sections: [
      timeline('auth-verify-seq', 'Verification sequence', [
        { title: 'Token validation', time: 't = 0s', detail: 'Signature + expiry check plus replay guard.', state: 'info' },
        { title: 'Studio selection', time: 't = 1s', detail: 'Show linked studios with occupancy + incidents so staff know where they are landing.', state: 'warning' },
        { title: 'Session creation', time: 't = 2s', detail: 'Issue Sanctum + PWA tokens, enforce MFA for finance + owner roles.', state: 'success' },
      ]),
      summary('auth-verify-signal', 'Signals captured', [
        { label: 'Device match', value: '96%', delta: 'passkeys + UA fingerprint' },
        { label: 'MFA coverage', value: 'Owners + finance', delta: 'cleaners optional' },
        { label: 'Link expiry', value: '15 minutes', delta: 'auto purge unused links' },
      ]),
      insights('auth-verify-cx', 'Guest-friendly touches', [
        { title: 'Switch device', description: 'QR handoff lets guests log in on kiosk while requesting from their phone.' },
        { title: 'Incident banner', description: 'If a studio has lamp maintenance, that context shows before login completes.' },
        { title: 'Session timer', description: 'Staff see how long until auto-logout based on their role policy.' },
      ]),
    ],
  },
]

export function registerAuthPages() {
  authPages.forEach(page => definePage(page))
}
