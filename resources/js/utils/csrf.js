export function resolveCsrfToken(pageProps = {}) {
  const candidate = unwrap(pageProps)
  if (candidate?.csrfToken) {
    return candidate.csrfToken
  }

  if (typeof document !== 'undefined') {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta?.content) {
      return meta.content;
    }

    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
    if (match) {
      try {
        return decodeURIComponent(match[1]);
      } catch (_) {
        return match[1];
      }
    }
  }

  return '';
}

function unwrap(value) {
  if (value && typeof value === 'object' && 'value' in value) {
    return unwrap(value.value)
  }

  return value
}
