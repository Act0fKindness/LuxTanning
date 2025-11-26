<template>
  <PublicLayout :use-tenant-branding="true">
    <Head title="Track a booking" />
    <div class="track-page">
      <section class="track-hero">
        <p class="track-hero__eyebrow">Live tracking</p>
        <h1>Track your booking</h1>
        <p>Enter your booking number, the name on the booking, and the service postcode to see your cleaner on the map.</p>
      </section>

      <div class="track-grid">
        <section class="track-card">
          <h2>Find your booking</h2>
          <form class="track-form" @submit.prevent="submit">
            <label class="track-field">
              <span>Booking number</span>
              <input v-model="form.booking_number" type="text" placeholder="GLNT-ABC123" autocomplete="off" />
              <small v-if="form.errors.booking_number" class="error">{{ form.errors.booking_number }}</small>
            </label>
            <label class="track-field">
              <span>Name on booking</span>
              <input v-model="form.customer_name" type="text" placeholder="Jamie Lee" autocomplete="name" />
              <small v-if="form.errors.customer_name" class="error">{{ form.errors.customer_name }}</small>
            </label>
            <label class="track-field">
              <span>Postcode</span>
              <input v-model="form.postal_code" type="text" placeholder="W1A 1AA" autocomplete="postal-code" />
              <small v-if="form.errors.postal_code" class="error">{{ form.errors.postal_code }}</small>
            </label>
            <button type="submit" class="btn-primary" :disabled="form.processing">
              <span v-if="!form.processing">Track booking</span>
              <span v-else>Searching…</span>
            </button>
          </form>
          <p class="help-text">Need help finding your booking number? Check your confirmation email or SMS.</p>
        </section>

        <section v-if="result" class="track-card result-card">
          <div class="result-header">
            <p class="booking-number">Booking {{ result.bookingNumber }}</p>
            <h3>{{ result.customer.name }}</h3>
            <p class="address">
              {{ result.customer.address.line1 }}<br />
              <span>{{ result.customer.address.city }} {{ result.customer.address.postcode }}</span>
            </p>
          </div>
          <div class="status-pill" :class="statusClass">
            <span>{{ result.statusLabel || 'Scheduled' }}</span>
            <small v-if="result.etaWindow">{{ result.etaWindow }}</small>
          </div>
          <div class="window" v-if="result.window.start || result.window.end">
            <p>Arrival window</p>
            <strong>{{ windowCopy }}</strong>
          </div>
        </section>
      </div>

      <section v-if="result" class="result-section">
        <div class="map-panel">
          <TrackBookingMap :api-key="googleMapsKey" :destination="result.map.destination" :latest-ping="result.map.latestPing" />
          <div class="map-meta">
            <div>
              <p class="label">Cleaner</p>
              <p class="value">{{ cleanerCopy }}</p>
            </div>
            <div v-if="result.map.latestPing">
              <p class="label">Last ping</p>
              <p class="value">{{ lastPingCopy }}</p>
            </div>
          </div>
        </div>

        <div class="timeline-panel">
          <h3>Progress</h3>
          <ul class="timeline">
            <li v-for="phase in result.timeline" :key="phase.key" :class="[{ complete: phase.complete, active: phase.active }]">
              <div class="dot"></div>
              <div>
                <p>{{ phase.label }}</p>
                <small v-if="phase.timestamp">{{ formatTimestamp(phase.timestamp) }}</small>
              </div>
            </li>
          </ul>
        </div>
      </section>

      <section v-else-if="hasSearched" class="track-card empty-state">
        <p>{{ notFoundMessage || 'We could not find a booking with those details.' }}</p>
        <p>Double-check the spelling and postcode or contact your cleaning team for help.</p>
      </section>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import PublicLayout from '../../Layouts/PublicLayout.vue'
import TrackBookingMap from '../../Components/TrackBookingMap.vue'

const props = defineProps({
  form: { type: Object, default: () => ({}) },
  result: { type: Object, default: null },
  hasSearched: { type: Boolean, default: false },
  notFoundMessage: { type: String, default: '' },
  googleMapsKey: { type: String, default: '' },
})

const form = useForm({
  booking_number: props.form?.booking_number || '',
  customer_name: props.form?.customer_name || '',
  postal_code: props.form?.postal_code || '',
})

const result = computed(() => props.result)

watch(
  () => props.form,
  value => {
    form.booking_number = value?.booking_number || ''
    form.customer_name = value?.customer_name || ''
    form.postal_code = value?.postal_code || ''
  },
  { deep: true }
)

const statusClass = computed(() => ({
  active: !!result.value,
  [`status-${result.value?.status}`]: true,
}))

const windowCopy = computed(() => {
  const start = result.value?.window?.start
  const end = result.value?.window?.end
  if (start && end) return `${start} – ${end}`
  return start || end || 'Awaiting ETA'
})

const cleanerCopy = computed(() => {
  const staff = result.value?.job?.staff
  if (staff?.name) return staff.name
  return result.value?.tenant?.name || 'Your cleaner'
})

const lastPingCopy = computed(() => {
  const ts = result.value?.map?.latestPing?.recorded_at
  if (!ts) return 'Just now'
  return new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
})

function submit() {
  form.get('/track', {
    preserveScroll: true,
    preserveState: true,
  })
}

function formatTimestamp(value) {
  if (!value) return ''
  return new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.track-page {
  padding: 40px 0 80px;
}

.track-hero {
  text-align: center;
  margin-bottom: 32px;
}

.track-hero__eyebrow {
  text-transform: uppercase;
  letter-spacing: .3em;
  color: #6366f1;
  font-weight: 600;
  font-size: 0.8rem;
}

.track-hero h1 {
  margin: 12px 0 8px;
  font-size: clamp(28px, 4vw, 42px);
}

.track-hero p {
  color: #475467;
  max-width: 480px;
  margin: 0 auto;
}

.track-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.track-card {
  background: #ffffff;
  border-radius: 24px;
  border: 1px solid rgba(15,23,42,0.08);
  padding: 24px;
  box-shadow: 0 30px 70px rgba(15,23,42,0.08);
}

.track-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 12px;
}

.track-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-weight: 600;
  color: #111827;
}

.track-field input {
  border-radius: 14px;
  border: 1px solid rgba(15,23,42,0.15);
  padding: 12px 14px;
  font-size: 1rem;
  font-weight: 500;
}

.track-field input:focus {
  border-color: #4f46e5;
  outline: none;
  box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
}

.btn-primary {
  background: #4f46e5;
  border: none;
  color: #fff;
  padding: 14px 16px;
  border-radius: 999px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #b42318;
  font-weight: 500;
}

.help-text {
  margin-top: 12px;
  color: #475467;
  font-size: 0.9rem;
}

.result-card {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.result-header h3 {
  margin: 6px 0 0;
  font-size: 1.4rem;
}

.result-header .address {
  margin: 6px 0 0;
  color: #475467;
  line-height: 1.3;
}

.booking-number {
  font-size: 0.9rem;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: #6366f1;
  font-weight: 600;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 18px;
  border-radius: 16px;
  background: rgba(79,70,229,0.1);
  color: #312e81;
  font-weight: 600;
}

.window p {
  margin: 0;
  color: #475467;
}

.window strong {
  font-size: 1.4rem;
}

.result-section {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
  gap: 24px;
}

.map-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.map-meta {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
  background: #0f172a;
  color: #fff;
  padding: 12px 18px;
  border-radius: 16px;
}

.map-meta .label {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: .2em;
  font-size: 0.75rem;
  opacity: 0.7;
}

.map-meta .value {
  margin: 4px 0 0;
  font-weight: 600;
}

.timeline-panel {
  background: #ffffff;
  border-radius: 24px;
  border: 1px solid rgba(15,23,42,0.08);
  padding: 24px;
  box-shadow: 0 20px 60px rgba(15,23,42,0.08);
}

.timeline {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.timeline li {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  color: #475467;
}

.timeline li .dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid rgba(79,70,229,0.4);
  margin-top: 4px;
}

.timeline li.complete .dot {
  background: #22c55e;
  border-color: #22c55e;
}

.timeline li.active .dot {
  background: #4f46e5;
  border-color: #4f46e5;
}

.empty-state {
  text-align: center;
  color: #475467;
}

@media (max-width: 992px) {
  .result-section {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .track-card {
    padding: 20px;
  }

  .map-meta {
    grid-template-columns: 1fr;
  }
}
</style>
