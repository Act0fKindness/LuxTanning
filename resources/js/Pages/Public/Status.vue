<template>
  <div class="container py-5">
    <Head title="Lux Platform Status" />
    <h1 class="mb-4">Lux platform status</h1>
    <p class="text-muted mb-5">Realtime snapshot of booking, wallet, lamp telemetry, and notification services.</p>
    <div class="row g-4">
      <div class="col-md-4" v-for="service in services" :key="service.name">
        <div class="p-4 border rounded-4 h-100">
          <p class="text-uppercase small text-muted mb-1">{{ service.name }}</p>
          <p class="h4 mb-1">
            <span :class="service.status === 'Operational' ? 'text-success' : 'text-danger'">{{ service.status }}</span>
          </p>
          <p class="small text-muted mb-0">Last incident: {{ service.lastIncident }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';

const services = [
  { name: 'Booking + wallet', status: 'Operational', lastIncident: '64 days ago' },
  { name: 'Lamp telemetry collectors', status: 'Operational', lastIncident: '12 days ago' },
  { name: 'Notifications (SMS + push)', status: 'Operational', lastIncident: '18 days ago' },
];
</script>
