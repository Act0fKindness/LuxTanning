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
    title: 'Send yourself a secure magic link via email or SMS.',
    description: 'Handles guest, customer, cleaner, and owner login flows. Enforces rate limits and device posture checks before issuing tokens.',
    sections: [
      summary('auth-magic-link-metrics', 'Delivery health', [
        { label: 'Links sent (24h)', value: '4,102', delta: '0.2% failure rate' },
        { label: 'OTP fallback', value: '531', delta: 'When magic links expire' },
        { label: 'Abuse blocks', value: '32', delta: 'Auto throttled' },
      ]),
      checklist('auth-magic-link-steps', 'Flow checklist', [
        { label: 'Collect identifier', detail: 'Email or phone; auto-detects tenants if multiple memberships exist.' },
        { label: 'Decide channel', detail: 'Prefers last successful channel; fallback to OTP if repeated failures.' },
        { label: 'Send + confirm', detail: 'Links carry tenant + role context, expire in 15 minutes, and force device binding.' },
      ]),
      insights('auth-magic-link-ux', 'CX details', [
        { title: 'Tenant picker', description: 'If the identifier belongs to multiple companies, present a branded picker before sending the link.' },
        { title: 'Session memory', description: 'Remember trusted devices and show the most recent login so staff can jump back in.' },
        { title: 'Accessibility', description: 'Keyboard-friendly layout with copy-to-clipboard button for OTP fallback codes.' },
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
    title: 'Verify the token, pick a tenant, and route to the right workspace.',
    description: 'After the user clicks a magic link or enters an OTP, this page confirms device fingerprint, handles tenant selection, and finalises login.',
    sections: [
      timeline('auth-verify-timeline', 'Verification sequence', [
        { title: 'Token validation', time: 't=0s', detail: 'Check signature, expiry, and replay status.', state: 'info' },
        { title: 'Tenant selection', time: 't=1s', detail: 'Show available companies, highlight most recent, and warn if access revoked.', state: 'warning' },
        { title: 'Session creation', time: 't=2s', detail: 'Issue Sanctum session, set role, enforce MFA if required.', state: 'success' },
      ]),
      summary('auth-verify-security', 'Security signals', [
        { label: 'Device match', value: '96%', delta: 'pinned via UA + IP + key' },
        { label: 'MFA required', value: 'Owner + Glint', delta: 'optional for staff' },
        { label: 'Auto-expire', value: '15 mins', delta: 'unused links purge' },
      ]),
      insights('auth-verify-ux', 'Experience polish', [
        { title: 'Inline incident banners', description: 'If status page shows auth incident, surface banner before verifying to set expectations.' },
        { title: 'Device handoff', description: 'Offer QR code for continuing login on another device for cleaners on shared phones.' },
        { title: 'Tenant avatars', description: 'Owners see branded logos and subdomains so they know exactly which workspace they are entering.' },
      ]),
    ],
  },
]

export function registerAuthPages() {
  authPages.forEach(page => definePage(page))
}
