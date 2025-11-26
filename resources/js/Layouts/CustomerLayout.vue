<template>
  <div class="layout-root">
    <Head />
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
      <div class="container">
        <a class="navbar-brand" href="/portal">My Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalnav"><span class="navbar-toggler-icon"></span></button>
        <div id="portalnav" class="collapse navbar-collapse justify-content-end">
          <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="/portal">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="/portal/subscription">Subscription</a></li>
            <li class="nav-item"><a class="nav-link" href="/portal/visits">Visits</a></li>
            <li class="nav-item"><a class="nav-link" href="/portal/payments">Payments</a></li>
            <li class="nav-item"><a class="nav-link" href="/portal/addresses">Addresses</a></li>
            <li class="nav-item"><a class="nav-link" href="/portal/notifications">Notifications</a></li>
            <li class="nav-item"><a class="nav-link" href="/portal/support">Support</a></li>
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
export default { name:'CustomerLayout', components:{ Head } }
</script>

