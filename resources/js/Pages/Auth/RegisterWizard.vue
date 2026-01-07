<template>
  <div class="auth-shell">
    <Head title="Create organisation" />
    <div class="row g-0 min-vh-100">
      <div class="col-lg-6 d-none d-lg-flex bg-dark text-white align-items-center justify-content-center p-5">
        <div>
          <p class="text-uppercase small text-muted mb-3">Multi-step onboarding</p>
          <h1 class="display-5 fw-semibold mb-3">Launch Lux OS in four guided steps.</h1>
          <p class="lead text-muted mb-4">Account, company profile, flagship studio, and kiosk defaults — all tracked in Lux audit logs.</p>
          <ul class="list-unstyled">
            <li class="mb-2" v-for="item in highlights" :key="item">
              <span class="me-2">⚡️</span>{{ item }}
            </li>
          </ul>
        </div>
      </div>
      <div class="col-lg-6 bg-white p-4 p-xl-5">
        <div class="mb-4">
          <p class="text-muted mb-1">Step {{ currentStep }} / {{ steps.length }}</p>
          <h2 class="h3 mb-0">{{ steps[currentStep - 1].title }}</h2>
          <p class="text-muted small">{{ steps[currentStep - 1].copy }}</p>
          <div class="progress" style="height: 6px;">
            <div class="progress-bar" role="progressbar" :style="{ width: ((currentStep / steps.length) * 100) + '%' }"></div>
          </div>
        </div>

        <form @submit.prevent="handleSubmit">
          <div v-if="currentStep === 1" class="space-y-3">
            <div class="mb-3">
              <label class="form-label">First name</label>
              <input class="form-control" v-model="form.account.first_name" required minlength="2" />
            </div>
            <div class="mb-3">
              <label class="form-label">Last name</label>
              <input class="form-control" v-model="form.account.last_name" required minlength="2" />
            </div>
            <div class="mb-3">
              <label class="form-label">Work email</label>
              <input class="form-control" type="email" v-model="form.account.email" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input class="form-control" type="password" v-model="form.account.password" required minlength="10" placeholder="Min 10 chars, 1 number" />
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm password</label>
              <input class="form-control" type="password" v-model="form.account.password_confirmation" required />
            </div>
          </div>

          <div v-else-if="currentStep === 2" class="space-y-3">
            <div class="mb-3">
              <label class="form-label">Trading name</label>
              <input class="form-control" v-model="form.organisation.trading_name" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Support email</label>
              <input class="form-control" type="email" v-model="form.organisation.support_email" />
            </div>
            <div class="mb-3">
              <label class="form-label">Support phone</label>
              <input class="form-control" v-model="form.organisation.support_phone" required />
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Timezone</label>
                <select class="form-select" v-model="form.organisation.timezone">
                  <option value="Europe/London">Europe/London</option>
                  <option value="Europe/Dublin">Europe/Dublin</option>
                  <option value="Europe/Paris">Europe/Paris</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Currency</label>
                <select class="form-select" v-model="form.organisation.currency">
                  <option value="GBP">GBP</option>
                  <option value="EUR">EUR</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Website</label>
              <input class="form-control" placeholder="https://" v-model="form.organisation.website_url" />
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <button type="button" class="btn btn-link p-0" @click="saveAndExit" :disabled="form.processing">Save & exit</button>
              <small class="text-muted">Creates draft org + user.</small>
            </div>
          </div>

          <div v-else-if="currentStep === 3" class="space-y-3">
            <div class="mb-3">
              <label class="form-label">Shop name</label>
              <input class="form-control" v-model="form.shop.name" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Address line 1</label>
              <input class="form-control" v-model="form.shop.address_line1" required />
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">City</label>
                <input class="form-control" v-model="form.shop.city" required />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Postcode</label>
                <input class="form-control" v-model="form.shop.postcode" required />
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Shop phone</label>
              <input class="form-control" v-model="form.shop.phone" />
            </div>
            <div class="mb-3">
              <label class="form-label">Opening hours preset</label>
              <div class="btn-group w-100">
                <button type="button" class="btn" :class="form.shop.opening_hours_preset === option.value ? 'btn-primary' : 'btn-outline-secondary'" v-for="option in openingPresets" :key="option.value" @click="form.shop.opening_hours_preset = option.value">
                  {{ option.label }}
                </button>
              </div>
            </div>
          </div>

          <div v-else class="space-y-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" v-model="form.kiosk.enable_kiosk">
              <label class="form-check-label">Enable kiosk</label>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3" v-for="toggle in kioskToggles" :key="toggle.key">
                <label class="form-label">{{ toggle.label }}</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" v-model="form.kiosk[toggle.key]">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">Auto reset (seconds)</label>
                <input type="number" class="form-control" v-model.number="form.kiosk.auto_reset_timeout" min="10" max="120" />
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Min minutes</label>
                <input type="number" class="form-control" v-model.number="form.kiosk.min_minutes" min="1" max="30" />
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Max minutes</label>
                <input type="number" class="form-control" v-model.number="form.kiosk.max_minutes" min="1" max="30" />
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-link" :disabled="currentStep === 1" @click="currentStep--">Back</button>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ currentStep === steps.length ? 'Finish setup' : 'Continue' }}</button>
          </div>
        </form>
        <div class="mt-3">
          <Link class="text-muted small" :href="route('login')">Sign in instead</Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const highlights = [
  'Step-by-step validation',
  'Draft + resume with save & exit',
  'Instant audit log entries',
];

const steps = [
  { title: 'Account', copy: 'Personal credentials for the organisation owner.' },
  { title: 'Company profile', copy: 'Trading name, support contacts, timezone, currency.' },
  { title: 'First shop', copy: 'Minimum viable shop with address + preset hours.' },
  { title: 'Kiosk defaults', copy: 'Pairing policies, approvals, minute thresholds.' },
];

const openingPresets = [
  { value: 'standard', label: 'Standard 7-day' },
  { value: 'weekdays', label: 'Weekdays only' },
  { value: 'custom', label: 'Custom' },
];

const kioskToggles = [
  { key: 'allow_bed_selection', label: 'Allow bed selection' },
  { key: 'require_staff_approval', label: 'Require staff approval' },
  { key: 'require_waiver_daily', label: 'Require waiver daily' },
];

const form = useForm({
  account: {
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
  },
  organisation: {
    trading_name: '',
    support_email: '',
    support_phone: '',
    website_url: '',
    timezone: 'Europe/London',
    currency: 'GBP',
  },
  shop: {
    name: 'Main Shop',
    address_line1: '',
    address_line2: '',
    city: '',
    county: '',
    postcode: '',
    phone: '',
    opening_hours_preset: 'standard',
    opening_hours: {},
  },
  kiosk: {
    enable_kiosk: true,
    allow_bed_selection: false,
    require_staff_approval: false,
    require_waiver_daily: true,
    auto_reset_timeout: 30,
    min_minutes: 1,
    max_minutes: 30,
  },
});

const currentStep = ref(1);

const validateStep = () => {
  switch (currentStep.value) {
    case 1:
      return form.account.first_name && form.account.last_name && form.account.email && form.account.password && form.account.password === form.account.password_confirmation;
    case 2:
      return form.organisation.trading_name && form.organisation.support_phone;
    case 3:
      return form.shop.name && form.shop.address_line1 && form.shop.city && form.shop.postcode;
    default:
      return true;
  }
};

const handleSubmit = () => {
  if (currentStep.value < steps.length) {
    if (validateStep()) {
      currentStep.value++;
    }
    return;
  }

  form.post(route('register.store'));
};

const saveAndExit = () => {
  if (!validateStep()) {
    return;
  }

  router.post(route('register.save'), form.data(), {
    preserveScroll: true,
  });
};
</script>

<style scoped>
.auth-shell {
  background: #f5f6fb;
}
.space-y-3 > * + * {
  margin-top: 1rem;
}
</style>
