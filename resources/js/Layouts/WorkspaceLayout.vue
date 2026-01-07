<template>
  <div class="workspace-root" :style="themeStyles" :data-workspace-skin="workspaceSkinKey">
    <Head>
      <link rel="icon" type="image/png" :href="brandIcon" data-brand-icon />
      <link rel="apple-touch-icon" :href="brandIcon" data-brand-icon />
    </Head>
    <header class="workspace-topbar" :class="{ 'drawer-mode': isOverlayMode }">
      <div class="topbar-left">
        <button v-if="isOverlayMode" class="workspace-burger" type="button" :class="{ open: sidebarOpen }" :aria-expanded="sidebarOpen ? 'true' : 'false'" aria-controls="workspace-nav" @click="toggleSidebar()">
          <span class="sr-only">{{ sidebarOpen ? 'Close menu' : 'Open menu' }}</span>
          <span class="bar" data-line="1"></span><span class="bar" data-line="2"></span><span class="bar" data-line="3"></span>
        </button>
        <div class="topbar-brand">
          <img v-if="brandLogo" :src="brandLogo" :alt="brandName" data-brand-logo />
          <div class="brand-copy">
            <p class="brand-name" data-brand-name>{{ brandName }}</p>
            <p class="brand-role">{{ roleLabel }}</p>
          </div>
        </div>
        <div class="topbar-text">
          <p class="crumb" v-if="breadcrumbs?.length">
            <span v-for="(crumb, idx) in breadcrumbs" :key="crumb.label">
              <a v-if="crumb.href" :href="crumb.href">{{ crumb.label }}</a>
              <span v-else>{{ crumb.label }}</span>
              <span v-if="idx < breadcrumbs.length - 1"> / </span>
            </span>
          </p>
          <h1>{{ title }}</h1>
        </div>
      </div>
      <div class="topbar-actions">
        <div class="topbar-quick">
          <a v-if="backToSiteUrl" :href="backToSiteUrl" target="_blank" rel="noopener">
            <i class="bi bi-box-arrow-up-right"></i>
            Back to {{ marketingHost || 'site' }}
          </a>
          <a v-if="showStatusLink" href="/status" target="_blank" rel="noopener">
            <i class="bi bi-activity"></i>
            Status
          </a>
          <a v-if="showSupportLink" :href="supportLink">
            <i class="bi bi-chat-dots"></i>
            Support
          </a>
        </div>
        <div class="topbar-status">
          <span class="status-pill"><i class="bi bi-activity me-1"></i>{{ statusCopy }}</span>
          <span class="status-pill quiet"><i class="bi bi-clock-history me-1"></i>{{ syncedCopy }}</span>
        </div>
        <slot name="top-actions"></slot>
      </div>
    </header>
    <div :class="['workspace', { pwa: mode === 'pwa' && isOverlayMode, 'drawer-mode': isOverlayMode }]">
      <div v-if="sidebarOpen && isOverlayMode" class="sidebar-overlay" @click="toggleSidebar(false)"></div>
      <aside
        v-if="!isOverlayMode || sidebarOpen"
        id="workspace-nav"
        :class="['sidebar', { open: sidebarOpen, 'drawer-mode': isOverlayMode }]"
        tabindex="-1"
        @keydown.esc="toggleSidebar(false)"
      >
        <nav v-for="section in filteredNav?.primary" :key="section.label" class="nav-section">
          <p class="section-label">{{ section.label }}</p>
          <a v-for="item in section.items" :key="item.href" :href="item.href" class="nav-link">
            <i v-if="item.icon" :class="['bi', item.icon]"></i>
            <span>{{ item.label }}</span>
          </a>
        </nav>

        <div class="sidebar-footer" v-if="backToSiteUrl || poweredByLink">
          <div class="brand-links">
            <a v-if="backToSiteUrl" class="back-link" :href="backToSiteUrl" target="_blank" rel="noopener">
              <i class="bi bi-box-arrow-up-right me-1"></i>Back to {{ marketingHost || 'website' }}
            </a>
            <a v-if="poweredByLink" class="powered-link" :href="poweredByLink.url" target="_blank" rel="noopener">
              Powered by <span>{{ poweredByLink.label }}</span>
            </a>
          </div>
        </div>
        <div class="sidebar-logout">
          <button type="button" class="logout-btn" @click="logout">
            <i class="bi bi-box-arrow-right"></i>
            Logout
          </button>
        </div>
      </aside>

      <main>
        <section class="workspace-body">
          <slot />
        </section>

        <nav v-if="mode === 'pwa'" class="pwa-nav">
          <button v-for="section in filteredNav?.primary" :key="section.label" class="tab-group" type="button">
            <span class="label">{{ section.label }}</span>
            <div class="tabs">
              <a v-for="item in section.items" :key="item.href" :href="item.href" class="tab">
                <i v-if="item.icon" :class="['bi', item.icon]"></i>
                <span>{{ item.label }}</span>
              </a>
            </div>
          </button>
        </nav>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { usePage, router, Head } from '@inertiajs/vue3'
import { resolveCsrfToken } from '../utils/csrf'
import { sidebarGradientFromColor, normalizeHex, pickContrastingTextColor, luminanceFromHex } from '../utils/color'

const props = defineProps({
  role: { type: String, default: 'workspace' },
  mode: { type: String, default: 'workspace' },
  title: { type: String, default: '' },
  breadcrumbs: { type: Array, default: () => [] },
  nav: { type: Object, default: null },
})

const FALLBACK_LOGO = '/images/lux-logo.png'
const PLATFORM_LOGO = FALLBACK_LOGO

const hexToRgba = (hex, alpha = 0.25) => {
  if (!hex || typeof hex !== 'string') return null
  let value = hex.replace('#', '')
  if (!(value.length === 3 || value.length === 6)) return null
  if (value.length === 3) {
    value = value
      .split('')
      .map(ch => ch + ch)
      .join('')
  }
  const parts = value.match(/.{2}/g)
  if (!parts) return null
  const [r, g, b] = parts.map(part => parseInt(part, 16))
  if ([r, g, b].some(component => Number.isNaN(component))) return null
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

const extractHost = url => {
  if (!url || typeof url !== 'string') return null
  try {
    const parsed = new URL(url, 'https://example.com')
    return parsed.hostname?.replace(/^www\./, '') || null
  } catch (e) {
    return null
  }
}

const roleNames = {
  customer: 'Member portal',
  cleaner: 'Studio floor',
  manager: 'Studio operations',
  owner: 'Portfolio HQ',
  accountant: 'Finance suite',
  support: 'Concierge desk',
  glint: 'Platform control',
  shared: 'Workspace',
}

const taglineMap = {
  owner: 'Track every studio, lamp, and membership from one control room.',
  manager: 'Minutes, beds, campaigns, and staff all update in real time.',
  cleaner: 'Shift board with lamp timers, playlists, and guest notes.',
  accountant: 'Granular payouts, bundles, VAT, and dispute workflows.',
  support: 'Concierge toolkit for guests + staff without tab fatigue.',
  glint: 'Platform governance for every Lux tenant and studio.',
  customer: 'Self-serve access to minutes, bookings, and Glow Guides.',
}

const themeMap = {
  workspace: {
    bg: '#eef2ff',
    main: '#fdfefe',
    surface: '#ffffffd9',
    text: '#0b1120',
    subtle: '#667085',
    border: 'rgba(15,23,42,.08)',
    accent: '#FF8C43',
    accentSoft: 'rgba(255,140,67,.22)',
    navText: 'rgba(255,255,255,.92)',
    navHover: 'rgba(255,255,255,.08)',
    sidebar: 'linear-gradient(180deg,#050a1a,#0f172a 60%,#1b1f3b)',
    shadow: '0 25px 65px rgba(15,23,42,.1)',
  },
  owner: {
    bg: '#f4f4ff',
    main: '#fbfcff',
    surface: '#ffffff',
    text: '#0b1120',
    subtle: '#475467',
    border: 'rgba(84,56,255,.12)',
    accent: '#FF4E68',
    accentSoft: 'rgba(255,78,104,.2)',
    navText: 'rgba(255,255,255,.95)',
    navHover: 'rgba(255,255,255,.12)',
    sidebar: 'linear-gradient(180deg,#110732,#1f1462 60%,#2b1f7a)',
    shadow: '0 35px 80px rgba(17,7,50,.28)',
  },
  manager: {
    bg: '#f3f6ff',
    main: '#ffffff',
    surface: '#fdfdff',
    text: '#0f172a',
    subtle: '#475467',
    border: 'rgba(79,70,229,.12)',
    accent: '#FFBE3D',
    accentSoft: 'rgba(255,190,61,.2)',
    navText: 'rgba(255,255,255,.92)',
    navHover: 'rgba(164,180,255,.15)',
    sidebar: 'linear-gradient(180deg,#0e153a,#1d2565 65%,#24307a)',
    shadow: '0 35px 70px rgba(15,23,42,.18)',
  },
  cleaner: {
    bg: '#f6f8f1',
    main: '#ffffff',
    surface: '#fffff7',
    text: '#0f172a',
    subtle: '#475467',
    border: 'rgba(250,204,21,.3)',
    accent: '#FF8C43',
    accentSoft: 'rgba(255,140,67,.25)',
    navText: 'rgba(255,255,255,.92)',
    navHover: 'rgba(255,255,255,.12)',
    sidebar: 'linear-gradient(180deg,#04120c,#0d2319 55%,#103025)',
    shadow: '0 30px 70px rgba(0,0,0,.2)',
  },
  accountant: {
    bg: '#fef6fb',
    main: '#fff7fb',
    surface: '#ffffff',
    text: '#0f172a',
    subtle: '#475467',
    border: 'rgba(236,72,153,.18)',
    accent: '#FF4E68',
    accentSoft: 'rgba(255,78,104,.18)',
    navText: 'rgba(255,255,255,.92)',
    navHover: 'rgba(244,114,182,.18)',
    sidebar: 'linear-gradient(180deg,#2b0f26,#471334 70%,#5e1f44)',
    shadow: '0 30px 70px rgba(43,15,38,.35)',
  },
  support: {
    bg: '#f0fbff',
    main: '#f9feff',
    surface: '#ffffff',
    text: '#0f172a',
    subtle: '#475467',
    border: 'rgba(14,165,233,.2)',
    accent: '#FFBE3D',
    accentSoft: 'rgba(255,190,61,.18)',
    navText: 'rgba(255,255,255,.95)',
    navHover: 'rgba(56,189,248,.15)',
    sidebar: 'linear-gradient(180deg,#041c2c,#053752 65%,#064968)',
    shadow: '0 30px 70px rgba(4,28,44,.4)',
  },
  glint: {
    bg: '#fff6f0',
    main: '#fffaf6',
    surface: '#ffffff',
    text: '#0f172a',
    subtle: '#475467',
    border: 'rgba(249,115,22,.18)',
    accent: '#FF4E68',
    accentSoft: 'rgba(255,78,104,.2)',
    navText: 'rgba(255,255,255,.92)',
    navHover: 'rgba(251,146,60,.18)',
    sidebar: 'linear-gradient(180deg,#020304,#07211b 55%,#0d3f32 100%)',
    shadow: '0 35px 80px rgba(43,18,5,.4)',
  },
  customer: {
    bg: '#f0fdfa',
    main: '#ffffff',
    surface: '#f7fffd',
    text: '#0f172a',
    subtle: '#0f766e',
    border: 'rgba(45,212,191,.25)',
    accent: '#FF8C43',
    accentSoft: 'rgba(255,140,67,.2)',
    navText: 'rgba(15,23,42,.95)',
    navHover: 'rgba(45,212,191,.15)',
    sidebar: 'linear-gradient(180deg,#022c22,#02463a 70%,#006154)',
    shadow: '0 25px 60px rgba(2,44,34,.35)',
  },
}

const page = usePage()
const sidebarOpen = ref(false)
const isCompact = ref(false)
const LOCK_CLASS = 'workspace-no-scroll'
const theme = computed(() => themeMap[props.role] || themeMap.workspace)
const tenantContext = computed(() => page.props?.tenant || null)
const brandingContext = computed(() => tenantContext.value?.branding || page.props?.branding || {})
const workspaceRoleBuckets = {
  owner: 'owner',
  manager: 'staff',
  accountant: 'staff',
  support: 'staff',
  cleaner: 'staff',
  glint: 'staff',
  customer: 'customer',
}
const workspaceSkinKey = computed(() => workspaceRoleBuckets[props.role] || 'staff')
const brandLogo = computed(() => {
  const fallback = PLATFORM_LOGO || FALLBACK_LOGO
  return brandingContext.value.logo || fallback
})
const brandName = computed(() => tenantContext.value?.name || props.nav?.meta?.name || 'Lux Tanning HQ')
const brandIcon = computed(() => brandingContext.value.icon || brandLogo.value || FALLBACK_LOGO)
const brandColors = computed(() => ({
  primary: brandingContext.value.colors?.primary || '#0C0714',
  secondary: brandingContext.value.colors?.secondary || '#1b1031',
  accent: brandingContext.value.colors?.accent || theme.value.accent,
}))
const workspaceOverrides = computed(() => {
  const workspaces = brandingContext.value.workspaces || {}
  return workspaces[workspaceSkinKey.value] || {}
})
const sidebarOverrideHex = computed(() => normalizeHex(workspaceOverrides.value.sidebar))
const backToSiteUrl = computed(() => tenantContext.value?.back_to_site_url || tenantContext.value?.marketing_url || null)
const marketingHost = computed(() => extractHost(backToSiteUrl.value))
const poweredByLink = computed(() => {
  const powered = brandingContext.value.powered_by
  if (!powered || powered.show === false) {
    return null
  }
  if (!powered.label || !powered.url) {
    return null
  }
  return { label: powered.label, url: powered.url }
})
const supportLink = computed(() =>
  tenantContext.value?.support_url || brandingContext.value.support_url || 'mailto:support@glintlabs.com'
)
const INTEGRATION_KEYS = ['quote', 'status', 'support', 'booking']
const integrationToggles = computed(() => {
  const enabled = brandingContext.value.integrations
  if (!Array.isArray(enabled)) {
    return INTEGRATION_KEYS.reduce((acc, key) => ({ ...acc, [key]: true }), {})
  }
  const set = new Set(enabled)
  return INTEGRATION_KEYS.reduce((acc, key) => {
    acc[key] = set.has(key)
    return acc
  }, {})
})
const filteredNav = computed(() => filterNavByIntegrations(props.nav, integrationToggles.value))
const showStatusLink = computed(() => integrationToggles.value.status !== false)
const showSupportLink = computed(() => integrationToggles.value.support !== false)

const roleLabel = computed(() => roleNames[props.role] || 'Workspace')
const statusCopy = computed(() => props.nav?.meta?.status || 'System services green')
const syncedCopy = computed(() => props.nav?.meta?.sync || 'Synced moments ago')
const sidebarBackground = computed(() => {
  const override = workspaceOverrides.value.sidebar
  if (!override) {
    return theme.value.sidebar
  }
  return sidebarGradientFromColor(override) || override
})

const navTextColor = computed(() => {
  if (!sidebarOverrideHex.value) {
    return theme.value.navText
  }
  return pickContrastingTextColor(sidebarOverrideHex.value, 'rgba(255,255,255,.92)', 'rgba(15,23,42,.9)', 0.55) || theme.value.navText
})

const navHoverColor = computed(() => {
  if (!sidebarOverrideHex.value) {
    return theme.value.navHover
  }
  const luminance = luminanceFromHex(sidebarOverrideHex.value)
  if (luminance === null) {
    return theme.value.navHover
  }
  return luminance > 0.55 ? 'rgba(15,23,42,.08)' : 'rgba(255,255,255,.12)'
})

const themeStyles = computed(() => {
  const accent = brandColors.value.accent || theme.value.accent
  const accentSoft = hexToRgba(brandColors.value.accent, 0.25) || theme.value.accentSoft
  return {
    '--ws-bg': theme.value.bg,
    '--ws-main': theme.value.main,
    '--ws-surface': theme.value.surface,
    '--ws-text': theme.value.text,
    '--ws-subtle': theme.value.subtle,
    '--ws-border': theme.value.border,
    '--ws-accent': accent,
    '--ws-accent-soft': accentSoft,
    '--ws-nav-text': navTextColor.value,
    '--ws-nav-hover': navHoverColor.value,
    '--ws-sidebar': sidebarBackground.value,
    '--ws-shadow': theme.value.shadow,
    '--ws-brand-primary': brandColors.value.primary,
    '--ws-brand-secondary': brandColors.value.secondary,
    '--ws-brand-accent': accent,
  }
})

const forceOverlayMode = computed(() => props.mode === 'pwa' && props.role !== 'cleaner')
const isOverlayMode = computed(() => forceOverlayMode.value || isCompact.value)

const updateScrollLock = state => {
  if (typeof document === 'undefined') return
  document.documentElement.classList.toggle(LOCK_CLASS, !!state)
}

const evaluateViewport = () => {
  if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') {
    isCompact.value = false
    sidebarOpen.value = !forceOverlayMode.value
    updateScrollLock(false)
    return
  }

  try {
    isCompact.value = window.matchMedia('(max-width: 1024px)').matches
  } catch (e) {
    isCompact.value = false
  }

  if (!isOverlayMode.value) {
    sidebarOpen.value = true
    updateScrollLock(false)
  } else {
    sidebarOpen.value = false
    updateScrollLock(false)
  }
}

const toggleSidebar = force => {
  if (!isOverlayMode.value) return
  const next = typeof force === 'boolean' ? force : !sidebarOpen.value
  sidebarOpen.value = next
  updateScrollLock(next)
}

const csrfToken = computed(() => resolveCsrfToken(page?.props))

onMounted(() => {
  evaluateViewport()
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', evaluateViewport)
  }
})

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', evaluateViewport)
  }
  updateScrollLock(false)
})

const logout = () => {
  router.post('/logout', { _token: csrfToken.value }, { onFinish: () => window.location.assign('/') })
}

function filterNavByIntegrations(navConfig, toggles) {
  if (!navConfig?.primary) {
    return navConfig || null
  }

  const filteredSections = navConfig.primary
    .map(section => {
      const filteredItems = (section.items || []).filter(item => {
        if (!item?.feature) {
          return true
        }
        return toggles[item.feature] !== false
      })
      if (!filteredItems.length) {
        return null
      }
      return { ...section, items: filteredItems }
    })
    .filter(Boolean)

  return { ...navConfig, primary: filteredSections }
}
</script>

<style scoped>
.workspace-root {
  min-height: 100vh;
  background: var(--ws-bg);
  color: var(--ws-text);
  display: flex;
  flex-direction: column;
}

.workspace-topbar {
  background: linear-gradient(90deg, #050b0e, #0f1a2b);
  border-bottom: 1px solid rgba(255,255,255,.08);
  padding: 16px clamp(16px, 4vw, 32px);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  box-shadow: 0 25px 60px rgba(5, 11, 14, 0.45);
  position: sticky;
  top: 0;
  z-index: 25;
  color: #fff;
}

.workspace-topbar.drawer-mode {
  position: relative;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  min-width: 0;
}

.topbar-brand {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding-right: 8px;
}

.topbar-brand img {
  height: 38px;
  width: 38px;
  border-radius: 12px;
  object-fit: cover;
  background: #fff;
  padding: 4px;
  box-shadow: 0 10px 18px rgba(5, 11, 14, 0.35);
}

.brand-copy {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
}

.brand-name {
  margin: 0;
  font-weight: 600;
  color: #fff;
}

.brand-role {
  margin: 2px 0 0;
  text-transform: uppercase;
  letter-spacing: .16em;
  font-size: .65rem;
  color: rgba(255,255,255,.6);
}

.topbar-text {
  min-width: 0;
}

.topbar-text h1 {
  margin: 0;
  font-size: clamp(22px, 3vw, 30px);
  color: #fff;
}

.crumb {
  margin: 0 0 4px;
  color: rgba(255,255,255,.65);
  font-size: .85rem;
}

.crumb a {
  color: rgba(255,255,255,.85);
  text-decoration: none;
}

.crumb span span {
  color: rgba(255,255,255,.45);
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.topbar-quick {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.topbar-quick a {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  color: rgba(255,255,255,.85);
  font-weight: 500;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.2);
  padding: 8px 14px;
  background: rgba(255,255,255,.04);
  transition: background .2s ease, border-color .2s ease, color .2s ease;
}

.topbar-quick a:hover {
  background: rgba(255,255,255,.12);
  border-color: rgba(255,255,255,.45);
  color: #fff;
}

.topbar-status {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.workspace {
  display: grid;
  grid-template-columns: 280px 1fr;
  flex: 1;
  background: var(--ws-bg);
  color: var(--ws-text);
}

.workspace.drawer-mode {
  grid-template-columns: 1fr;
}

.workspace.pwa {
  grid-template-columns: 1fr;
  background: var(--ws-sidebar);
  color: #fff;
}

:global(html.workspace-no-scroll) {
  overflow: hidden;
}

.sidebar {
  background: var(--ws-sidebar);
  color: var(--ws-nav-text);
  padding: 28px 22px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  border-right: 1px solid rgba(255,255,255,.08);
  box-shadow: inset -1px 0 0 rgba(255,255,255,.04);
  position: sticky;
  top: 0;
  align-self: flex-start;
  max-height: 100vh;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,.4) transparent;
}

.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,.35);
  border-radius: 999px;
}

.sidebar.drawer-mode {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  width: min(85vw, 320px);
  transform: translateX(-100%);
  transition: transform .3s ease;
  z-index: 30;
  box-shadow: 20px 0 60px rgba(5,10,26,.45);
}

.sidebar.drawer-mode.open {
  transform: translateX(0);
}

.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(5,10,26,.65);
  backdrop-filter: blur(4px);
  z-index: 20;
  animation: fadeIn .2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.sidebar-status {
  display: flex;
  flex-direction: column;
  gap: .4rem;
}

.brand-links {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 8px;
}

.sidebar-footer {
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid rgba(255,255,255,.1);
}

.back-link,
.powered-link {
  color: var(--ws-nav-text);
  text-decoration: none;
  font-size: .78rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  opacity: .85;
  transition: opacity .2s ease;
}

.back-link:hover,
.powered-link:hover {
  opacity: 1;
}

.powered-link span {
  color: var(--ws-brand-accent, var(--ws-accent));
  font-weight: 600;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .4rem .9rem;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.12);
  font-size: .78rem;
  color: var(--ws-nav-text);
}

.status-pill.quiet {
  opacity: .75;
}

.nav-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.section-label {
  margin: 12px 0 0;
  text-transform: uppercase;
  letter-spacing: .15em;
  font-size: 11px;
  color: rgba(255,255,255,.55);
}

.nav-link {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 14px;
  text-decoration: none;
  color: var(--ws-nav-text);
  font-weight: 500;
  border: 1px solid transparent;
  transition: background .2s ease, transform .2s ease;
}

.nav-link:hover {
  background: var(--ws-nav-hover);
  transform: translateX(4px);
}

.sidebar-logout {
  margin-top: auto;
  padding-top: 16px;
}

.sidebar-logout .logout-btn {
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.25);
  background: rgba(255,255,255,.08);
  color: var(--ws-nav-text);
  padding: 10px 14px;
  cursor: pointer;
}

.sidebar-logout .logout-btn:hover {
  background: rgba(255,255,255,.16);
  border-color: rgba(255,255,255,.45);
}

main {
  padding: clamp(24px, 5vw, 40px);
  background: var(--ws-main);
}

.workspace-burger {
  position: relative;
  width: 44px;
  height: 44px;
  border: 1px solid rgba(255,255,255,.25);
  border-radius: 14px;
  background: rgba(15,23,42,.8);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: border-color .2s ease, background .2s ease;
}

.workspace-burger .bar {
  position: absolute;
  width: 22px;
  height: 2px;
  border-radius: 999px;
  background: #fff;
  transition: transform .25s ease, opacity .2s ease, top .25s ease;
}

.workspace-burger .bar[data-line="1"] { top: 16px; }
.workspace-burger .bar[data-line="2"] { top: 22px; }
.workspace-burger .bar[data-line="3"] { top: 28px; }

.workspace-burger.open .bar[data-line="1"] {
  transform: rotate(45deg);
  top: 22px;
}

.workspace-burger.open .bar[data-line="2"] {
  opacity: 0;
}

.workspace-burger.open .bar[data-line="3"] {
  transform: rotate(-45deg);
  top: 22px;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}

.ghost-btn {
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.25);
  color: #fff;
  border-radius: 999px;
  font-weight: 600;
  padding: 10px 18px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: background .2s ease, border-color .2s ease, color .2s ease;
}

.ghost-btn:hover {
  background: rgba(255,255,255,.15);
  border-color: rgba(255,255,255,.45);
}

.workspace-body {
  margin-top: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.workspace.pwa main {
  background: transparent;
  color: #fff;
}

.workspace.pwa .crumb {
  color: rgba(255,255,255,.7);
}

.workspace.pwa .ghost-btn {
  background: rgba(255,255,255,.12);
  border-color: rgba(255,255,255,.35);
}

.pwa-nav {
  margin-top: 32px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.tab-group {
  background: rgba(15,23,42,.55);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 16px;
  padding: 12px;
  color: #fff;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.tab-group .label {
  text-transform: uppercase;
  letter-spacing: .18em;
  font-size: 11px;
  color: rgba(255,255,255,.65);
}

.tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.tab {
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,.08);
  padding: 8px 12px;
  text-decoration: none;
  color: #fff;
  font-size: 14px;
  display: inline-flex;
  gap: 8px;
  align-items: center;
}

.tab:hover {
  background: rgba(255,255,255,.08);
}

@media (max-width: 1200px) {
  .workspace {
    grid-template-columns: 240px 1fr;
  }
}

@media (max-width: 1024px) {
  .topbar-actions {
    width: 100%;
    justify-content: flex-start;
  }
}

@media (max-width: 768px) {
  .workspace-topbar {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
