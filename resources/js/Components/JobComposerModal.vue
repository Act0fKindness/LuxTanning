<template>
  <Teleport to="body">
    <div v-if="show" class="job-modal-overlay" @keydown.esc="close">
      <section class="job-modal" role="dialog" aria-modal="true">
        <header class="job-modal__head">
          <div>
            <p class="eyebrow">Job composer</p>
            <h2>Create job for {{ tenant?.name || 'tenant' }}</h2>
          </div>
          <button type="button" class="icon-btn" @click="close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </header>

        <div class="job-modal__body">
          <form class="job-form" @submit.prevent="submit">
            <section class="job-form__section">
              <div class="section-head">
                <p class="label">Customer</p>
                <button type="button" class="link" @click="toggleMode">{{ useExistingCustomer ? 'Create new customer' : 'Select existing' }}</button>
              </div>
              <div v-if="useExistingCustomer" class="field">
                <label class="field-label">Existing customer</label>
                <select v-model="form.customer_id" class="field-input">
                  <option :value="''">Select customer</option>
                  <option v-for="customer in customerOptions" :key="customer.id" :value="customer.id">
                    {{ customer.name }}{{ customer.email ? ` – ${customer.email}` : '' }}
                  </option>
                </select>
              </div>
              <div v-else class="field-grid">
                <label>
                  <span class="field-label">Customer name</span>
                  <input v-model="form.customer_name" type="text" class="field-input" required placeholder="Gloria Winters" />
                </label>
                <label>
                  <span class="field-label">Email</span>
                  <input v-model="form.customer_email" type="email" class="field-input" placeholder="gloria@example.com" />
                </label>
                <label>
                  <span class="field-label">Phone</span>
                  <input v-model="form.customer_phone" type="text" class="field-input" placeholder="+44 7700 900123" />
                </label>
              </div>
            </section>

            <section class="job-form__section">
              <div class="section-head">
                <p class="label">Address</p>
                <div class="address-actions" v-if="availableAddresses.length">
                  <select v-model="selectedAddressId" class="field-input">
                    <option :value="''">Manual address</option>
                    <option v-for="address in availableAddresses" :key="address.id" :value="address.id">
                      {{ address.line1 }} {{ address.postcode ? `(${address.postcode})` : '' }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="field-grid">
                <label>
                  <span class="field-label">Address line 1</span>
                  <input v-model="form.address_line1" type="text" class="field-input" :required="!selectedAddressId" placeholder="10 Downing Street" />
                </label>
                <label>
                  <span class="field-label">Address line 2</span>
                  <input v-model="form.address_line2" type="text" class="field-input" placeholder="Flat, building" />
                </label>
                <label>
                  <span class="field-label">City</span>
                  <input v-model="form.city" type="text" class="field-input" placeholder="London" />
                </label>
                <label>
                  <span class="field-label">Postcode</span>
                  <input v-model="form.postcode" type="text" class="field-input" :required="!selectedAddressId" placeholder="SW1A 1AA" />
                </label>
              </div>
            </section>

            <section class="job-form__section designer-section">
              <div class="section-head">
                <p class="label">Plan builder</p>
              </div>
              <div class="designer">
                <div class="designer-controls">
                  <label class="field">
                    <div class="field-label slider-label">
                      <span>Windows</span>
                      <strong>{{ form.windows }} panes</strong>
                    </div>
                    <div class="range-track">
                      <input type="range" min="4" max="80" step="2" v-model.number="form.windows" />
                      <div class="range-labels">
                        <span class="is-start">4</span>
                        <span>40</span>
                        <span class="is-end">80</span>
                      </div>
                    </div>
                  </label>

                  <label class="field">
                    <span class="field-label">Storeys</span>
                    <div class="pill-row compact">
                      <button
                        v-for="option in storeyOptions"
                        :key="option.value"
                        type="button"
                        :class="['pill', { active: form.storeys === option.value }]"
                        @click="form.storeys = option.value"
                      >
                        <strong>{{ option.label }}</strong>
                        <small>{{ option.meta }}</small>
                      </button>
                    </div>
                  </label>

                  <label class="field">
                    <span class="field-label">Cadence</span>
                    <div class="pill-row">
                      <button
                        v-for="option in frequencyOptions"
                        :key="option.value"
                        type="button"
                        :class="['pill', { active: form.frequency === option.value }]"
                        @click="form.frequency = option.value"
                      >
                        <strong>{{ option.label }}</strong>
                        <small>{{ option.meta }}</small>
                      </button>
                    </div>
                  </label>

                  <div class="field">
                    <span class="field-label">Add-ons</span>
                    <div class="addon-grid">
                      <label v-for="addon in addOnOptions" :key="addon.key" class="addon">
                        <input type="checkbox" v-model="form[addon.key]" />
                        <div class="addon-content">
                          <div class="addon-info">
                            <p>{{ addon.label }}</p>
                            <small>{{ addon.description }}</small>
                          </div>
                          <span class="price">+{{ addon.meta }}</span>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>

                <aside class="designer-summary">
                  <p class="eyebrow">Live estimate</p>
                  <h4>{{ perVisitDisplay }} per visit</h4>
                  <p class="muted">Includes {{ timeEstimate }} on site, {{ addOnCopy }}.</p>

                  <dl class="summary-list">
                    <div>
                      <dt>Plan</dt>
                      <dd>{{ planLabel }}</dd>
                    </div>
                    <div>
                      <dt>Monthly</dt>
                      <dd>{{ monthlyDisplay }}</dd>
                    </div>
                    <div>
                      <dt>Deposit</dt>
                      <dd>{{ depositDisplay }}</dd>
                    </div>
                    <div>
                      <dt>Next slot</dt>
                      <dd>{{ nextSlotCopy }}</dd>
                    </div>
                  </dl>

                  <ul class="line-items">
                    <li>
                      <span>Windows</span>
                      <strong>{{ formatCurrency(quoteDetails.breakdown.base_windows) }}</strong>
                    </li>
                    <li>
                      <span>Add-ons</span>
                      <strong>{{ formatCurrency(quoteDetails.breakdown.extras_total) }}</strong>
                    </li>
                    <li>
                      <span>Storey impact</span>
                      <strong>{{ impactDisplay(quoteDetails.breakdown.storey_adjustment) }}</strong>
                    </li>
                    <li>
                      <span>Cadence impact</span>
                      <strong>{{ impactDisplay(quoteDetails.breakdown.frequency_adjustment) }}</strong>
                    </li>
                    <li>
                      <span>First visit uplift</span>
                      <strong>{{ impactDisplay(quoteDetails.breakdown.first_clean_adjustment) }}</strong>
                    </li>
                  </ul>
                </aside>
              </div>
            </section>

            <section class="job-form__section">
              <p class="label">Schedule</p>
              <div class="field-grid">
                <label>
                  <span class="field-label">Date</span>
                  <input v-model="form.date" type="date" class="field-input" required />
                </label>
                <label>
                  <span class="field-label">Start time</span>
                  <input v-model="form.start_time" type="time" class="field-input" required />
                </label>
                <label>
                  <span class="field-label">Assign cleaner</span>
                  <select v-model="form.staff_user_id" class="field-input" required>
                    <option :value="''">Select cleaner</option>
                    <option v-for="cleaner in cleanerOptions" :key="cleaner.id" :value="cleaner.id">
                      {{ cleaner.name }}
                    </option>
                  </select>
                </label>
                <label>
                  <span class="field-label">Notes</span>
                  <input v-model="form.notes" type="text" class="field-input" placeholder="Gate code, access notes" />
                </label>
              </div>
            </section>

            <section class="job-form__section submit-row">
              <div>
                <p class="label">Quote summary</p>
                <p class="price">
                  {{ perVisitDisplay }} <span>per visit</span>
                </p>
                <p class="muted">Deposit {{ depositDisplay }} · {{ timeEstimate }}</p>
              </div>
              <button type="submit" class="btn primary" :disabled="form.processing">
                <span v-if="!form.processing">Create job</span>
                <span v-else>Saving…</span>
              </button>
            </section>
            <p v-if="firstError" class="error">{{ firstError }}</p>
          </form>
        </div>
      </section>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { calculateWindowQuote, formatCurrency } from '../utils/quote'

const props = defineProps({
  show: { type: Boolean, default: false },
  tenant: { type: Object, default: null },
  customers: { type: Array, default: () => [] },
  staff: { type: Array, default: () => [] },
  recentJobs: { type: Array, default: () => [] },
  defaultCustomerId: { type: String, default: '' },
  prefillJob: { type: Object, default: null },
})

const emit = defineEmits(['close', 'submitted'])

const tenant = computed(() => props.tenant || null)
const prefill = computed(() => props.prefillJob || null)

const form = useForm({
  customer_id: '',
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  address_id: '',
  address_line1: '',
  address_line2: '',
  city: '',
  postcode: '',
  lat: '',
  lng: '',
  storeys: 2,
  windows: 20,
  frames: false,
  sills: false,
  gutters: false,
  frequency: 'six_week',
  date: new Date().toISOString().slice(0, 10),
  start_time: '14:00',
  staff_user_id: '',
  notes: '',
})

const customerOptions = computed(() => props.customers || [])
const selectedCustomer = computed(() => customerOptions.value.find(c => c.id === form.customer_id) || null)
const availableAddresses = computed(() => selectedCustomer.value?.addresses || [])
const cleanerOptions = computed(() => (props.staff || []).filter(member => (member.role_key || '').includes('cleaner') || member.role === 'Cleaner'))
const useExistingCustomer = ref(true)
const selectedAddressId = ref('')

const storeyOptions = [
  { value: 1, label: 'Single storey', meta: 'Compact/bungalow' },
  { value: 2, label: 'Two storey', meta: 'Semi / terrace' },
  { value: 3, label: 'Three+', meta: 'Townhouse wrap' },
]

const frequencyOptions = [
  { value: 'one_off', label: 'One-off', meta: 'Deep clean' },
  { value: 'four_week', label: '4 weeks', meta: 'Most popular' },
  { value: 'six_week', label: '6 weeks', meta: 'Balanced' },
  { value: 'eight_week', label: '8 weeks', meta: 'Budget' },
]

const addOnOptions = [
  { key: 'frames', label: 'Frames & sills detail', description: 'Hand detail frames and trims', meta: '£4' },
  { key: 'sills', label: 'Deep sill polish', description: 'Refresh interior/exterior sills', meta: '£3' },
  { key: 'gutters', label: 'Gutter clear & flush', description: 'Vacuum + rinse 10m run', meta: '£25' },
]

const frequencyMeta = {
  one_off: { label: 'One-off clean', perMonth: 0, nextSlot: 'Thu · 08:00-10:00' },
  four_week: { label: 'Every 4 weeks', perMonth: 1, nextSlot: 'Mon · 13:00-15:00' },
  six_week: { label: 'Every 6 weeks', perMonth: 0.66, nextSlot: 'Wed · 09:00-11:00' },
  eight_week: { label: 'Every 8 weeks', perMonth: 0.5, nextSlot: 'Fri · 11:00-13:00' },
}

const firstError = computed(() => Object.values(form.errors)[0] || '')

watch(
  () => props.show,
  value => {
    if (value) {
      bootstrapDefaults()
    } else {
      form.reset()
      form.clearErrors()
    }
  },
  { immediate: true },
)

watch(selectedCustomer, customer => {
  if (!customer) return
  form.customer_name = customer.name
  form.customer_email = customer.email || ''
  form.customer_phone = customer.phone || ''
  selectedAddressId.value = ''
})

watch(selectedAddressId, id => {
  form.address_id = id || ''
  if (!id) return
  const address = availableAddresses.value.find(addr => addr.id === id)
  if (!address) return
  form.address_line1 = address.line1 || ''
  form.address_line2 = address.line2 || ''
  form.city = address.city || ''
  form.postcode = address.postcode || ''
  form.lat = address.lat || ''
  form.lng = address.lng || ''
})

function bootstrapDefaults() {
  useExistingCustomer.value = Boolean(props.defaultCustomerId || customerOptions.value.length)
  form.customer_id = props.defaultCustomerId || ''
  if (!form.customer_id && customerOptions.value.length) {
    form.customer_id = customerOptions.value[0].id
  }
  if (cleanerOptions.value.length) {
    form.staff_user_id = cleanerOptions.value[0].id
  }
  selectedAddressId.value = ''

  if (prefill.value) {
    if (prefill.value.customer?.id) {
      useExistingCustomer.value = true
      form.customer_id = prefill.value.customer.id
    } else if (prefill.value.customer?.name) {
      useExistingCustomer.value = false
      form.customer_name = prefill.value.customer.name
    }
    if (prefill.value.address) {
      form.address_line1 = prefill.value.address.line1 || ''
      form.address_line2 = prefill.value.address.line2 || ''
      form.city = prefill.value.address.city || ''
      form.postcode = prefill.value.address.postcode || ''
    }
    if (prefill.value.date) {
      form.date = prefill.value.date
    }
  }
}

function toggleMode() {
  useExistingCustomer.value = !useExistingCustomer.value
  if (!useExistingCustomer.value) {
    form.customer_id = ''
  }
}

const designerState = computed(() => ({
  windows: Number(form.windows) || 0,
  storeys: Number(form.storeys) || 1,
  frequency: form.frequency,
  frames: !!form.frames,
  sills: !!form.sills,
  gutters: !!form.gutters,
}))

const planInfo = computed(() => frequencyMeta[designerState.value.frequency] || frequencyMeta.six_week)
const quoteDetails = computed(() => calculateWindowQuote(designerState.value))
const perVisitDisplay = computed(() => formatCurrency(quoteDetails.value.total_pence / 100))
const monthlyDisplay = computed(() => {
  const visits = planInfo.value.perMonth || 0
  if (!visits) return perVisitDisplay.value
  return formatCurrency((quoteDetails.value.total_pence / 100) * visits)
})
const depositDisplay = computed(() => formatCurrency(quoteDetails.value.deposit_pence / 100))
const timeEstimate = computed(() => {
  const minutes = quoteDetails.value.estimate_minutes
  const hours = minutes / 60
  return hours < 1 ? `${minutes} mins` : `${Math.round(hours * 10) / 10} hrs`
})
const selectedAddOnLabels = computed(() =>
  addOnOptions
    .filter(option => designerState.value[option.key])
    .map(option => option.label),
)
const addOnCopy = computed(() => {
  const count = selectedAddOnLabels.value.length
  if (!count) return 'no add-ons'
  if (count === 1) return selectedAddOnLabels.value[0]
  return `${count} add-ons`
})
const nextSlotCopy = computed(() => planInfo.value.nextSlot)
const planLabel = computed(() => planInfo.value.label)

function impactDisplay(value) {
  if (!value) return formatCurrency(0)
  const prefix = value >= 0 ? '+' : '−'
  return `${prefix}${formatCurrency(Math.abs(value))}`
}

function close() {
  emit('close')
}

function submit() {
  if (!form.customer_name && !form.customer_id && selectedCustomer.value) {
    form.customer_name = selectedCustomer.value.name
  }
  const action = tenant.value?.id ? `/glint/tenants/${tenant.value.id}/jobs` : null
  if (!action) return
  form.post(action, {
    preserveScroll: true,
    onSuccess: () => {
      emit('submitted')
      close()
      form.reset()
    },
  })
}
</script>

<style scoped>
.job-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(5, 10, 26, 0.55);
  backdrop-filter: blur(3px);
  z-index: 60;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.job-modal {
  width: min(960px, 100%);
  max-height: 95vh;
  background: #fff;
  border-radius: 24px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.job-modal__head {
  padding: 20px 28px;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.job-modal__body {
  padding: 24px;
  overflow-y: auto;
}

.job-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.job-form__section {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 18px;
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.label {
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.field-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-label {
  font-size: 13px;
  color: #475467;
}

.field-input,
.field select,
.field input {
  width: 100%;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  padding: 10px 12px;
  font-size: 14px;
}

.designer {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(240px, 300px);
  gap: 18px;
}

.designer-controls {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 18px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.designer-controls .field {
  gap: 6px;
}

.slider-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.slider-label strong {
  font-size: 1rem;
}

.range-track {
  position: relative;
}

.range-track input[type='range'] {
  width: 100%;
  accent-color: #0fb89b;
}

.range-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #94a3b8;
}

.pill-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.pill-row.compact .pill {
  flex: 1 1 calc(33% - 8px);
}

.pill {
  border-radius: 16px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  padding: 10px 12px;
  flex: 1 1 140px;
  background: #f8fffb;
  text-align: left;
  cursor: pointer;
}

.pill strong {
  display: block;
}

.pill small {
  font-size: 0.8rem;
  color: #647a72;
}

.pill.active {
  border-color: #0fb89b;
  box-shadow: 0 6px 18px rgba(15, 184, 155, 0.2);
  background: rgba(15, 184, 155, 0.12);
}

.addon-grid {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.addon {
  border: 1px solid rgba(15, 23, 42, 0.1);
  border-radius: 16px;
  padding: 10px 12px;
  display: flex;
  gap: 10px;
  align-items: flex-start;
  background: #fff;
}

.addon input {
  margin-top: 6px;
}

.addon-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  gap: 12px;
}

.addon-info p {
  margin: 0;
  font-weight: 600;
}

.addon-info small {
  color: #647a72;
}

.price {
  font-size: 24px;
  font-weight: 600;
  margin: 0;
}

.price span {
  font-size: 14px;
  font-weight: 400;
  margin-left: 6px;
  color: #475467;
}

.designer-summary {
  border: 1px solid rgba(15, 184, 155, 0.2);
  border-radius: 18px;
  padding: 16px;
  background: linear-gradient(160deg, rgba(79, 225, 193, 0.15), rgba(255, 255, 255, 0.95));
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.designer-summary .muted {
  margin: 0;
  color: #3a5951;
}

.summary-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
  margin: 0;
}

.summary-list dt {
  font-size: 0.7rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #3a5951;
}

.summary-list dd {
  margin: 0;
  font-weight: 600;
}

.line-items {
  list-style: none;
  margin: 0;
  padding: 10px 0;
  border-top: 1px solid rgba(15, 23, 42, 0.12);
  border-bottom: 1px solid rgba(15, 23, 42, 0.12);
  display: flex;
  flex-direction: column;
  gap: 6px;
  color: #0d3c33;
}

.line-items li {
  display: flex;
  justify-content: space-between;
}

.job-form__section.submit-row {
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
}

.btn.primary {
  border: none;
  border-radius: 999px;
  background: #0f172a;
  color: #fff;
  padding: 12px 24px;
  font-weight: 600;
  cursor: pointer;
}

.icon-btn {
  border: none;
  background: rgba(15, 23, 42, 0.05);
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.link {
  background: none;
  border: none;
  color: #2563eb;
  cursor: pointer;
}

.error {
  color: #b42318;
  font-size: 13px;
}

@media (max-width: 640px) {
  .job-modal__body {
    padding: 16px;
  }
  .job-form__section {
    padding: 14px;
  }
  .designer {
    grid-template-columns: 1fr;
  }
}
</style>
