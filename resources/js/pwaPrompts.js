function getCookie(name) {
  const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
  return match ? decodeURIComponent(match[1]) : null;
}

function setCookie(name, value, days = 365) {
  const expires = new Date(Date.now() + days * 864e5).toUTCString();
  document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
}

function isStandalone() {
  return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function isMobileDevice() {
  return /iphone|ipad|ipod|android/i.test(window.navigator.userAgent);
}

function createBanner({ id, message, buttonLabel, onConfirm, onDismiss }) {
  const existing = document.getElementById(id);
  if (existing) return existing;

  const banner = document.createElement('div');
  banner.id = id;
  banner.classList.add('lux-pwa-banner');
  banner.style.background = '#0C0714';
  banner.style.color = '#ffffff';
  banner.style.padding = '10px 16px';
  banner.style.display = 'flex';
  banner.style.alignItems = 'center';
  banner.style.justifyContent = 'space-between';
  banner.style.gap = '12px';
  banner.style.fontSize = '0.9rem';
  banner.style.zIndex = '1045';
  banner.style.position = 'fixed';
  banner.style.left = '0';
  banner.style.right = '0';
  banner.style.width = '100%';
  banner.style.maxWidth = '100%';
  banner.style.boxShadow = '0 6px 20px rgba(0,0,0,0.18)';
  banner.style.transform = 'translateZ(0)';

  const text = document.createElement('span');
  text.textContent = message;
  text.style.flex = '1';
  banner.appendChild(text);

  const actions = document.createElement('div');
  actions.style.display = 'flex';
  actions.style.gap = '8px';

  const confirmBtn = document.createElement('button');
  confirmBtn.type = 'button';
  confirmBtn.textContent = buttonLabel;
  confirmBtn.style.background = 'linear-gradient(135deg,#ffbe3d,#ff4e68)';
  confirmBtn.style.color = '#100612';
  confirmBtn.style.border = 'none';
  confirmBtn.style.borderRadius = '999px';
  confirmBtn.style.fontWeight = '700';
  confirmBtn.style.padding = '6px 16px';
  confirmBtn.style.cursor = 'pointer';
  confirmBtn.addEventListener('click', () => onConfirm?.(banner));
  actions.appendChild(confirmBtn);

  const dismissBtn = document.createElement('button');
  dismissBtn.type = 'button';
  dismissBtn.textContent = 'Later';
  dismissBtn.style.background = 'transparent';
  dismissBtn.style.color = '#ffffff';
  dismissBtn.style.border = '1px solid rgba(255,255,255,0.35)';
  dismissBtn.style.borderRadius = '999px';
  dismissBtn.style.padding = '6px 14px';
  dismissBtn.style.cursor = 'pointer';
  dismissBtn.addEventListener('click', () => onDismiss?.(banner));
  actions.appendChild(dismissBtn);

  banner.appendChild(actions);
  return banner;
}

function positionBanner(banner) {
  const nav = document.querySelector('.navbar');
  const top = nav ? Math.max(nav.getBoundingClientRect().height, nav.offsetHeight || 0) : 0;
  banner.style.top = `${top}px`;
  updateBodyBannerOffset();
}

function insertBannerUnderNavbar(banner) {
  if (!banner) return;
  if (banner.parentElement !== document.body) {
    document.body.appendChild(banner);
  }
  positionBanner(banner);
  setTimeout(() => positionBanner(banner), 0);
  const handler = () => positionBanner(banner);
  window.addEventListener('resize', handler);
  banner.__resizeHandler = handler;
}

function updateBodyBannerOffset() {
  if (typeof document === 'undefined' || !document.body) return;
  const banners = document.querySelectorAll('.lux-pwa-banner');
  if (!banners.length) {
    document.body.style.removeProperty('--banner-offset');
    return;
  }
  const active = banners[banners.length - 1];
  const height = active.getBoundingClientRect().height || 0;
  document.body.style.setProperty('--banner-offset', `${height}px`);
}

export function initPwaPrompts() {
  if (!isMobileDevice() && !isStandalone()) return;

  let deferredPrompt = null;

  const PERMISSIONS_COOKIE = 'lux_permissions_banner_v1';
  const PERMISSIONS_SESSION_KEY = 'lux_permissions_banner_session';

  window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    deferredPrompt = event;
    maybeShowInstallBanner();
  });

  function hideBanner(id) {
    const banner = document.getElementById(id);
    if (banner && banner.parentElement) {
      if (banner.__resizeHandler) {
        window.removeEventListener('resize', banner.__resizeHandler);
        delete banner.__resizeHandler;
      }
      banner.parentElement.removeChild(banner);
      updateBodyBannerOffset();
    }
  }

  function maybeShowInstallBanner() {
    if (isStandalone()) return;
    if (!isMobileDevice()) return;
    if (getCookie('lux_install_banner') === 'hidden') return;

    const banner = createBanner({
      id: 'lux-install-banner',
      message: 'Install the Lux app on your phone for faster access.',
      buttonLabel: 'Install',
      onConfirm: async bannerEl => {
        if (deferredPrompt) {
          deferredPrompt.prompt();
          const { outcome } = await deferredPrompt.userChoice;
          if (outcome === 'accepted') {
            setCookie('lux_install_banner', 'installed');
            deferredPrompt = null;
            hideBanner('lux-install-banner');
          } else {
            setCookie('lux_install_banner', 'dismissed', 7);
            hideBanner('lux-install-banner');
          }
        } else {
          alert('Use your browser\'s Share or More menu and choose "Add to Home Screen" to install.');
          setCookie('lux_install_banner', 'dismissed', 3);
          hideBanner('lux-install-banner');
        }
      },
      onDismiss: () => {
        setCookie('lux_install_banner', 'hidden', 7);
        hideBanner('lux-install-banner');
      }
    });
    insertBannerUnderNavbar(banner);
  }

  async function maybeShowPermissionsBanner() {
    if (!isStandalone()) return;
    if (!('Notification' in window) && !('geolocation' in navigator)) return;
    if (getCookie(PERMISSIONS_COOKIE) === 'handled') return;
    if (sessionStorage?.getItem(PERMISSIONS_SESSION_KEY) === 'dismissed') return;

    const needs = await permissionsNeeded();
    if (!needs.notifications && !needs.location) {
      setCookie(PERMISSIONS_COOKIE, 'handled');
      return;
    }

    const message = buildPermissionMessage(needs);

    const banner = createBanner({
      id: 'lux-permissions-banner',
      message,
      buttonLabel: 'Enable',
      onConfirm: async () => {
        try {
          if (needs.notifications && 'Notification' in window) {
            try {
              await Notification.requestPermission();
            } catch (error) {
              console.warn('[Lux] Notification permission error', error);
            }
          }

          if (needs.location && 'geolocation' in navigator) {
            await requestGeolocationOnce();
          }
        } finally {
          setCookie(PERMISSIONS_COOKIE, 'handled');
          hideBanner('lux-permissions-banner');
        }
      },
      onDismiss: () => {
        try {
          sessionStorage?.setItem(PERMISSIONS_SESSION_KEY, 'dismissed');
        } catch (error) {}
        hideBanner('lux-permissions-banner');
      }
    });

    insertBannerUnderNavbar(banner);
  }

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
      maybeShowInstallBanner();
      maybeShowPermissionsBanner();
    }
  });

  window.addEventListener('appinstalled', () => {
    setCookie('glint_install_banner', 'installed');
    hideBanner('glint-install-banner');
    maybeShowPermissionsBanner();
  });

  if (isStandalone()) {
    maybeShowPermissionsBanner();
  } else {
    maybeShowInstallBanner();
  }

  function buildPermissionMessage(needs) {
    if (needs.notifications && needs.location) {
      return 'Turn on notifications and location so owners can track your route and send updates.';
    }
    if (needs.notifications) {
      return 'Turn on notifications to get real-time job updates.';
    }
    if (needs.location) {
      return 'Turn on location so owners can see your live route to each job.';
    }
    return 'Enable permissions for the best experience.';
  }

  async function permissionsNeeded() {
    const notificationState = await queryPermission('notifications');
    const locationState = await queryPermission('geolocation');
    return {
      notifications: ('Notification' in window) && notificationState !== 'granted',
      location: ('geolocation' in navigator) && locationState !== 'granted'
    };
  }

  async function queryPermission(name) {
    if (!navigator.permissions || !navigator.permissions.query) return null;
    try {
      const status = await navigator.permissions.query({ name });
      return status.state;
    } catch (error) {
      return null;
    }
  }

  function requestGeolocationOnce() {
    return new Promise(resolve => {
      if (!('geolocation' in navigator)) {
        resolve();
        return;
      }
      navigator.geolocation.getCurrentPosition(
        () => resolve(),
        () => resolve(),
        { enableHighAccuracy: true, timeout: 15000 }
      );
    });
  }
}
