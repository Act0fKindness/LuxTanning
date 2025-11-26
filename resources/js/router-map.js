import { pageRegistry } from './PageRegistry'

const roleMatrix = {
  guest: undefined,
  public: undefined,
  customer: ['customer'],
  cleaner: ['cleaner'],
  manager: ['manager'],
  owner: ['owner'],
  accountant: ['accountant'],
  support: ['support'],
  glint: ['platform_admin'],
  shared: ['customer', 'cleaner', 'manager', 'owner', 'accountant', 'support'],
}

const roleComponentMap = {
  customer: 'Customer/PageShell',
  cleaner: 'Cleaner/PageShell',
  manager: 'Manager/PageShell',
  owner: 'Owner/PageShell',
  accountant: 'Accountant/PageShell',
  support: 'Support/PageShell',
  glint: 'Glint/PageShell',
  shared: 'Shared/PageShell',
}

const inferComponent = (role, layout) => {
  if (layout === 'auth') return 'Auth/PageShell'
  if (layout === 'public') return 'Public/PageShell'
  if (layout === 'pwa' || layout === 'workspace') {
    return roleComponentMap[role] || 'Shared/PageShell'
  }
  return roleComponentMap[role] || 'Public/PageShell'
}

export const routes = Object.values(pageRegistry).map(spec => ({
  path: spec.route,
  component: inferComponent(spec.role, spec.layout),
  pageKey: spec.key,
  public: spec.role === 'guest' || spec.role === 'public',
  roles: roleMatrix[spec.role],
  host: spec.host,
  url: spec.url,
  branding: spec.branding,
}))

export default routes
