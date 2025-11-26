const PULL_THRESHOLD = 80;

function isStandalone() {
  return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function isAtTop() {
  const el = document.scrollingElement || document.documentElement;
  return (el?.scrollTop ?? window.pageYOffset ?? 0) <= 0;
}

export function initPullToRefresh() {
  if (typeof window === 'undefined' || typeof document === 'undefined') return;
  if (!('ontouchstart' in window)) return;
  if (!isStandalone()) return;

  let startY = null;
  let tracking = false;
  let readyToRefresh = false;

  window.addEventListener('touchstart', event => {
    if (!isAtTop()) {
      tracking = false;
      startY = null;
      readyToRefresh = false;
      return;
    }

    startY = event.touches[0]?.clientY ?? null;
    tracking = startY !== null;
    readyToRefresh = false;
  }, { passive: true });

  window.addEventListener('touchmove', event => {
    if (!tracking || startY === null) return;

    const currentY = event.touches[0]?.clientY ?? startY;
    const delta = currentY - startY;
    readyToRefresh = delta > PULL_THRESHOLD;
  }, { passive: true });

  window.addEventListener('touchend', () => {
    if (tracking && readyToRefresh) {
      window.location.reload();
    }
    startY = null;
    tracking = false;
    readyToRefresh = false;
  });
}
