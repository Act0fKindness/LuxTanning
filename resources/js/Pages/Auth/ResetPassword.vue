<template>
  <div class="container py-5">
    <Head title="Choose a new password" />
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-body p-4">
            <h1 class="h4 mb-3">Reset password</h1>
            <form @submit.prevent="submit">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" v-model="form.email" required />
                <div class="text-danger small" v-if="form.errors.email">{{ form.errors.email }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">New password</label>
                <input class="form-control" type="password" v-model="form.password" required minlength="10" />
                <div class="text-danger small" v-if="form.errors.password">{{ form.errors.password }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm password</label>
                <input class="form-control" type="password" v-model="form.password_confirmation" required />
              </div>
              <button class="btn btn-primary w-100" type="submit" :disabled="form.processing">Reset password</button>
            </form>
            <p class="mt-4 text-center">
              <Link :href="route('login')">Back to sign in</Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ token: String, email: String });
const form = useForm({ token: props.token, email: props.email || '', password: '', password_confirmation: '' });
const submit = () => form.post(route('password.update'));
</script>
