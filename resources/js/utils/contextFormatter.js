export function formatText(template, context = {}) {
  if (!template || typeof template !== 'string') {
    return template
  }

  return template.replace(/\{\{\s*([\w.]+)\s*\}\}/g, (_, key) => {
    const value = key.split('.').reduce((acc, segment) => {
      if (acc && typeof acc === 'object' && segment in acc) {
        return acc[segment]
      }
      return undefined
    }, context)

    return value === undefined || value === null ? '' : value
  })
}

export function getContextValue(context = {}, path = '', fallback = undefined) {
  if (!path || typeof path !== 'string') {
    return fallback
  }

  const value = path.split('.').reduce((acc, segment) => {
    if (acc && typeof acc === 'object' && segment in acc) {
      return acc[segment]
    }
    return undefined
  }, context)

  return value === undefined ? fallback : value
}
