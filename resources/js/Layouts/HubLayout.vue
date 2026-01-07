<template>
  <div class="layout-root">
    <Head>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    </Head>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
      <div class="container-fluid px-3">
        <a class="navbar-brand" href="/hub"><img :src="whiteLogo" alt="Lux Tanning" /></a>
        <button class="burger d-lg-none" :class="{ open: drawerOpen }" @click="toggleDrawer" aria-label="Toggle menu"><span></span><span></span><span></span></button>
        <div class="collapse navbar-collapse justify-content-end d-none d-lg-flex">
          <ul class="navbar-nav align-items-lg-center">
            <li class="nav-item"><a class="nav-link" :class="{active:isExact('/hub')}" href="/hub">Hub Overview</a></li>
            <li class="nav-item"><a class="nav-link" :class="{active:isActive('/hub/tenants')}" href="/hub/tenants">Companies</a></li>
            <li class="nav-item"><a class="nav-link" :class="{active:isActive('/hub/jobs')}" href="/hub/jobs">Jobs</a></li>
            <li class="nav-item"><a class="nav-link" :class="{active:isActive('/hub/users')}" href="/hub/users">Users</a></li>
            <li class="nav-item"><a class="nav-link" :class="{active:isActive('/hub/fees')}" href="/hub/fees">Fees</a></li>
            <li class="nav-item"><a class="nav-link" :class="{active:isActive('/hub/payouts')}" href="/hub/payouts">Payouts</a></li>
            <li class="nav-item ms-lg-3"><button class="btn btn-ghost btn-sm" @click.prevent="logout"><i class="bi bi-box-arrow-right me-1"></i>Logout</button></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="drawer-overlay d-lg-none" v-if="drawerOpen" @click="toggleDrawer(false)"></div>
    <aside class="drawer d-lg-none" :class="{ open: drawerOpen }">
      <div class="drawer-header d-flex align-items-center justify-content-between">
        <a href="/hub" class="d-inline-flex align-items-center text-decoration-none"><img :src="whiteLogo" alt="Lux Tanning" /></a>
        <button class="burger small open" @click="toggleDrawer(false)"><span></span><span></span><span></span></button>
      </div>
      <nav class="drawer-menu">
        <a href="/hub" class="drawer-link" :class="{active:isExact('/hub')}"><i class="bi bi-speedometer2 me-2"></i>Hub Overview</a>
        <a href="/hub/tenants" class="drawer-link" :class="{active:isActive('/hub/tenants')}"><i class="bi bi-buildings me-2"></i>Companies</a>
        <a href="/hub/jobs" class="drawer-link" :class="{active:isActive('/hub/jobs')}"><i class="bi bi-briefcase me-2"></i>Jobs</a>
        <a href="/hub/users" class="drawer-link" :class="{active:isActive('/hub/users')}"><i class="bi bi-people me-2"></i>Users</a>
        <a href="/hub/fees" class="drawer-link" :class="{active:isActive('/hub/fees')}"><i class="bi bi-cash-coin me-2"></i>Fees</a>
        <a href="/hub/payouts" class="drawer-link" :class="{active:isActive('/hub/payouts')}"><i class="bi bi-bank me-2"></i>Payouts</a>
        <button class="drawer-link btn-link" @click.prevent="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
      </nav>
    </aside>

    <main>
      <div class="container-fluid px-0 pr-5">
        <div class="row g-0">
          <aside class="col-lg-2 col-xl-2 aside--admin d-none d-lg-block">
            <div class="dash-aside">
              <div class="d-flex align-items-center mb-3">
                <div class="avatar me-2"><i class="bi bi-person"></i></div>
                <div>
                  <div class="fw-bold">{{ $page?.props?.auth?.user?.name }}</div>
                  <div class="text-muted small">Lux Platform Owner</div>
                </div>
              </div>
              <nav class="menu">
                <a href="/hub" class="menu-link" :class="{active:isExact('/hub')}"><i class="bi bi-speedometer2 me-2"></i>Hub Overview</a>
                <a href="/hub/tenants" class="menu-link" :class="{active:isActive('/hub/tenants')}"><i class="bi bi-buildings me-2"></i>Companies</a>
                <a href="/hub/jobs" class="menu-link" :class="{active:isActive('/hub/jobs')}"><i class="bi bi-briefcase me-2"></i>Jobs</a>
                <a href="/hub/users" class="menu-link" :class="{active:isActive('/hub/users')}"><i class="bi bi-people me-2"></i>Users</a>
                <a href="/hub/fees" class="menu-link" :class="{active:isActive('/hub/fees')}"><i class="bi bi-cash-coin me-2"></i>Fees</a>
                <a href="/hub/payouts" class="menu-link" :class="{active:isActive('/hub/payouts')}"><i class="bi bi-bank me-2"></i>Payouts</a>
              </nav>
            </div>
          </aside>
          <section class="col-lg-10 col-xl-10 content-col">
            <slot />
          </section>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { Head, router } from '@inertiajs/vue3'
import { resolveCsrfToken } from '../utils/csrf'
export default {
  name: 'HubLayout',
  components: { Head },
  data(){ return { whiteLogo:'/images/lux-logo.png', drawerOpen:false } },
  computed:{
    csrfToken(){
      return resolveCsrfToken(this.$page?.props)
    }
  },
  methods:{
    isActive(prefix){ try{ const url = (this.$page?.url || ''); return url.startsWith(prefix) }catch(e){ return false } },
    isExact(path){ try{ const url = (this.$page?.url || ''); return url === path }catch(e){ return false } },
    toggleDrawer(f){ this.drawerOpen = typeof f==='boolean'?f:!this.drawerOpen; document.documentElement.classList.toggle('no-scroll', this.drawerOpen) },
    logout(){
      const token = this.csrfToken
      try{
        const f=document.getElementById('logoutForm')
        if(f){
          const input = f.querySelector('input[name="_token"]')
          if (input) {
            input.value = token
          }
          f.submit()
          return
        }
        router.post('/logout',{ _token: token },{ onFinish:()=>window.location.assign('/') })
      }catch(e){
        fetch('/logout',{ method:'POST', headers:{ 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':token } }).finally(()=>window.location.assign('/'))
      }
    }
  }
}
</script>

<style>
:root{ --nav-h:70px }
.navbar{ background:#0B0C0F; padding:.8rem 1.25rem; min-height:var(--nav-h) }
.navbar-brand img{height:34px}
.nav-link{ color:#fff!important; opacity:.9; margin-left:1rem }
.nav-link:hover,.nav-link.active{ color:#FF8C43!important; opacity:1 }
.btn-ghost{ color:#0C0714; background:#fbe9dd; border-radius:999px; padding:.55rem 1rem; font-weight:600 }
.burger{ position:relative; width:42px; height:42px; border:none; background:transparent; display:inline-flex; align-items:center; justify-content:center; margin-left:auto; cursor:pointer }
.burger span{ position:absolute; width:22px; height:2px; background:#fff; border-radius:2px; transition:transform .25s, opacity .2s, top .25s }
.burger span:nth-child(1){ top:14px } .burger span:nth-child(2){ top:20px } .burger span:nth-child(3){ top:26px }
.burger.open span:nth-child(1){ transform:rotate(45deg); top:20px } .burger.open span:nth-child(2){ opacity:0 } .burger.open span:nth-child(3){ transform:rotate(-45deg); top:20px }
.drawer{ position:fixed; top:0; left:0; bottom:0; width:88%; max-width:320px; background:#0B0C0F; color:#fff; transform:translateX(-100%); transition:transform .25s; z-index:1050; padding:16px }
.drawer.open{ transform:none } .drawer-header img{ height:28px }
.drawer-menu{ display:flex; flex-direction:column; gap:4px; margin-top:8px }
.drawer-link{ color:#fff; text-decoration:none; padding:.75rem .5rem; border-radius:8px; display:flex; align-items:center }
.drawer-link.active,.drawer-link:hover{ background:rgba(255,255,255,.08) }

/* Aside + content */
.dash-aside{ position:sticky; top:var(--nav-h); margin-top:0; height:calc(100vh - var(--nav-h)); border-right:1px solid #E9EAF0; border-radius:0; padding:20px 16px; background:#fff; box-shadow:none; display:flex; flex-direction:column; gap:16px; overflow-y:auto; max-width:250px; margin-bottom:0!important }
.dash-aside .menu{ display:flex; flex-direction:column; gap:6px }
.dash-aside .menu-link{ display:flex; align-items:center; gap:.25rem; padding:.55rem .75rem; border-radius:10px; text-decoration:none; color:#0B0C0F; border:1px solid transparent }
.dash-aside .menu-link:hover{ background:rgba(255,140,67,.08); border-color:rgba(255,140,67,.35) }
.dash-aside .menu-link.active{ background:rgba(255,140,67,.14); border-color:rgba(255,140,67,.5) }
.aside--admin{ flex:0 0 15%; max-width:15%; }

/* Ensure page content clears fixed navbar */
main { padding-top: var(--nav-h); }
.content-col{ padding: 16px; }
</style>
