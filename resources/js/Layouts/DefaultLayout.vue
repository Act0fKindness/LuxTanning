<template>
  <div class="layout-root">
    <Head>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    </Head>

    <!-- Site-wide Navbar (matches homepage) -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
      <div :class="[$page?.props?.auth?.user ? 'container-fluid px-3' : 'container']">
        <a class="navbar-brand" href="/">
          <img :src="whiteLogo" alt="Lux Tanning" />
        </a>
        <button class="burger" :class="{ open: drawerOpen }" @click="toggleDrawer" aria-label="Toggle menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
        <div id="navmenu" class="collapse navbar-collapse justify-content-end">
          <ul class="navbar-nav align-items-lg-center">
            <template v-if="$page?.props?.auth?.user">
              <template v-if="isPlatformAdmin">
                <li class="nav-item"><a class="nav-link" href="/hub">Hub Overview</a></li>
                <li class="nav-item"><a class="nav-link" href="/hub/jobs">Jobs</a></li>
                <li class="nav-item"><a class="nav-link" href="/hub/tenants">Companies</a></li>
                <li class="nav-item"><a class="nav-link" href="/hub/users">Users</a></li>
                <li class="nav-item"><a class="nav-link" href="/hub/fees">Fees</a></li>
                <li class="nav-item"><a class="nav-link" href="/hub/payouts">Payouts</a></li>
                <li class="nav-item ms-lg-3">
                  <button class="btn btn-ghost btn-sm" @click.prevent="logout"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
                </li>
              </template>
              <template v-else>
                <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="/tenant/jobs">Jobs</a></li>
                <li class="nav-item"><a class="nav-link" href="/tenant/schedule">Schedule</a></li>
                <li class="nav-item"><a class="nav-link" href="/tenant/customers">Customers</a></li>
                <li class="nav-item"><a class="nav-link" href="/tenant/payments">Payments</a></li>
                <li class="nav-item ms-lg-3">
                  <button class="btn btn-ghost btn-sm" @click.prevent="logout"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
                </li>
              </template>
            </template>
            <template v-else>
              <li class="nav-item"><a class="nav-link" href="/courses">Courses</a></li>
              <li class="nav-item"><a class="nav-link" href="/locations">Studios</a></li>
              <li class="nav-item"><a class="nav-link" href="/membership">Membership</a></li>
              <li class="nav-item"><a class="nav-link" href="/#technology">Technology</a></li>
              <li class="nav-item"><a class="nav-link" href="/status">Status</a></li>
              <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
              <li class="nav-item ms-lg-3">
                <a class="btn btn-ghost btn-sm" href="/book"><i class="bi bi-lightning-charge me-1"></i>Book a sun bed</a>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Mobile Drawer -->
    <div class="drawer-overlay" v-if="drawerOpen" @click="toggleDrawer(false)"></div>
    <aside class="drawer" :class="{ open: drawerOpen }" @keydown.esc="toggleDrawer(false)" tabindex="-1">
      <div class="drawer-header d-flex align-items-center justify-content-between">
        <a href="/" class="d-inline-flex align-items-center text-decoration-none">
          <img :src="whiteLogo" alt="Lux Tanning" />
        </a>
        <button class="burger small open" @click="toggleDrawer(false)" aria-label="Close menu"><span></span><span></span><span></span></button>
      </div>
      <nav class="drawer-menu">
          <template v-if="$page?.props?.auth?.user">
            <template v-if="isPlatformAdmin">
              <a href="/hub" class="drawer-link"><i class="bi bi-speedometer2 me-2"></i>Hub Overview</a>
              <a href="/hub/jobs" class="drawer-link"><i class="bi bi-briefcase me-2"></i>Jobs</a>
              <button class="drawer-link btn-link" @click.prevent="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
            </template>
          <template v-else>
            <a href="/dashboard" class="drawer-link"><i class="bi bi-speedometer2 me-2"></i>Overview</a>
            <a href="/tenant/jobs" class="drawer-link"><i class="bi bi-card-checklist me-2"></i>Jobs</a>
            <a href="/tenant/schedule" class="drawer-link"><i class="bi bi-calendar3 me-2"></i>Schedule</a>
            <a href="/tenant/customers" class="drawer-link"><i class="bi bi-people me-2"></i>Customers</a>
            <a href="/tenant/payments" class="drawer-link"><i class="bi bi-credit-card me-2"></i>Payments</a>
            <a href="/tenant/invoices" class="drawer-link"><i class="bi bi-receipt me-2"></i>Invoices</a>
            <a href="/tenant/staff" class="drawer-link"><i class="bi bi-person-badge me-2"></i>Staff</a>
            <a href="/tenant/settings/brand" class="drawer-link"><i class="bi bi-gear me-2"></i>Settings</a>
            <button class="drawer-link btn-link" @click.prevent="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
          </template>
        </template>
          <template v-else>
            <a href="/courses" class="drawer-link">Courses</a>
            <a href="/locations" class="drawer-link">Studios</a>
            <a href="/membership" class="drawer-link">Membership</a>
            <a href="/#technology" class="drawer-link">Technology</a>
            <a href="/status" class="drawer-link">Status</a>
            <a href="/login" class="drawer-link">Login</a>
            <a href="/book" class="drawer-cta">Book a sun bed</a>
          </template>
      </nav>
    </aside>

    <transition name="fade">
      <div v-if="mustChangePassword" class="password-reminder">
        <div class="reminder-content">
          <div>
            <strong>Update your password</strong>
            <p class="mb-0">For security, please change the password set by your admin.</p>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-mint btn-sm" @click="goChangePassword">Change now</button>
            <button class="btn btn-light btn-sm" @click="dismissPasswordReminder">Not now</button>
          </div>
        </div>
      </div>
    </transition>

    <main>
      <template v-if="showAside">
        <div :class="containerClass">
          <div class="row g-4">
            <aside :class="isPlatformAdmin ? 'col-lg-2 col-xl-2 aside--admin' : 'col-lg-2'">
              <div :class="['dash-aside', (isPlatformAdmin && ($page?.url||'').startsWith('/hub')) ? 'admin' : '']">
                <div class="d-flex align-items-center mb-3">
                  <div class="avatar me-2"><i class="bi bi-person"></i></div>
                  <div>
                    <div class="fw-bold">{{ $page?.props?.auth?.user?.name }}</div>
                    <div class="text-muted small">{{ roleLabel }}</div>
                    <div class="text-muted small">{{ companyLabel }}</div>
                  </div>
                </div>
                <template v-if="isPlatformAdmin">
                  <nav class="menu admin-menu mb-3">
                    <a href="/hub" class="menu-link"><i class="bi bi-speedometer2 me-2"></i>Hub Overview</a>
                    <a href="/hub/jobs" class="menu-link"><i class="bi bi-briefcase me-2"></i>Jobs</a>
                    <a href="/hub/tenants" class="menu-link"><i class="bi bi-buildings me-2"></i>Companies</a>
                    <a href="/hub/users" class="menu-link"><i class="bi bi-people me-2"></i>Users</a>
                    <a href="/hub/fees" class="menu-link"><i class="bi bi-cash-coin me-2"></i>Fees</a>
                    <a href="/hub/payouts" class="menu-link"><i class="bi bi-bank me-2"></i>Payouts</a>
                  </nav>
                </template>
                <template v-else>
                  <nav class="menu">
                    <a href="/dashboard" class="menu-link"><i class="bi bi-speedometer2 me-2"></i>Overview</a>
                    <a href="/tenant/jobs" class="menu-link"><i class="bi bi-card-checklist me-2"></i>Jobs</a>
                    <a href="/tenant/schedule" class="menu-link"><i class="bi bi-calendar3 me-2"></i>Schedule</a>
                    <a href="/tenant/customers" class="menu-link"><i class="bi bi-people me-2"></i>Customers</a>
                    <a href="/tenant/payments" class="menu-link"><i class="bi bi-credit-card me-2"></i>Payments</a>
                    <a href="/tenant/invoices" class="menu-link"><i class="bi bi-receipt me-2"></i>Invoices</a>
                    <a href="/tenant/staff" class="menu-link"><i class="bi bi-person-badge me-2"></i>Staff</a>
                    <a href="/tenant/settings/brand" class="menu-link"><i class="bi bi-gear me-2"></i>Settings</a>
                  </nav>
                </template>
              </div>
            </aside>
            <section :class="isPlatformAdmin ? 'col-lg-10 col-xl-10' : 'col-lg-10'">
              <slot />
            </section>
          </div>
        </div>
      </template>
      <template v-else>
        <slot />
      </template>
    </main>
    <!-- Hidden logout form for robust logout on Laravel Auth -->
    <form id="logoutForm" action="/logout" method="POST" style="display:none">
      <input type="hidden" name="_token" :value="csrfToken">
    </form>

    <!-- Site-wide Footer (hide on logged-in/admin pages) -->
    <footer v-if="!$page?.props?.auth?.user">
      <div :class="[$page?.props?.auth?.user ? 'container-fluid px-3' : 'container']">
        <div class="row align-items-center gy-3">
          <div class="col-md-4 text-center text-md-start">
            <img :src="whiteLogo" alt="Lux Tanning" class="mb-2" />
            <div class="small">© 2025 Lux Tanning Studios Ltd.</div>
          </div>
          <div class="col-md-4 text-center">
            <a href="/privacy" class="me-3">Privacy</a>
            <a href="/terms" class="me-3">Terms</a>
            <a href="/status">Status</a>
          </div>
          <div class="col-md-4 text-center text-md-end">
            <a class="me-2" aria-label="Twitter" href="#"><i class="bi bi-twitter"></i></a>
            <a class="me-2" aria-label="LinkedIn" href="#"><i class="bi bi-linkedin"></i></a>
            <a aria-label="Instagram" href="#"><i class="bi bi-instagram"></i></a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { Head, router } from '@inertiajs/vue3'
import { resolveCsrfToken } from '../utils/csrf'
export default {
  name: 'DefaultLayout',
  components: { Head },
  data() {
    return {
      whiteLogo: '/images/lux-logo.png',
      drawerOpen: false
    }
  },
  computed: {
    csrfToken(){
      return resolveCsrfToken(this.$page?.props)
    },
      showAside(){
        try {
          const url = this.$page?.url || ''
        return !!(this.$page?.props?.auth?.user && (url.startsWith('/tenant') || url.startsWith('/dashboard') || (this.isPlatformAdmin && url.startsWith('/hub'))))
      } catch (e) { return false }
    },
    isPlatformAdmin(){
      return (this.$page?.props?.auth?.user?.role === 'platform_admin')
    },
    containerClass(){
      const hub = (this.$page?.url || '').startsWith('/hub')
      return `container-fluid ${this.isPlatformAdmin && hub ? 'px-0' : 'px-3'}`
    },
    roleLabel(){
      const r = this.$page?.props?.auth?.user?.role
      if(!r) return ''
      if (r === 'platform_admin') return 'Glint Labs Owner'
      return String(r).charAt(0).toUpperCase() + String(r).slice(1)
    },
    companyLabel(){
      return this.$page?.props?.auth?.user?.company || ''
    },
    mustChangePassword(){
      return !!this.$page?.props?.auth?.user?.must_change_password
    }
  },
  methods: {
    toggleDrawer(force){
      this.drawerOpen = typeof force === 'boolean' ? force : !this.drawerOpen
      document.documentElement.classList.toggle('no-scroll', this.drawerOpen)
    },
    logout() {
      const token = this.csrfToken
      try {
        const f = document.getElementById('logoutForm')
        if (f) {
          const input = f.querySelector('input[name="_token"]')
          if (input) {
            input.value = token
          }
          f.submit()
          return
        }
        router.post('/logout', { _token: token }, { onFinish: () => window.location.assign('/') })
      } catch (e) {
        fetch('/logout', { method: 'POST', headers: { 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': token }})
          .finally(() => window.location.assign('/'))
      }
    },
    goChangePassword() {
      router.visit('/account/password')
    },
    dismissPasswordReminder() {
      router.post('/account/password/reminder/dismiss', {}, { preserveScroll: true })
    }
  }
}
</script>

<style>
/* Global CSS variables used across pages */
:root{
  --violet:#5438FF;   /* Primary */
  --sky:#A4B4FF;      /* Secondary */
  --mint:#4FE1C1;     /* Accent */
  --white:#FFFFFF;
  --bg:#F7F8FB;
  --black:#0D0D0D;
  --grey:#6B7280;
  --line:#E9EAF0;
  --radius:20px;
  --nav-h:70px;
}

/* Navbar */
.navbar{ background:var(--black); padding:.8rem 1.25rem; min-height: var(--nav-h); }
.navbar-brand img{height:34px; display:block}
.navbar .nav-link{ color:var(--white)!important; margin-left:1rem; opacity:.9; transition:opacity .2s,color .2s; }
.navbar .nav-link:hover{color:var(--mint)!important;opacity:1}
.navbar-dark .navbar-toggler{border-color:rgba(255,255,255,.35)}
.navbar-dark .navbar-toggler-icon{filter:invert(1) grayscale(1) brightness(200%)}
.btn-ghost{ color:var(--black); background:var(--white); border-radius:999px; padding:.55rem 1rem; font-weight:600; }
.navbar .btn-ghost:hover, .navbar .btn-ghost:focus{ background: var(--white) !important; color: var(--black) !important; opacity:.92; }

/* Stylish burger */
.burger{ position:relative; width:42px; height:42px; border:none; background:transparent; display:inline-flex; align-items:center; justify-content:center; margin-left:auto; cursor:pointer; outline:none }
.burger span{ position:absolute; width:22px; height:2px; background:#fff; border-radius:2px; transition:transform .25s ease, opacity .2s ease, top .25s ease }
.burger span:nth-child(1){ top:14px }
.burger span:nth-child(2){ top:20px }
.burger span:nth-child(3){ top:26px }
.burger.open span:nth-child(1){ transform:rotate(45deg); top:20px }
.burger.open span:nth-child(2){ opacity:0 }
.burger.open span:nth-child(3){ transform:rotate(-45deg); top:20px }
.burger.small span{ width:18px }

/* Drawer */
.drawer-overlay{ position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:saturate(120%) blur(2px); z-index:1049; animation:fadeIn .2s ease }
@keyframes fadeIn{ from{opacity:0} to{opacity:1} }
.drawer{ position:fixed; top:0; left:0; height:100vh; width:min(86vw, 360px); background:#0D0D0D; color:#fff; z-index:1050; transform:translateX(-100%); transition:transform .3s cubic-bezier(.4,0,.2,1); box-shadow: 10px 0 40px rgba(0,0,0,.35) }
.drawer.open{ transform:translateX(0) }
.drawer-header{ padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.08) }
.drawer-header img{ height:28px }
.drawer-menu{ display:flex; flex-direction:column; padding:10px 6px }
.drawer-link{ padding:10px 14px; color:#E7E7E7; text-decoration:none; border-radius:10px; display:flex; align-items:center }
.drawer-link:hover{ background:rgba(255,255,255,.06); color:#fff }
.drawer-cta{ margin:10px 14px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:.6rem 1rem; background:var(--mint); color:#0B0C0F; text-decoration:none; font-weight:700 }
.btn-link.drawer-link{ background:none; border:none; text-align:left; width:100% }

/* Prevent scroll when drawer open */
.no-scroll { overflow:hidden }

main { min-height: 60vh; padding-top: calc(var(--nav-h) + 24px); }

/* Aside styles */
.dash-aside{ position:sticky; top:calc(var(--nav-h) + 24px); border:1px solid var(--line); border-radius:16px; padding:16px; background:#fff; box-shadow:0 10px 30px rgba(20,20,40,.06); margin-bottom:0!important }
.dash-aside.admin{ top:var(--nav-h); height:calc(100vh - var(--nav-h)); border-right:1px solid var(--line); border-radius:0; box-shadow:none; overflow-y:auto; padding:24px 20px; display:flex; flex-direction:column; gap:18px; max-width:250px }
.aside--admin{ flex:0 0 15%; max-width:15%; }
.dash-aside.admin .admin-menu{ margin-bottom:0 }

/* Footer */
footer{background:var(--black);color:#bbb;padding:2.5rem 0}
footer a{color:var(--mint);text-decoration:none}
footer a:hover{text-decoration:underline}
footer img{height:28px;display:block}

/* Containers */
.container { max-width: 1100px; }
.password-reminder{ position:sticky; top:70px; z-index:1040; background:#101828; color:#fff; padding:12px 18px; display:flex; justify-content:center; border-bottom:1px solid rgba(255,255,255,0.08); }
.password-reminder .reminder-content{ width:100%; max-width:960px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
.password-reminder p{ color:rgba(255,255,255,0.75); font-size:0.85rem; }
.btn-light.btn-sm{ border:1px solid #d5dae0; border-radius:999px; padding:.35rem .9rem; }
.btn-mint.btn-sm{ background:#4FE1C1; border:none; border-radius:999px; color:#0B0C0F; font-weight:600; padding:.35rem 1rem; }
</style>
