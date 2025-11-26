<template>
  <div class="layout-root">
    <Head />
    <nav class="navbar navbar-dark bg-dark px-3">
      <a class="navbar-brand d-flex align-items-center" href="/app/today">
        <i class="bi bi-droplet-half me-2"></i> Cleaner App
      </a>
      <button class="btn btn-sm btn-outline-light" @click.prevent="logout">Logout</button>
    </nav>
    <main class="container py-3">
      <slot />
    </main>
  </div>
</template>

<script>
import { Head, router } from '@inertiajs/vue3'
import { resolveCsrfToken } from '../utils/csrf'
export default {
  name:'CleanerLayout',
  components:{ Head },
  computed:{
    csrfToken(){
      return resolveCsrfToken(this.$page?.props)
    }
  },
  methods:{
    logout(){
      const token = this.csrfToken
      router.post('/logout', { _token: token }, { onFinish: () => window.location.assign('/') })
    }
  }
}
</script>

<style>
.navbar{ min-height:56px }
</style>
