<template>
  <div class="container py-5">
    <Head title="Pair kiosk" />
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-4 p-lg-5">
            <h1 class="h4 mb-2">Pair this kiosk</h1>
            <p class="text-muted">Enter the 6-digit code from the admin console.</p>
            <div v-if="success" class="alert alert-success">Paired! Device {{ success.device_id }} bound to shop {{ success.shop_id }}.</div>
            <form @submit.prevent="submit">
              <div class="mb-3">
                <label class="form-label">Pairing code</label>
                <input class="form-control" v-model="form.code" maxlength="6" minlength="6" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Device label</label>
                <input class="form-control" v-model="form.device_name" required />
              </div>
              <button class="btn btn-primary w-100" type="submit" :disabled="form.processing">Pair device</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({ code: '', device_name: '' });
const success = ref(null);

const submit = async () => {
  try {
    const { data } = await window.axios.post(route('kiosk.pair.store'), form.data());
    success.value = data;
    form.reset();
  } catch (error) {
    alert('Pairing failed. Check the code.');
  }
};
</script>
