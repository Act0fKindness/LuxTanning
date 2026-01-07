<template>
  <div class="auth-layout container py-5">
    <Head title="Create customer account" />
    <div class="row justify-content-center">
      <div class="col-lg-8 col-xl-6">
        <div class="glass-panel shadow-lift">
          <p class="eyebrow text-uppercase text-muted">Customer portal</p>
          <h1 class="h3 mb-2">Join your tanning shop</h1>
          <p class="mb-4">Choose your salon, create login details, and you'll unlock the Luma OS customer experience.</p>

          <form @submit.prevent="submit">
            <div class="mb-3">
              <label class="form-label">Tanning company</label>
              <select class="form-select" v-model="selectedCompanyId">
                <option value="" disabled>Select a company</option>
                <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Preferred shop / location</label>
              <select class="form-select" v-model="form.shop_id" :disabled="availableShops.length === 0" required>
                <option value="" disabled>Select a shop</option>
                <option v-for="shop in availableShops" :key="shop.id" :value="shop.id">
                  {{ shop.name }}<span v-if="shop.city"> · {{ shop.city }}</span>
                  <span v-if="shop.postcode"> ({{ shop.postcode }})</span>
                </option>
              </select>
              <p class="text-muted small mt-1" v-if="availableShops.length === 0">No locations available for this company yet.</p>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">First name</label>
                <input class="form-control" v-model="form.first_name" required />
                <div class="text-danger small" v-if="form.errors.first_name">{{ form.errors.first_name }}</div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Last name</label>
                <input class="form-control" v-model="form.last_name" required />
                <div class="text-danger small" v-if="form.errors.last_name">{{ form.errors.last_name }}</div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input class="form-control" type="email" v-model="form.email" required />
              <div class="text-danger small" v-if="form.errors.email">{{ form.errors.email }}</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Phone (optional)</label>
              <input class="form-control" v-model="form.phone" />
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" v-model="form.password" required autocomplete="new-password" />
                <small class="text-muted">Min 10 characters, include a number.</small>
                <div class="text-danger small" v-if="form.errors.password">{{ form.errors.password }}</div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Confirm password</label>
                <input class="form-control" type="password" v-model="form.password_confirmation" required autocomplete="new-password" />
              </div>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="marketing" v-model="form.marketing_opt_in" />
              <label class="form-check-label" for="marketing">Send me updates, offers, and skincare tips.</label>
            </div>

            <button class="btn btn-primary w-100" type="submit" :disabled="form.processing">
              {{ form.processing ? 'Creating account…' : 'Create customer account' }}
            </button>
            <p class="text-center text-muted mt-3 mb-0">
              Already registered?
              <Link :href="route('login')" class="text-decoration-underline">Sign in</Link>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  companies: { type: Array, default: () => [] },
  selectedCompanyId: { type: String, default: null },
  selectedShopId: { type: String, default: null },
});

const companies = computed(() => props.companies || []);
const selectedCompanyId = ref(props.selectedCompanyId || companies.value[0]?.id || '');

const form = useForm({
  shop_id: props.selectedShopId || '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  marketing_opt_in: false,
});

const availableShops = computed(() => {
  const company = companies.value.find(company => company.id === selectedCompanyId.value);
  return company ? company.shops : [];
});

const ensureShopSelection = () => {
  if (!availableShops.value.length) {
    form.shop_id = '';
    return;
  }
  if (!availableShops.value.some(shop => shop.id === form.shop_id)) {
    form.shop_id = availableShops.value[0].id;
  }
};

watch(selectedCompanyId, ensureShopSelection);
watch(availableShops, ensureShopSelection);
ensureShopSelection();

const submit = () => {
  form.post(route('customer.register.store'));
};
</script>
