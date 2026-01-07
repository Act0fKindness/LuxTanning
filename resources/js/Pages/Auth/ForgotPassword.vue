<template>
  <div class="container py-5">
    <Head title="Reset password" />
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-4 p-lg-5">
            <h1 class="h4 mb-3">Forgot your password?</h1>
            <p class="text-muted">Enter your email and we will send a reset link if an account exists.</p>
            <div v-if="status" class="alert alert-success">{{ status }}</div>
            <form @submit.prevent="submit">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" v-model="form.email" required autofocus />
                <div class="text-danger small" v-if="form.errors.email">{{ form.errors.email }}</div>
              </div>
              <button class="btn btn-primary w-100" type="submit" :disabled="form.processing">Send reset link</button>
            </form>
            <p class="mt-4 mb-0 text-center">
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

const props = defineProps({ status: String });
const form = useForm({ email: '' });
const submit = () => form.post(route('password.email'));
</script>
