<template>
  <OwnerLayout :title="'Create job'" :breadcrumbs="breadcrumbs">
    <div class="job-create">
      <header class="job-create__head">
        <div>
          <p class="eyebrow">Manual job composer</p>
          <h1>Create a job for {{ tenant?.name || 'your company' }}</h1>
          <p class="muted">
            Use the instant pricing calculator, adjust price manually if needed, then pick a date and see the next cadence slots.
          </p>
        </div>
      </header>

      <form class="job-form" @submit.prevent="submit">
        <section class="job-form__section">
          <div class="section-head">
            <p class="label">Customer</p>
            <button type="button" class="link" @click="toggleCustomerMode">
              {{ useExistingCustomer ? 'Create new customer' : 'Select existing' }}
            </button>
          </div>
          <div v-if="useExistingCustomer" class="field">
            <label class="field-label">Existing customer</label>
            <select v-model="form.customer_id" class="field-input">
              <option value="">Select customer</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.name || 'Customer' }}{{ customer.email ? ` - ${customer.email}` : '' }}
              </option>
            </select>
          </div>
          <div v-else class="field-grid">
            <label>
              <span class="field-label">Customer name</span>
              <input v-model="form.customer_name" type="text" class="field-input" placeholder="Gloria Winters" />
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
            <p class="label">Property & address</p>
            <div v-if="availableAddresses.length" class="address-select">
              <select v-model="selectedAddressId" class="field-input">
                <option value="">Manual address</option>
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
            <p class="label">Instant pricing</p>
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
                    class="pill"
                    :class="{ active: option.value === form.storeys }"
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
                    class="pill"
                    :class="{ active: option.value === form.frequency }"
                    @click="form.frequency = option.value"
                  >
                    <strong>{{ option.label }}</strong>
                    <small>{{ option.meta }}</small>
                  </button>
                </div>
              </label>

              <label class="field">
                <span class="field-label">Add-ons</span>
                <div class="addon-grid">
                  <label v-for="addon in addOnOptions" :key="addon.key" class="addon-card">
                    <input type="checkbox" :checked="Boolean(form[addon.key])" @change="toggleAddon(addon.key)" />
                    <div class="addon-content">
                      <div>
                        <p>{{ addon.label }}</p>
                        <small>{{ addon.description }}</small>
                      </div>
                      <span class="price">+{{ addon.meta }}</span>
                    </div>
                  </label>
                </div>
              </label>
            </div>

            <aside class="designer-summary">
              <p class="eyebrow">Live estimate</p>
              <h3>{{ perVisitDisplay }} per visit</h3>
              <p class="muted">{{ planLabel }} · {{ timeEstimate }} · {{ addOnCopy }}</p>

              <dl class="summary-list">
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
                  <dd>{{ planInfo.nextSlot }}</dd>
                </div>
              </dl>

              <div class="override-panel">
                <label class="override-toggle">
                  <input type="checkbox" v-model="advancedPricing" />
                  <span>Advanced pricing override</span>
                </label>
                <div v-if="advancedPricing" class="override-grid">
                  <label>
                    <span class="field-label">Per visit (£)</span>
                    <input type="number" min="0" step="0.5" class="field-input" v-model="overridePrice" />
                  </label>
                  <label>
                    <span class="field-label">Deposit (£)</span>
                    <input type="number" min="0" step="0.5" class="field-input" v-model="overrideDeposit" />
                  </label>
                  <label>
                    <span class="field-label">Duration (mins)</span>
                    <input type="number" min="15" step="5" class="field-input" v-model="overrideDuration" />
                  </label>
                  <label>
                    <span class="field-label">Reason</span>
                    <input type="text" class="field-input" v-model="overrideReason" placeholder="e.g. loyalty discount" />
                  </label>
                </div>
              </div>
            </aside>
          </div>
        </section>

        <section class="job-form__section">
          <p class="label">Schedule & dispatch</p>
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
                <option value="">Select cleaner</option>
                <option v-for="member in staff" :key="member.id" :value="member.id">
                  {{ member.name }}
                </option>
              </select>
            </label>
            <label>
              <span class="field-label">Notes</span>
              <input v-model="form.notes" type="text" class="field-input" placeholder="Gate code, access notes" />
            </label>
          </div>
        </section>

        <section v-if="cadenceRows.length" class="job-form__section">
          <div class="section-head">
            <p class="label">Upcoming cadence</p>
            <p class="muted small">Preview of the next five visits using this cadence.</p>
          </div>
          <table class="cadence-table">
            <thead>
              <tr>
                <th>Visit</th>
                <th>Date</th>
                <th>Window</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in cadenceRows" :key="row.label">
                <td>{{ row.label }}</td>
                <td>{{ row.date }}</td>
                <td>{{ row.window }}</td>
              </tr>
            </tbody>
          </table>
        </section>

        <section class="submit-row">
          <div>
            <p class="label">Quote summary</p>
            <p class="price">{{ finalPerVisitDisplay }} <span>per visit</span></p>
            <p class="muted">Deposit {{ finalDepositDisplay }} · {{ finalDurationLabel }}</p>
            <p v-if="form.errors && Object.keys(form.errors).length" class="error">{{ firstError }}</p>
          </div>
          <button type="submit" class="btn primary" :disabled="form.processing">
            <span v-if="!form.processing">Create job</span>
            <span v-else>Saving…</span>
          </button>
        </section>
      </form>
    </div>
  </OwnerLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import OwnerLayout from '../../../Layouts/OwnerLayout.vue'
import { calculateWindowQuote, formatCurrency } from '../../../utils/quote'

const props = defineProps({
  customers: { type: Array, default: () => [] },
  staff: { type: Array, default: () => [] },
  tenant: { type: Object, default: null },
})

const breadcrumbs = [
  { label: 'Jobs', href: '/owner/jobs' },
  { label: 'Create job' },
]

const useExistingCustomer = ref(true)
const selectedAddressId = ref('')
const advancedPricing = ref(false)
const overridePrice = ref('')
const overrideDeposit = ref('')
const overrideDuration = ref('')
const overrideReason = ref('')

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

const customers = computed(() => props.customers || [])
const staff = computed(() => props.staff || [])
const tenant = computed(() => props.tenant || null)
const selectedCustomer = computed(() => customers.value.find(customer => customer.id === form.customer_id) || null)
const availableAddresses = computed(() => selectedCustomer.value?.addresses || [])

const designerState = computed(() => ({
  windows: Number(form.windows) || 0,
  storeys: Number(form.storeys) || 1,
  frequency: form.frequency,
  frames: !!form.frames,
  sills: !!form.sills,
  gutters: !!form.gutters,
}))

const planMeta = {
  one_off: { label: 'One-off clean', perMonth: 0, nextSlot: 'Next available slot' },
  four_week: { label: 'Every 4 weeks', perMonth: 1, nextSlot: 'Every 4 weeks' },
  six_week: { label: 'Every 6 weeks', perMonth: 0.66, nextSlot: 'Every 6 weeks' },
  eight_week: { label: 'Every 8 weeks', perMonth: 0.5, nextSlot: 'Every 8 weeks' },
}

const planInfo = computed(() => planMeta[designerState.value.frequency] || planMeta.six_week)
const quoteDetails = computed(() => calculateWindowQuote(designerState.value))
const perVisitAmount = computed(() => quoteDetails.value.total_pence / 100)
const perVisitDisplay = computed(() => formatCurrency(perVisitAmount.value))
const monthlyDisplay = computed(() => {
  const visits = planInfo.value.perMonth || 0
  if (!visits) return perVisitDisplay.value
  return formatCurrency(perVisitAmount.value * visits)
})
const depositAmount = computed(() => quoteDetails.value.deposit_pence / 100)
const depositDisplay = computed(() => formatCurrency(depositAmount.value))
const timeEstimate = computed(() => {
  const minutes = quoteDetails.value.estimate_minutes
  const hours = minutes / 60
  return hours < 1 ? `${minutes} mins` : `${Math.round(hours * 10) / 10} hrs`
})
const planLabel = computed(() => planInfo.value.label)
const addOnCopy = computed(() => {
  const selected = addOnOptions.filter(option => form[option.key]).map(option => option.label)
  if (!selected.length) return 'no add-ons'
  if (selected.length === 1) return selected[0]
  return `${selected.length} add-ons`
})

const cadenceRows = computed(() => {
  if (!form.date || form.frequency === 'one_off') {
    return []
  }
  const increments = [0, 1, 2, 3, 4]
  const weeksLookup = { four_week: 4, six_week: 6, eight_week: 8 }
  const weeks = weeksLookup[form.frequency] || 0
  if (!weeks) return []
  const baseDate = new Date(form.date)
  const durationMinutes = advancedPricing.value && overrideDuration.value ? Number(overrideDuration.value) : quoteDetails.value.estimate_minutes

  return increments.map((offset, index) => {
    const visitDate = new Date(baseDate)
    visitDate.setDate(baseDate.getDate() + weeks * 7 * index)
    return {
      label: index === 0 ? 'Anchor' : `+${index} cadence`,
      date: formatVisitDate(visitDate),
      window: formatWindow(form.start_time, durationMinutes),
    }
  })
})

const finalPerVisitDisplay = computed(() => (advancedPricing.value && overridePrice.value ? formatCurrency(parseFloat(overridePrice.value || 0)) : perVisitDisplay.value))
const finalDepositDisplay = computed(() => (advancedPricing.value && overrideDeposit.value ? formatCurrency(parseFloat(overrideDeposit.value || 0)) : depositDisplay.value))
const finalDurationLabel = computed(() => {
  const minutes = advancedPricing.value && overrideDuration.value ? Number(overrideDuration.value) : quoteDetails.value.estimate_minutes
  if (!minutes) return '—'
  return minutes >= 60 ? `${Math.round((minutes / 60) * 10) / 10} hrs` : `${minutes} mins`
})

const firstError = computed(() => Object.values(form.errors)[0] || '')

watch(selectedCustomer, customer => {
  if (!customer) {
    form.customer_name = ''
    form.customer_email = ''
    form.customer_phone = ''
    return
  }
  useExistingCustomer.value = true
  form.customer_name = customer.name || ''
  form.customer_email = customer.email || ''
  form.customer_phone = customer.phone || ''
  selectedAddressId.value = ''
})

watch(selectedAddressId, id => {
  form.address_id = id || ''
  if (!id) {
    form.lat = ''
    form.lng = ''
    return
  }
  const address = availableAddresses.value.find(addr => addr.id === id)
  if (!address) return
  form.address_line1 = address.line1 || ''
  form.address_line2 = address.line2 || ''
  form.city = address.city || ''
  form.postcode = address.postcode || ''
  form.lat = address.lat || ''
  form.lng = address.lng || ''
})

watch(advancedPricing, enabled => {
  if (enabled) {
    overridePrice.value = perVisitAmount.value.toFixed(2)
    overrideDeposit.value = depositAmount.value.toFixed(2)
    overrideDuration.value = String(quoteDetails.value.estimate_minutes)
  } else {
    overridePrice.value = ''
    overrideDeposit.value = ''
    overrideDuration.value = ''
    overrideReason.value = ''
  }
})

function toggleCustomerMode() {
  useExistingCustomer.value = !useExistingCustomer.value
  if (!useExistingCustomer.value) {
    form.customer_id = ''
    selectedAddressId.value = ''
  }
}

function toggleAddon(key) {
  form[key] = !form[key]
}

function formatVisitDate(date) {
  return date.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short' })
}

function formatWindow(startTime, minutes) {
  if (!startTime) return 'TBC'
  const [hour, min] = startTime.split(':').map(Number)
  const start = new Date()
  start.setHours(hour, min || 0, 0, 0)
  const end = new Date(start)
  end.setMinutes(start.getMinutes() + (minutes || 0))
  return `${padTime(start.getHours())}:${padTime(start.getMinutes())}–${padTime(end.getHours())}:${padTime(end.getMinutes())}`
}

function padTime(value) {
  return String(value).padStart(2, '0')
}

function parsePounds(value, fallback) {
  const parsed = parseFloat(value)
  if (Number.isNaN(parsed)) {
    return fallback
  }
  return Math.round(parsed * 100)
}

function submit() {
  form.transform(data => ({
    ...data,
    override_price_pence: advancedPricing.value ? parsePounds(overridePrice.value, null) : null,
    override_deposit_pence: advancedPricing.value ? parsePounds(overrideDeposit.value, null) : null,
    override_estimate_minutes: advancedPricing.value && overrideDuration.value ? Number(overrideDuration.value) : null,
    override_reason: advancedPricing.value ? (overrideReason.value || 'Manual override') : null,
  }))
    .post('/owner/jobs', {
      preserveScroll: true,
      onSuccess: () => {
        form.reset()
      },
    })
}

if (customers.value.length) {
  form.customer_id = customers.value[0].id
}

if (staff.value.length) {
  form.staff_user_id = staff.value[0].id
}
</script>

<style scoped>
.job-create { display: flex; flex-direction: column; gap: 24px; }
.job-create__head { display: flex; justify-content: space-between; }
.job-form { display: flex; flex-direction: column; gap: 20px; }
.job-form__section { border: 1px solid rgba(15, 23, 42, 0.08); border-radius: 20px; padding: 20px; display: flex; flex-direction: column; gap: 16px; background: #fff; }
.section-head { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
.label { font-weight: 600; color: #0f172a; }
.field { display: flex; flex-direction: column; gap: 10px; }
.field-label { font-size: 14px; color: #475467; }
.field-input { border: 1px solid rgba(15, 23, 42, 0.12); border-radius: 10px; padding: 10px 12px; width: 100%; }
.field-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
.link { background: none; border: none; color: #4f46e5; font-weight: 600; cursor: pointer; }
.address-select { width: 220px; }
.designer { display: flex; flex-direction: column; gap: 18px; }
@media (min-width: 960px) { .designer { flex-direction: row; align-items: flex-start; } }
.designer-controls { flex: 1; display: flex; flex-direction: column; gap: 16px; }
.designer-summary { flex: 0 0 320px; border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 18px; background: #f8fafc; display: flex; flex-direction: column; gap: 12px; }
.range-track { position: relative; }
.range-track input { width: 100%; }
.range-labels { position: absolute; inset: 0; display: flex; justify-content: space-between; font-size: 12px; color: #94a3b8; pointer-events: none; }
.pill-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; }
.pill { border: 1px solid rgba(15, 23, 42, 0.1); border-radius: 12px; padding: 10px; text-align: left; background: #fff; cursor: pointer; display: flex; flex-direction: column; gap: 4px; }
.pill.active { border-color: #4f46e5; background: rgba(79,70,229,0.08); }
.addon-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
.addon-card { border: 1px solid rgba(15,23,42,.1); border-radius: 12px; padding: 12px; display: flex; gap: 10px; cursor: pointer; }
.addon-content { display: flex; justify-content: space-between; gap: 12px; width: 100%; }
.summary-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; }
.summary-list dt { font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; }
.summary-list dd { margin: 0; font-weight: 600; }
.override-panel { border-top: 1px solid rgba(15,23,42,.1); padding-top: 12px; display: flex; flex-direction: column; gap: 10px; }
.override-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; }
.cadence-table { width: 100%; border-collapse: collapse; }
.cadence-table th, .cadence-table td { padding: 10px 12px; border: 1px solid rgba(15,23,42,.08); text-align: left; }
.submit-row { border: 1px solid rgba(15,23,42,.08); border-radius: 20px; padding: 20px; background: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
.price { font-size: 26px; margin: 0; font-weight: 700; }
.price span { font-size: 14px; font-weight: 400; color: #94a3b8; margin-left: 8px; }
.muted { color: #64748b; }
.small { font-size: 13px; }
.error { color: #dc2626; margin-top: 6px; }
.btn.primary { background: #4f46e5; color: #fff; border: none; border-radius: 999px; padding: 12px 26px; font-weight: 600; cursor: pointer; }
</style>
