<template>
  <div class="container py-4">
    <Head title="Kiosks" />
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h4 mb-0">Kiosk devices</h1>
        <p class="text-muted mb-0">Generate pairing codes and monitor device state.</p>
      </div>
      <div class="d-flex gap-2">
        <select class="form-select" v-model="selectedShop">
          <option value="">Select shop</option>
          <option v-for="shop in shops" :key="shop.id" :value="shop.id">{{ shop.name }}</option>
        </select>
        <button class="btn btn-primary" :disabled="!selectedShop" @click="generate">Generate pairing code</button>
      </div>
    </div>

    <div v-if="pairingCode" class="alert alert-success d-flex justify-content-between align-items-center">
      <div>
        Pairing code <strong>{{ pairingCode.code }}</strong> (expires {{ pairingCode.expires_at }})
      </div>
      <button class="btn btn-sm btn-outline-light" @click="pairingCode = null">Dismiss</button>
    </div>

    <div class="row g-3">
      <div class="col-md-4" v-for="device in devices" :key="device.id">
        <div class="border rounded-4 p-3 h-100">
          <p class="fw-semibold mb-1">{{ device.name }}</p>
          <p class="text-muted small mb-1">{{ device.shop }}</p>
          <p class="text-muted small mb-2">Status: {{ device.status }}</p>
          <button class="btn btn-outline-danger btn-sm" @click="revoke(device.id)">Revoke</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ shops: Array, devices: Array });
const selectedShop = ref('');
const pairingCode = ref(null);

const generate = async () => {
  const { data } = await window.axios.post(route('app.kiosks.generate', selectedShop.value));
  pairingCode.value = data;
};

const revoke = id => router.post(route('app.devices.revoke', id));
</script>
