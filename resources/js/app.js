import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { initPwaPrompts } from './pwaPrompts';
import { initPullToRefresh } from './pwaPullToRefresh';
import { initStandaloneRedirect, initHomeEntryRedirect } from './utils/standaloneRedirect';
import LumaAssistant from './Components/LumaAssistant.vue';

const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfTokenMeta?.getAttribute('content') || '';
const reportJsError = payload => {
  const body = JSON.stringify(payload);
  if (navigator.sendBeacon) {
    navigator.sendBeacon('/js-debug', new Blob([body], { type: 'application/json' }));
    return;
  }
  fetch('/js-debug', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
    body,
    keepalive: true,
  }).catch(() => {});
};

window.addEventListener('error', event => {
  reportJsError({
    type: 'error',
    message: event.message,
    stack: event.error?.stack,
    href: window.location.href,
    component: window.__INITIAL_PAGE__?.component,
  });
});

window.addEventListener('unhandledrejection', event => {
  const reason = event.reason || {};
  reportJsError({
    type: 'unhandledrejection',
    message: reason?.message || String(reason),
    stack: reason?.stack,
    href: window.location.href,
    component: window.__INITIAL_PAGE__?.component,
  });
});

InertiaProgress.init({ color: '#4B5563', showSpinner: false });

console.log('[Lux] Inertia boot');

// Eagerly bundle all pages to avoid chunk loading issues in production/CDN
const pages = require.context('./Pages', true, /\.vue$/);

let assistantVm = null; // Ensures the chatbot widget mounts once across pages#A0K

createInertiaApp({
  resolve: name => {
    console.log('[Lux] Resolve page', name);
    const page = pages(`./${name}.vue`);
    return page.default || page;
  },
  setup({ el, App, props, plugin }) {
    const vue = createApp({ render: () => h(App, props) });
    vue.use(plugin);
    const routeHelper = (...args) => {
      if (typeof window.route === 'function') {
        return window.route(...args);
      }
      console.warn('[Lux] route helper missing');
      return '/';
    };
    vue.config.globalProperties.route = routeHelper;
    vue.config.globalProperties.$route = routeHelper;
    vue.mixin({ methods: { route: routeHelper } });
    vue.mount(el);

    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
      document.body.classList.add('pwa-standalone');
      el.classList.add('pwa-standalone');
    }

    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch(err => {
          console.warn('[Lux] Service worker registration failed', err);
        });
      });
    }

    initPwaPrompts();
    initPullToRefresh();
    initStandaloneRedirect(props.initialPage);
    initHomeEntryRedirect(props.initialPage);

    if (!assistantVm) {
      const assistantTarget = document.createElement('div');
      assistantTarget.id = 'luma-assistant-root';
      document.body.appendChild(assistantTarget);
      assistantVm = createApp(LumaAssistant);
      assistantVm.mount(assistantTarget);
    }
  },
});
