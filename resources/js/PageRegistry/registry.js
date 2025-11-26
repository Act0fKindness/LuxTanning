export const pageRegistry = {}

const hostBases = {
  tenant: 'https://{tenant}.glintlabs.com',
  marketing: 'https://www.glintlabs.com',
  glint: 'https://admin.glintlabs.com',
}

const tenantRoles = new Set(['customer', 'cleaner', 'manager', 'owner', 'accountant', 'support', 'shared'])

const ensureLeadingSlash = route => (route?.startsWith('/') ? route : `/${route || ''}`)
const tokeniseParams = route => ensureLeadingSlash(route).replace(/:([A-Za-z0-9_]+)/g, '{$1}')

const inferHost = spec => {
  if (spec.host) return spec.host
  if (spec.tenantFacing) return 'tenant'
  if (tenantRoles.has(spec.role)) return 'tenant'
  if (spec.role === 'glint') return 'glint'
  return 'marketing'
}

const defaultBrandingForHost = (host, spec) => {
  if (host === 'tenant') {
    return {
      type: 'tenant',
      baseUrl: hostBases.tenant,
      slugPlaceholder: '{tenant}',
      marketingLink: true,
      showBackLink: spec.role === 'customer' || spec.tenantFacing === true,
      poweredBy: true,
    }
  }

  if (host === 'glint') {
    return {
      type: 'platform',
      baseUrl: hostBases.glint,
      poweredBy: false,
    }
  }

  return {
    type: 'marketing',
    baseUrl: hostBases.marketing,
    poweredBy: false,
  }
}

const buildUrl = (host, route) => {
  const base = hostBases[host] || ''
  return `${base}${tokeniseParams(route)}`
}

export function definePage(spec) {
  const host = inferHost(spec)
  const enriched = {
    ...spec,
    host,
  }
  const branding = spec.branding || defaultBrandingForHost(host, spec)
  enriched.branding = branding
  enriched.url = spec.url || buildUrl(host, spec.route)

  pageRegistry[enriched.key] = enriched
  return enriched
}
