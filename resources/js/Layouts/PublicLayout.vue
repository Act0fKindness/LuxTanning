<template>
  <div class="layout-root" :style="brandStyles">
    <Head>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    </Head>

    <nav class="lux-nav navbar navbar-expand-lg fixed-top">
      <div class="container">
        <a class="navbar-brand" href="/">
          <img :src="brandLogo" :alt="companyName" />
          <div>
            <p class="brand-eyebrow">Lux Tanning</p>
            <span>{{ companyName }}</span>
          </div>
        </a>
        <button class="burger d-lg-none" :class="{ open: drawerOpen }" @click="toggleDrawer" aria-label="Toggle menu">
          <span></span><span></span><span></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end d-none d-lg-flex">
          <ul class="navbar-nav align-items-lg-center">
            <li class="nav-item" v-if="hasBackLink"><a class="nav-link" :href="backLink" target="_blank" rel="noopener">Back to {{ marketingHost }}</a></li>
            <li class="nav-item" v-for="item in navLinks" :key="item.href"><a class="nav-link" :href="item.href">{{ item.label }}</a></li>
            <li class="nav-item ms-lg-3">
              <a class="btn btn-ghost btn-sm" href="/book"><i class="bi bi-lightning-charge me-1"></i>Book now</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="drawer-overlay d-lg-none" v-if="drawerOpen" @click="toggleDrawer(false)"></div>
    <aside class="drawer d-lg-none" :class="{ open: drawerOpen }" tabindex="-1">
      <div class="drawer-header d-flex align-items-center justify-content-between">
        <a href="/" class="d-inline-flex align-items-center text-decoration-none">
          <img :src="brandLogo" :alt="companyName" />
          <span>{{ companyName }}</span>
        </a>
        <button class="burger small open" @click="toggleDrawer(false)" aria-label="Close menu"><span></span><span></span><span></span></button>
      </div>
      <nav class="drawer-menu">
        <a v-if="hasBackLink" :href="backLink" class="drawer-link" target="_blank" rel="noopener">Back to {{ marketingHost }}</a>
        <a v-for="item in navLinks" :key="item.href" :href="item.href" class="drawer-link">{{ item.label }}</a>
        <a href="/book" class="drawer-cta">Book now</a>
      </nav>
    </aside>

    <main class="public-main">
      <slot />
    </main>

    <footer class="site-footer">
      <div class="container">
        <div class="footer-grid">
          <div class="footer-brand">
            <img :src="brandLogo" :alt="companyName" />
            <p>Premium UV sun beds · Strood, Kent</p>
            <span>© {{ new Date().getFullYear() }} Lux Tanning Studios Ltd.</span>
          </div>
          <div class="footer-links">
            <p class="label">Explore</p>
            <a href="/courses">Courses</a>
            <a href="/locations">Studios</a>
            <a href="/membership">Membership</a>
            <a href="/privacy">Privacy</a>
            <a href="/terms">Terms</a>
          </div>
          <div class="footer-contact">
            <p class="label">Visit</p>
            <p>3 Station Road, Strood, ME2 4AX</p>
            <a href="tel:01634713989">01634 713989</a>
            <div class="footer-socials">
              <a aria-label="Instagram" href="https://instagram.com/luxtanning" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
              <a aria-label="TikTok" href="https://tiktok.com/@luxtanning" target="_blank" rel="noopener"><i class="bi bi-tiktok"></i></a>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <GlintConcierge />
  </div>
</template>

<script setup>
import { computed, ref, onBeforeUnmount } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import GlintConcierge from '../Components/GlintConcierge.vue'

const DEFAULT_LOGO = '/images/lux-logo.png'

const page = usePage()
const drawerOpen = ref(false)
const props = defineProps({
  useTenantBranding: { type: Boolean, default: false },
})

const tenant = computed(() => page.props?.tenant || null)
const fallbackBranding = computed(() => page.props?.branding || {})
const shouldUseTenantBranding = computed(() => props.useTenantBranding && !!tenant.value)
const activeBranding = computed(() => {
  if (shouldUseTenantBranding.value) {
    return tenant.value?.branding || fallbackBranding.value
  }
  return fallbackBranding.value
})
const brandLogo = computed(() => activeBranding.value.logo || DEFAULT_LOGO)
const companyName = computed(() => (shouldUseTenantBranding.value ? tenant.value?.name || 'Lux Tanning' : 'Lux Tanning'))
const backLink = computed(() =>
  shouldUseTenantBranding.value ? tenant.value?.back_to_site_url || tenant.value?.marketing_url || null : null,
)
const marketingHost = computed(() => extractHost(backLink.value))
const hasBackLink = computed(() => Boolean(backLink.value && marketingHost.value))
const brandStyles = computed(() => ({
  '--brand-accent': activeBranding.value.colors?.accent || '#ffffff',
  '--brand-primary': '#ffffff',
}))

const navLinks = [
  { label: 'Courses', href: '/courses' },
  { label: 'Studios', href: '/locations' },
  { label: 'Membership', href: '/membership' },
  { label: 'Technology', href: '/#technology' },
  { label: 'Shop', href: '/shop' },
  { label: 'Status', href: '/status' },
  { label: 'Login', href: '/login' },
]

const toggleDrawer = force => {
  const next = typeof force === 'boolean' ? force : !drawerOpen.value
  drawerOpen.value = next
  if (typeof document !== 'undefined') {
    document.documentElement.classList.toggle('no-scroll', next)
  }
}

onBeforeUnmount(() => {
  if (typeof document !== 'undefined') {
    document.documentElement.classList.remove('no-scroll')
  }
})

function extractHost(url) {
  if (!url || typeof url !== 'string') return null
  try {
    const parsed = new URL(url, 'https://example.com')
    return parsed.hostname?.replace(/^www\./, '') || null
  } catch (e) {
    return null
  }
}
</script>

<style>
.layout-root {
  background: var(--brand-primary);
}

:root {
  --ink: #050505;
  --stone: #f5f5f5;
  --nav-h: 78px;
}

.lux-nav {
  background: #050505;
  padding: 0.9rem 1.5rem;
  min-height: var(--nav-h);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.navbar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #ffffff;
  font-weight: 600;
}
.navbar-brand img {
  height: 38px;
}
.brand-eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.6rem;
  margin: 0;
  color: rgba(255, 255, 255, 0.45);
}
.navbar .nav-link {
  color: rgba(255, 255, 255, 0.8) !important;
  margin-left: 1.25rem;
  letter-spacing: 0.03em;
}
.navbar .nav-link:hover {
  color: #ffffff !important;
}
.btn-ghost {
  color: #050505;
  background: #ffffff;
  border-radius: 999px;
  padding: 0.55rem 1.2rem;
  font-weight: 600;
  border: none;
}

.burger {
  position: relative;
  width: 42px;
  height: 42px;
  border: none;
  background: transparent;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-left: auto;
  cursor: pointer;
}
.burger span {
  position: absolute;
  width: 22px;
  height: 2px;
  background: #fff;
  border-radius: 2px;
  transition: transform 0.25s ease, opacity 0.2s ease, top 0.25s ease;
}
.burger span:nth-child(1) {
  top: 14px;
}
.burger span:nth-child(2) {
  top: 21px;
}
.burger span:nth-child(3) {
  top: 28px;
}
.burger.open span:nth-child(1) {
  transform: rotate(45deg);
  top: 21px;
}
.burger.open span:nth-child(2) {
  opacity: 0;
}
.burger.open span:nth-child(3) {
  transform: rotate(-45deg);
  top: 21px;
}

.drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(8, 5, 17, 0.6);
  backdrop-filter: blur(3px);
  z-index: 1049;
  animation: fadeIn 0.2s ease;
}
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
.drawer {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: 88%;
  max-width: 320px;
  background: #050505;
  color: #fff;
  transform: translateX(-100%);
  transition: transform 0.25s ease;
  z-index: 1050;
  padding: 20px;
}
.drawer.open {
  transform: none;
}
.drawer-header img {
  height: 28px;
}
.drawer-header span {
  margin-left: 0.5rem;
  font-weight: 600;
}
.drawer-menu {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 8px;
}
.drawer-link {
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  padding: 0.8rem 0.6rem;
  border-radius: 10px;
  display: flex;
  align-items: center;
  font-weight: 500;
}
.drawer-link:hover {
  background: rgba(255, 255, 255, 0.08);
}
.drawer-cta {
  margin: 20px 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 0.75rem 1.4rem;
  background: #ffffff;
  color: #050505;
  text-decoration: none;
  font-weight: 700;
}

.public-main {
  min-height: 60vh;
  padding-top: var(--nav-h);
}

.site-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding: 28px 0;
  background: #050505;
  color: rgba(255, 255, 255, 0.85);
  margin-top: 60px;
}
.site-footer a {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
}
.site-footer a:hover {
  color: #ffffff;
}
.footer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 2rem;
  align-items: start;
}
.footer-brand img {
  height: 42px;
  margin-bottom: 0.5rem;
}
.footer-brand span {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.55);
}
.footer-links,
.footer-contact {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.footer-links .label,
.footer-contact .label {
  text-transform: uppercase;
  letter-spacing: 0.25em;
  font-size: 0.65rem;
  color: rgba(255, 255, 255, 0.4);
  margin-bottom: 0.3rem;
}
.footer-socials {
  display: flex;
  gap: 1rem;
  margin-top: 0.5rem;
}
.footer-socials a {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.85);
}
.footer-socials a:hover {
  color: #fff;
}

.no-scroll {
  overflow: hidden;
}
</style>
