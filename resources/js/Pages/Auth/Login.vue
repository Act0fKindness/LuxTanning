<template>
  <div class="auth-page container py-5">
    <Head title="Sign in" />
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-body p-4 p-lg-5">
            <h1 class="h3 mb-3">Welcome back</h1>
            <p class="text-muted mb-4">Sign in to the Luma OS admin console.</p>
            <div v-if="status" class="alert alert-info">{{ status }}</div>
            <form @submit.prevent="submit">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" v-model="form.email" required autofocus />
                <div class="text-danger small" v-if="form.errors.email">{{ form.errors.email }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" v-model="form.password" required />
                <div class="text-danger small" v-if="form.errors.password">{{ form.errors.password }}</div>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="remember" v-model="form.remember">
                  <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <Link :href="route('password.request')" class="text-muted">Forgot password?</Link>
              </div>
              <button class="btn btn-primary w-100" type="submit" :disabled="form.processing">Sign in</button>
            </form>
            <hr class="my-4" />
            <div>
              <p class="text-muted mb-2">Haven't activated your invite yet?</p>
              <form class="d-flex gap-2" @submit.prevent="resendInvite">
                <input class="form-control" placeholder="Work email" v-model="inviteEmail" type="email" required />
                <button class="btn btn-outline-secondary" type="submit" :disabled="inviteProcessing">Resend invite</button>
              </form>
            </div>
            <p class="mt-4 mb-0 text-center">
              Need an account?
              <Link :href="route('register')">Create an organisation</Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const inviteEmail = ref('');
const inviteProcessing = ref(false);

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};

const resendInvite = () => {
  inviteProcessing.value = true;
  router.post(route('invites.resend'), { email: inviteEmail.value }, {
    onFinish: () => (inviteProcessing.value = false),
    preserveScroll: true,
  });
};
</script>
