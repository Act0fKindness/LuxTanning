<template>
  <div class="container py-5">
    <Head title="Accept invitation" />
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-4 p-lg-5">
            <p class="text-uppercase small text-muted mb-1">Invitation</p>
            <h1 class="h4 mb-3">Join {{ invitee.organisation?.name }}</h1>
            <p class="text-muted">You are being invited as <strong>{{ invitee.role }}</strong>.</p>
            <div class="mb-4">
              <p class="small text-muted mb-1">Shops</p>
              <span class="badge bg-light text-dark me-2" v-for="shop in invitee.shops" :key="shop.id">{{ shop.name }}</span>
            </div>
            <form @submit.prevent="accept">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">First name</label>
                  <input class="form-control" v-model="form.first_name" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Last name</label>
                  <input class="form-control" v-model="form.last_name" required />
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Set password</label>
                <input class="form-control" type="password" v-model="form.password" required minlength="10" />
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm password</label>
                <input class="form-control" type="password" v-model="form.password_confirmation" required />
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="terms" v-model="form.terms" required />
                <label class="form-check-label" for="terms">I accept the Luma OS terms.</label>
              </div>
              <div class="d-flex gap-3">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Accept invitation</button>
                <button class="btn btn-outline-secondary" type="button" @click="decline" :disabled="declining">Decline</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  token: String,
  invitee: Object,
});

const form = useForm({ first_name: '', last_name: '', password: '', password_confirmation: '', terms: false });
const declining = ref(false);

const accept = () => form.post(route('invites.accept', props.token));
const decline = () => {
  declining.value = true;
  router.post(route('invites.decline', props.token), {}, { onFinish: () => (declining.value = false) });
};
</script>
