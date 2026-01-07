<template>
  <div class="container py-4">
    <Head title="Organisation settings" />
    <h1 class="h4 mb-4">Organisation profile</h1>
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4">
        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label">Support email</label>
            <input class="form-control" type="email" v-model="form.support_email" />
          </div>
          <div class="mb-3">
            <label class="form-label">Support phone</label>
            <input class="form-control" v-model="form.support_phone" />
          </div>
          <div class="mb-3">
            <label class="form-label">Website</label>
            <input class="form-control" v-model="form.website_url" placeholder="https://" />
          </div>
          <div class="mb-3">
            <label class="form-label">Primary colour</label>
            <input class="form-control" type="color" v-model="form.brand.primary_color" />
          </div>
          <div class="mb-3">
            <label class="form-label">Secondary colour</label>
            <input class="form-control" type="color" v-model="form.brand.secondary_color" />
          </div>
          <button class="btn btn-primary" type="submit" :disabled="form.processing">Save changes</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ organisation: Object });
const form = useForm({
  support_email: props.organisation?.support_email || '',
  support_phone: props.organisation?.support_phone || '',
  website_url: props.organisation?.website_url || '',
  brand: {
    primary_color: props.organisation?.brand?.primary_color || '#5438ff',
    secondary_color: props.organisation?.brand?.secondary_color || '#4FE1C1',
  },
});
const submit = () => form.post(route('app.org.update'));
</script>
