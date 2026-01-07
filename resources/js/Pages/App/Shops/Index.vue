<template>
  <div class="container py-4">
    <Head title="Shops" />
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h4 mb-0">Shops</h1>
        <p class="text-muted mb-0">Location configuration + kiosk policies per shop.</p>
      </div>
      <button class="btn btn-primary" type="button" @click="showForm = !showForm">Add shop</button>
    </div>

    <div v-if="showForm" class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Name</label>
              <input class="form-control" v-model="form.name" required />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone</label>
              <input class="form-control" v-model="form.phone" />
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" v-model="form.email" />
          </div>
          <div class="mb-3">
            <label class="form-label">Address line 1</label>
            <input class="form-control" v-model="form.address_line1" required />
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">City</label>
              <input class="form-control" v-model="form.city" required />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Postcode</label>
              <input class="form-control" v-model="form.postcode" required />
            </div>
          </div>
          <button class="btn btn-success" type="submit" :disabled="form.processing">Save shop</button>
        </form>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-6" v-for="shop in shops" :key="shop.id">
        <div class="border rounded-4 p-3">
          <h3 class="h6 mb-1">{{ shop.name }}</h3>
          <p class="text-muted small mb-1">{{ shop.phone }} · {{ shop.city }}</p>
          <p class="text-muted small mb-0">{{ shop.postcode }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ shops: Array });
const showForm = ref(false);
const form = useForm({ name: '', phone: '', email: '', address_line1: '', city: '', postcode: '' });
const submit = () => form.post(route('app.shops.store'), { onSuccess: () => { form.reset(); showForm.value = false; } });
</script>
