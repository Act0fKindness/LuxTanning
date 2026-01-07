<template>
  <div class="container py-5">
    <Head title="Verify email" />
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-4">
            <h1 class="h4 mb-3">Verify your email</h1>
            <p class="text-muted">We sent a verification link to <strong>{{ maskedEmail }}</strong>. Complete this step to unlock the console.</p>
            <div v-if="status" class="alert alert-info">{{ status }}</div>
            <form class="d-flex gap-3" @submit.prevent="resend">
              <button class="btn btn-primary" type="submit" :disabled="form.processing">Resend verification email</button>
              <button class="btn btn-outline-secondary" type="button" @click="editing = !editing">Change email</button>
            </form>
            <div v-if="editing" class="mt-3">
              <label class="form-label">New email</label>
              <form @submit.prevent="updateEmail" class="d-flex gap-2">
                <input class="form-control" type="email" v-model="emailForm.email" required />
                <button class="btn btn-outline-primary" type="submit" :disabled="emailForm.processing">Update</button>
              </form>
              <div class="text-danger small" v-if="emailForm.errors.email">{{ emailForm.errors.email }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  email: String,
  status: String,
});

const mask = email => {
  if (!email) return '';
  const [name, domain] = email.split('@');
  return `${name[0]}***@${domain}`;
};

const maskedEmail = computed(() => mask(props.email));

const form = useForm({});
const emailForm = useForm({ email: props.email || '' });
const editing = ref(false);

const resend = () => form.post(route('verification.send'));
const updateEmail = () => emailForm.post(route('verification.change'), {
  onSuccess: () => (editing.value = false),
});
</script>
