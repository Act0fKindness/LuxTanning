<template>
  <section class="quote-panel">
    <header class="quote-head">
      <div>
        <p class="eyebrow">Quote playground</p>
        <h3>Tailor a plan</h3>
        <p class="muted">Match the booking flow demo with live pricing controls.</p>
      </div>
    </header>

    <div class="designer">
      <div class="designer-controls">
        <div class="control-grid">
          <div class="control-card full">
            <div class="field-label slider-label">
              <span>Windows</span>
              <strong>{{ windows }} panes</strong>
            </div>
            <div class="range-track">
              <input type="range" min="4" max="80" step="2" v-model.number="windows" />
              <div class="range-labels">
                <span :class="{ 'is-start': true }">4</span>
                <span>32</span>
                <span :class="{ 'is-end': true }">80</span>
              </div>
            </div>
          </div>

          <div class="control-card">
            <span class="field-label">Storeys</span>
            <div class="pill-row compact">
              <button
                v-for="option in storeyOptions"
                :key="option.value"
                type="button"
                :class="['pill', { active: storeys === option.value }]"
                @click="storeys = option.value"
              >
                <strong>{{ option.label }}</strong>
                <small>{{ option.meta }}</small>
              </button>
            </div>
          </div>

          <div class="control-card">
            <span class="field-label">Cadence</span>
            <div class="pill-row">
              <button
                v-for="option in frequencyOptions"
                :key="option.value"
                type="button"
                :class="['pill', { active: frequency === option.value }]"
                @click="frequency = option.value"
              >
                <strong>{{ option.label }}</strong>
                <small>{{ option.meta }}</small>
              </button>
            </div>
          </div>

          <div class="control-card full">
            <span class="field-label">Add-ons</span>
            <div class="addon-grid">
              <label v-for="addon in addOns" :key="addon.key" class="addon">
                <input type="checkbox" :value="addon.key" v-model="selectedAddOns" />
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

          <div class="control-card full">
            <span class="field-label">Notes</span>
            <textarea v-model="notes" rows="2" placeholder="Add reminder or rationale for this quote"></textarea>
          </div>
        </div>
      </div>

      <aside class="designer-summary">
        <p class="eyebrow">Suggested plan</p>
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
            <dt>ETA hold</dt>
            <dd>{{ nextSlotCopy }}</dd>
          </div>
          <div>
            <dt>Savings vs one-off</dt>
            <dd :class="{ positive: savingsPounds > 0 }">{{ savingsDisplay }}</dd>
          </div>
        </dl>

        <ul class="line-items">
          <li>
            <span>Windows</span>
            <strong>{{ formatCurrencyDisplay(quote.breakdown.base_windows) }}</strong>
          </li>
          <li>
            <span>Add-ons</span>
            <strong>{{ formatCurrencyDisplay(quote.breakdown.extras_total) }}</strong>
          </li>
          <li>
            <span>Storey impact</span>
            <strong>{{ impactDisplay(quote.breakdown.storey_adjustment) }}</strong>
          </li>
          <li>
            <span>Cadence impact</span>
            <strong>{{ impactDisplay(quote.breakdown.frequency_adjustment) }}</strong>
          </li>
          <li>
            <span>First visit uplift</span>
            <strong>{{ impactDisplay(quote.breakdown.first_clean_adjustment) }}</strong>
          </li>
        </ul>

        <div class="summary-actions">
          <button type="button" class="ghost" @click="reset">Reset</button>
          <button type="button" @click="copyQuote">Copy plan</button>
        </div>
        <p v-if="copied" class="copy-feedback">Copied!</p>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { calculateWindowQuote, formatCurrency } from '../utils/quote'

const props = defineProps({
  customer: { type: Object, default: null },
})

const windows = ref(24)
const storeys = ref(2)
const frequency = ref('six_week')
const selectedAddOns = ref([])
const notes = ref('')
const copied = ref(false)

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

const addOns = [
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

const quote = computed(() =>
  calculateWindowQuote({
    windows: windows.value,
    storeys: storeys.value,
    frequency: frequency.value,
    frames: selectedAddOns.value.includes('frames'),
    sills: selectedAddOns.value.includes('sills'),
    gutters: selectedAddOns.value.includes('gutters'),
  }),
)

const planInfo = computed(() => frequencyMeta[frequency.value] || frequencyMeta.six_week)
const perVisit = computed(() => quote.value.total_pence / 100)
const perVisitDisplay = computed(() => formatCurrency(perVisit.value))
const monthly = computed(() => (planInfo.value.perMonth ? perVisit.value * planInfo.value.perMonth : perVisit.value))
const monthlyDisplay = computed(() => formatCurrency(monthly.value))
const depositDisplay = computed(() => formatCurrency(quote.value.deposit_pence / 100))
const timeEstimate = computed(() => {
  const minutes = quote.value.estimate_minutes
  const hours = minutes / 60
  return hours < 1 ? `${minutes} mins` : `${Math.round(hours * 10) / 10} hrs`
})
const baseOneOff = computed(() =>
  calculateWindowQuote({
    windows: windows.value,
    storeys: storeys.value,
    frequency: 'one_off',
    frames: selectedAddOns.value.includes('frames'),
    sills: selectedAddOns.value.includes('sills'),
    gutters: selectedAddOns.value.includes('gutters'),
  }),
)
const savingsPounds = computed(() => Math.max(0, (baseOneOff.value.total_pence - quote.value.total_pence) / 100))
const savingsDisplay = computed(() => (savingsPounds.value > 0 ? `-£${savingsPounds.value.toFixed(2)}` : '—'))
const planLabel = computed(() => planInfo.value.label)
const nextSlotCopy = computed(() => planInfo.value.nextSlot)
const addOnCopy = computed(() => {
  const count = selectedAddOns.value.length
  if (!count) return 'no add-ons'
  if (count === 1) return '1 add-on'
  return `${count} add-ons`
})

watch(
  () => props.customer,
  customer => {
    notes.value = ''
    if (customer?.last_job?.address?.line1) {
      notes.value = `Last: ${customer.last_job.address.line1}`
    }
  },
  { immediate: true },
)

function formatCurrencyDisplay(value) {
  return formatCurrency((value || 0) / 100)
}

function impactDisplay(value) {
  if (!value) return formatCurrencyDisplay(0)
  const prefix = value >= 0 ? '+' : '−'
  return `${prefix}${formatCurrency(Math.abs(value) / 100)}`
}

function reset() {
  windows.value = 24
  storeys.value = 2
  frequency.value = 'six_week'
  selectedAddOns.value = []
  notes.value = ''
}

function copyQuote() {
  const parts = [
    `${perVisitDisplay.value} per visit`,
    `${windows.value} windows`,
    `${storeyOptions.find(option => option.value === storeys.value)?.label || storeys.value} storeys`,
    planLabel.value,
  ]
  if (selectedAddOns.value.length) {
    parts.push(`Add-ons: ${selectedAddOns.value.join(', ')}`)
  }
  navigator.clipboard
    ?.writeText(parts.join(' • '))
    .then(() => {
      copied.value = true
      setTimeout(() => (copied.value = false), 1500)
    })
    .catch(() => {})
}
</script>

<style scoped>
.quote-panel {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 28px;
  background: #fff;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.quote-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
}

.designer {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(260px, 320px);
  gap: 20px;
  align-items: start;
}


.designer-controls {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 22px;
  padding: 16px;
}

.control-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
}

.control-card {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 16px;
  padding: 12px 14px;
  background: #fff;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.control-card.full {
  grid-column: 1 / -1;
}

.field-label {
  font-weight: 600;
  color: #0b3a30;
}

.slider-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.slider-label strong {
  font-size: 1rem;
  color: #032b25;
}

.range-track {
  position: relative;
  padding: 0 4px;
}

.range-track input[type='range'] {
  width: 100%;
  accent-color: #0fb89b;
}

.range-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #758a84;
}

.pill-row {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.pill-row.compact .pill {
  flex: 1 1 calc(33% - 10px);
}

.pill {
  border-radius: 16px;
  border: 1px solid rgba(15, 23, 42, 0.15);
  padding: 10px 14px;
  flex: 1 1 140px;
  background: #f5fffb;
  color: #032b25;
  text-align: left;
  cursor: pointer;
}

.pill small {
  display: block;
  font-size: 0.8rem;
  color: #546b63;
}

.pill.active {
  border-color: #0fb89b;
  background: rgba(15, 184, 155, 0.1);
  box-shadow: 0 8px 20px rgba(15, 184, 155, 0.2);
}

.addon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 10px;
}

.addon {
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 18px;
  padding: 10px 14px;
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
  color: #032b25;
}

.addon-info small {
  color: #536663;
}

.price {
  font-weight: 700;
  white-space: nowrap;
}

.designer-summary {
  border: 1px solid rgba(15, 184, 155, 0.2);
  border-radius: 22px;
  padding: 20px;
  background: linear-gradient(160deg, rgba(79, 225, 193, 0.15), rgba(255, 255, 255, 0.95));
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.designer-summary h4 {
  margin: 0;
  font-size: 1.8rem;
  color: #022b25;
}

.summary-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
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
  color: #022b25;
}

.summary-list dd.positive {
  color: #0fb89b;
}

.line-items {
  list-style: none;
  margin: 0;
  padding: 12px 0;
  border-top: 1px solid rgba(15, 23, 42, 0.12);
  border-bottom: 1px solid rgba(15, 23, 42, 0.12);
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.line-items li {
  display: flex;
  justify-content: space-between;
  color: #0d3c33;
}

.summary-actions {
  display: flex;
  gap: 10px;
}

.summary-actions button {
  flex: 1;
  border-radius: 999px;
  border: none;
  padding: 10px 16px;
  cursor: pointer;
}

.summary-actions .ghost {
  border: 1px solid rgba(15, 23, 42, 0.2);
  background: transparent;
}

.summary-actions button:not(.ghost) {
  background: #0f172a;
  color: #fff;
}

.copy-feedback {
  margin: 0;
  font-size: 0.85rem;
  color: #0f172a;
}

@media (max-width: 960px) {
  .designer {
    grid-template-columns: 1fr;
  }
  .control-card.full {
    grid-column: 1;
  }
}
</style>
