<template>
  <div class="layout-root">
    <Head />
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
      <div class="container-fluid px-3">
        <a class="navbar-brand" href="/hub">Lux Platform Hub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hubnav"><span class="navbar-toggler-icon"></span></button>
        <div id="hubnav" class="collapse navbar-collapse justify-content-end">
          <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="/hub">Overview</a></li>
            <li class="nav-item"><a class="nav-link" href="/hub/tenants">Tenants</a></li>
            <li class="nav-item"><a class="nav-link" href="/hub/jobs">Jobs</a></li>
            <li class="nav-item"><a class="nav-link" href="/hub/fees">Fees</a></li>
            <li class="nav-item"><a class="nav-link" href="/hub/disputes">Disputes</a></li>
            <li class="nav-item"><a class="nav-link" href="/hub/payouts">Payouts</a></li>
            <li class="nav-item"><a class="nav-link" href="/hub/templates">Templates</a></li>
            <li class="nav-item"><a class="nav-link" href="/hub/ops">Ops</a></li>
            <li class="nav-item"><a class="nav-link" href="/hub/analytics">Analytics</a></li>
          </ul>
        </div>
      </div>
    </nav>
    <main style="margin-top:64px">
      <slot />
    </main>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
export default { name:'SuperAdminLayout', components:{ Head } }
</script>
