<template>
  <div class="layout-root" :style="brandStyles">
    <Head>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    </Head>

    <!-- Public Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
      <div class="container">
        <a class="navbar-brand" href="/">
          <img :src="brandLogo" :alt="companyName" />
        </a>
        <button class="burger d-lg-none" :class="{ open: drawerOpen }" @click="toggleDrawer" aria-label="Toggle menu">
          <span></span><span></span><span></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end d-none d-lg-flex">
          <ul class="navbar-nav align-items-lg-center">
            <li class="nav-item" v-if="hasBackLink"><a class="nav-link" :href="backLink" target="_blank" rel="noopener">Back to {{ marketingHost }}</a></li>
            <li class="nav-item"><a class="nav-link" href="/#customise">Customise</a></li>
            <li class="nav-item"><a class="nav-link" href="/#features">Features</a></li>
            <li class="nav-item"><a class="nav-link" href="/#screens">Screens</a></li>
            <li class="nav-item"><a class="nav-link" href="/pricing">Pricing</a></li>
            <li class="nav-item"><a class="nav-link" href="/#faq">FAQ</a></li>
            <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="/register">Register</a></li>
            <li class="nav-item ms-lg-3">
              <a class="btn btn-ghost btn-sm" href="/#contact"><i class="bi bi-chat-dots me-1"></i>Talk to us</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Mobile Drawer -->
    <div class="drawer-overlay d-lg-none" v-if="drawerOpen" @click="toggleDrawer(false)"></div>
    <aside class="drawer d-lg-none" :class="{ open: drawerOpen }" tabindex="-1">
      <div class="drawer-header d-flex align-items-center justify-content-between">
        <a href="/" class="d-inline-flex align-items-center text-decoration-none">
          <img :src="brandLogo" :alt="companyName" />
        </a>
        <button class="burger small open" @click="toggleDrawer(false)" aria-label="Close menu"><span></span><span></span><span></span></button>
      </div>
      <nav class="drawer-menu">
        <a v-if="hasBackLink" :href="backLink" class="drawer-link" target="_blank" rel="noopener">Back to {{ marketingHost }}</a>
        <a href="/#customise" class="drawer-link">Customise</a>
        <a href="/#features" class="drawer-link">Features</a>
        <a href="/#screens" class="drawer-link">Screens</a>
        <a href="/pricing" class="drawer-link">Pricing</a>
        <a href="/#faq" class="drawer-link">FAQ</a>
        <a href="/login" class="drawer-link">Login</a>
        <a href="/register" class="drawer-link">Register</a>
        <a href="/#contact" class="drawer-cta">Talk to us</a>
      </nav>
    </aside>

    <main class="public-main">
      <slot />
    </main>

    <!-- Always show footer on public pages -->
    <footer class="site-footer">
      <div class="container">
        <div class="row align-items-center gy-3">
          <div class="col-md-4 text-center text-md-start">
            <img :src="brandLogo" :alt="companyName" class="mb-2" />
            <div class="small">© {{ new Date().getFullYear() }} Glint Labs Ltd. All rights reserved.</div>
          </div>
          <div class="col-md-4 text-center">
            <a href="/privacy-ploicy" class="me-3">Privacy</a>
            <a href="/terms" class="me-3">Terms</a>
            <a href="#">Status</a>
          </div>
          <div class="col-md-4 text-center text-md-end">
            <a class="me-2" aria-label="Twitter" href="#"><i class="bi bi-twitter"></i></a>
            <a class="me-2" aria-label="LinkedIn" href="#"><i class="bi bi-linkedin"></i></a>
            <a aria-label="Instagram" href="#"><i class="bi bi-instagram"></i></a>
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

const DEFAULT_LOGO = 'https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152'

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
const companyName = computed(() =>
  shouldUseTenantBranding.value ? tenant.value?.name || 'Workspace' : (page.props?.companyName || 'Glint Labs')
)
const backLink = computed(() =>
  shouldUseTenantBranding.value ? tenant.value?.back_to_site_url || tenant.value?.marketing_url || null : null
)
const marketingHost = computed(() => extractHost(backLink.value))
const hasBackLink = computed(() => Boolean(backLink.value && marketingHost.value))
const brandStyles = computed(() => ({
  '--brand-accent': activeBranding.value.colors?.accent || '#4FE1C1',
  '--brand-primary': activeBranding.value.colors?.primary || '#0D0D0D',
}))

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
:root{ --violet:#5438FF; --sky:#A4B4FF; --mint:#4FE1C1; --white:#FFFFFF; --bg:#F7F8FB; --black:#0D0D0D; --grey:#6B7280; --line:#E9EAF0; --radius:20px; --nav-h:70px; --brand-accent: var(--mint); --brand-primary: var(--black); }

.navbar{ background:var(--black); padding:.8rem 1.25rem; min-height: var(--nav-h); }
.navbar-brand img{height:34px; display:block}
.navbar .nav-link{ color:var(--white)!important; margin-left:1rem; opacity:.9; transition:opacity .2s,color .2s; }
.navbar .nav-link:hover{color:var(--brand-accent)!important;opacity:1}
.btn-ghost{ color:var(--black); background:var(--white); border-radius:999px; padding:.55rem 1rem; font-weight:600; }

.burger{ position:relative; width:42px; height:42px; border:none; background:transparent; display:inline-flex; align-items:center; justify-content:center; margin-left:auto; cursor:pointer; outline:none }
.burger span{ position:absolute; width:22px; height:2px; background:#fff; border-radius:2px; transition:transform .25s ease, opacity .2s ease, top .25s ease }
.burger span:nth-child(1){ top:14px }
.burger span:nth-child(2){ top:20px }
.burger span:nth-child(3){ top:26px }
.burger.open span:nth-child(1){ transform:rotate(45deg); top:20px }
.burger.open span:nth-child(2){ opacity:0 }
.burger.open span:nth-child(3){ transform:rotate(-45deg); top:20px }

.drawer-overlay{ position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:saturate(120%) blur(2px); z-index:1049; animation:fadeIn .2s ease }
@keyframes fadeIn{ from{opacity:0} to{opacity:1} }
.drawer{ position:fixed; top:0; left:0; bottom:0; width:88%; max-width:320px; background:#0B0C0F; color:#fff; transform:translateX(-100%); transition:transform .25s ease; z-index:1050; padding:16px }
.drawer.open{ transform:none }
.drawer-header img{ height:28px }
.drawer-menu{ display:flex; flex-direction:column; gap:4px; margin-top:8px }
.drawer-link{ color:#fff; text-decoration:none; padding:.75rem .5rem; border-radius:8px; display:flex; align-items:center }
.drawer-link.active, .drawer-link:hover{ background:rgba(255,255,255,.08) }
.drawer-cta{ margin:10px 14px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:.6rem 1rem; background:var(--brand-accent); color:#0B0C0F; text-decoration:none; font-weight:700 }

.public-main { min-height: 60vh; padding-top: var(--nav-h); }

.site-footer{ border-top:1px solid var(--line); padding:24px 0; background:var(--black); color:#bbb; margin-top:32px }
.site-footer a{ color:var(--brand-accent); margin-left:12px; text-decoration:none }
.site-footer a:hover{ text-decoration:underline }
.no-scroll{ overflow:hidden }
</style>
