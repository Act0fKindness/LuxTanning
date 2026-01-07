<template>
  <div class="app-shell container py-4">
    <Head title="Dashboard" />
    <header class="mb-4">
      <p class="text-muted mb-1">{{ organisation?.name || 'Organisation' }}</p>
      <h1 class="h3 mb-0">Luma OS Control Room</h1>
      <p class="text-muted">Hello {{ user.name }} — role: {{ user.role }}</p>
    </header>
    <div class="row g-4">
      <div class="col-md-4" v-for="card in cards" :key="card.label">
        <div class="p-4 rounded-4 border h-100">
          <p class="text-uppercase small text-muted mb-1">{{ card.label }}</p>
          <h2 class="h3">{{ card.value }}</h2>
          <p class="text-muted small mb-0">{{ card.detail }}</p>
        </div>
      </div>
    </div>
    <section class="mt-5">
      <h2 class="h5 mb-3">Shops in scope</h2>
      <div class="row g-3">
        <div class="col-md-6" v-for="shop in shops" :key="shop.id">
          <div class="border rounded-4 p-3 d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-0 fw-semibold">{{ shop.name }}</p>
              <p class="text-muted small mb-0">{{ shop.timezone }}</p>
            </div>
            <Link :href="route('app.kiosks.index')" class="btn btn-light btn-sm">Kiosks</Link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  user: Object,
  organisation: Object,
  shops: Array,
});

const cards = [
  { label: 'Wallet ledger', value: '+1,240 min', detail: '24h credit change' },
  { label: 'Active kiosk devices', value: '18', detail: 'Across your shops' },
  { label: 'Open invites', value: '3 pending', detail: 'Awaiting acceptance' },
];
</script>
