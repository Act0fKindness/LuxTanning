const STORAGE_KEY = 'glint:last-app-path';

const AUTH_ROUTES = new Set([
  '/login',
  '/register',
  '/password/reset',
  '/password/email',
  '/password/confirm'
]);

function normalizePage(page) {
  if (!page) return null;
  if (typeof page === 'string') {
    try { return JSON.parse(page); } catch (_) { return null; }
  }
  if (page.initialPage) {
    return normalizePage(page.initialPage);
  }
  return page;
}

function isStandalone() {
  return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function isMobileViewport() {
  if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') return false;
  try {
    return window.matchMedia('(max-width: 768px)').matches;
  } catch (_) {
    return false;
  }
}

const MARKETING_ROUTES = new Set([
  '/',
  '/pricing',
  '/services',
  '/coverage',
  '/reviews',
  '/faq',
  '/contact',
  '/book',
  '/book/options',
  '/book/slot',
  '/book/account',
  '/book/pay',
  '/book/confirm',
  '/privacy-ploicy',
  '/terms',
  '/register'
]);

const PROTECTED_PREFIXES = [
  '/dashboard',
  '/owner',
  '/tenant',
  '/hub',
  '/account',
  '/portal',
  '/app'
];

function requiresAuth(path) {
  if (!path) return false;
  return PROTECTED_PREFIXES.some(prefix => path.startsWith(prefix));
}

function resolveDefaultPath(user) {
  if (!user) return '/login';

  switch (user.role) {
    case 'platform_admin':
      return '/glint/platform';
    case 'owner':
      return '/owner/overview';
    case 'manager':
      return '/manager/dispatch/board';
    case 'cleaner':
      return '/cleaner/today';
    case 'accountant':
      return '/accountant/invoices';
    case 'support':
      return '/support/tickets';
    case 'customer':
      return '/customer/dashboard';
    default:
      return '/dashboard';
  }
}

function safeSet(value) {
  try {
    localStorage.setItem(STORAGE_KEY, value);
  } catch (_) {}
}

function safeGet() {
  try {
    return localStorage.getItem(STORAGE_KEY);
  } catch (_) {
    return null;
  }
}

function trackRouteChanges() {
  if (trackRouteChanges._bound) return;
  trackRouteChanges._bound = true;

  window.addEventListener('inertia:finish', event => {
    const page = event?.detail?.page;
    if (!page) return;
    const path = new URL(page.url, window.location.origin).pathname;
    if (!MARKETING_ROUTES.has(path) && !AUTH_ROUTES.has(path)) {
      safeSet(path);
    }
  });
}

export function initStandaloneRedirect(initialInput) {
  if (typeof window === 'undefined' || typeof document === 'undefined') return;
  if (!isStandalone()) return;

  trackRouteChanges();

  const page = normalizePage(initialInput) || normalizePage(window.__INITIAL_PAGE__);
  const initialUrl = page?.url || window.location.href;
  const initialPath = new URL(initialUrl, window.location.origin).pathname;
  const user = page?.props?.auth?.user ?? null;
  const stored = safeGet();
  const defaultPath = resolveDefaultPath(user);
  let target = stored;

  if (!target || MARKETING_ROUTES.has(target) || AUTH_ROUTES.has(target)) {
    target = defaultPath;
  } else if (defaultPath !== '/dashboard' && target === '/dashboard') {
    target = defaultPath;
  }

  if (!user && requiresAuth(target)) {
    target = '/login';
  }

  if (MARKETING_ROUTES.has(initialPath) && target && target !== initialPath) {
    window.location.replace(target);
    return;
  }

  if (!MARKETING_ROUTES.has(initialPath) && !AUTH_ROUTES.has(initialPath)) {
    safeSet(initialPath);
  }
}

export function initHomeEntryRedirect(initialInput) {
  if (typeof window === 'undefined' || typeof document === 'undefined') return;
  if (isStandalone()) return;

  const page = normalizePage(initialInput) || normalizePage(window.__INITIAL_PAGE__);
  const initialUrl = page?.url || window.location.href;
  const path = new URL(initialUrl, window.location.origin).pathname;

  if (path !== '/') return;
  if (isMobileViewport()) return;

  const user = page?.props?.auth?.user ?? null;
  if (!user) return;

  const target = resolveDefaultPath(user);
  if (target && target !== path) {
    window.location.replace(target);
  }
}
