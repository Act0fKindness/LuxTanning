<template>
  <WorkspaceLayout role="glint" :mode="'workspace'" title="Customers" :breadcrumbs="breadcrumbs" :nav="nav">
    <div class="customers-page">
      <aside class="customers-list">
        <div class="list-header">
          <h2>Customers</h2>
          <input v-model="search" type="search" placeholder="Search by name, email or address" />
        </div>
        <div class="list-controls">
          <label class="tenant-select">
            <span>Company</span>
            <select v-model="tenantFilter" @change="changeTenant">
              <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">{{ tenant.name }}</option>
            </select>
          </label>
          <div class="stats">
            <span>{{ stats.customers }} customers</span>
            <span>{{ stats.leads }} leads</span>
          </div>
        </div>
        <div class="list-scroll">
          <template v-if="filteredCustomers.length">
            <p class="group-label">Customers</p>
            <button
              type="button"
              v-for="customer in filteredCustomers"
              :key="customer.id"
              :class="['customer-tile', { active: customer.id === selectedId }]"
              @click="selectCustomer(customer.id)"
            >
              <div class="tile-copy">
                <p class="name">{{ displayName(customer) }}</p>
                <small>{{ customer.address_line1 || customer.postcode || 'No address' }}</small>
              </div>
              <i class="bi bi-chevron-right"></i>
            </button>
          </template>
          <template v-if="filteredLeads.length">
            <p class="group-label">Job leads</p>
            <button
              type="button"
              v-for="lead in filteredLeads"
              :key="lead.id"
              :class="['customer-tile', 'lead', { active: lead.id === selectedId }]"
              @click="selectCustomer(lead.id)"
            >
              <div class="tile-copy">
                <p class="name">{{ lead.name || 'Job lead' }}</p>
                <small>{{ lead.address_line1 || lead.postcode || 'Address pending' }}</small>
              </div>
              <i class="bi bi-shuffle"></i>
            </button>
          </template>
          <p v-if="!filteredCustomers.length && !filteredLeads.length" class="empty">No matches.</p>
        </div>
      </aside>

      <section class="customers-detail" v-if="selected">
        <header class="detail-head">
          <div>
            <p class="eyebrow">{{ selected.is_lead ? 'Job lead' : 'Customer' }}</p>
            <h1>{{ selected.name || 'Unnamed' }}</h1>
            <p class="muted" v-if="selected.address_line1">{{ selected.address_line1 }} {{ selected.postcode }}</p>
          </div>
          <div class="detail-actions">
            <button type="button" class="ghost" @click="openJobModal(selected)">
              <i class="bi bi-briefcase"></i>
              Create job
            </button>
            <button type="button" class="ghost" @click="selectNextLead" v-if="selected.is_lead">
              Next lead
            </button>
          </div>
        </header>

        <div class="detail-grid">
          <article class="info-card edit-card" v-if="!selected.is_lead">
            <h4>Edit details</h4>
            <form @submit.prevent="submitEdit">
              <div class="form-grid">
                <label>
                  <span>Name</span>
                  <input v-model="editForm.name" type="text" placeholder="Customer name" />
                </label>
                <label>
                  <span>Email</span>
                  <input v-model="editForm.email" type="email" placeholder="customer@example.com" />
                </label>
                <label>
                  <span>Phone</span>
                  <input v-model="editForm.phone" type="text" placeholder="+44" />
                </label>
              </div>
              <div class="form-grid">
                <label>
                  <span>Address line 1</span>
                  <input v-model="editForm.address_line1" type="text" placeholder="10 Downing St" />
                </label>
                <label>
                  <span>Address line 2</span>
                  <input v-model="editForm.address_line2" type="text" placeholder="Flat, Building" />
                </label>
                <label>
                  <span>City</span>
                  <input v-model="editForm.city" type="text" placeholder="London" />
                </label>
                <label>
                  <span>Postcode</span>
                  <input v-model="editForm.postcode" type="text" placeholder="SW1A 1AA" />
                </label>
              </div>
              <div class="merge-select" v-if="!selectedHasAddress && placeholderOptions.length">
                <label>
                  <span>Merge with</span>
                  <select v-model="editForm.merge_target_id">
                    <option value="">Select [NO NAME] record</option>
                    <option v-for="option in placeholderOptions" :key="option.id" :value="option.id">
                      {{ option.address_line1 || 'Unnamed address' }} {{ option.postcode || '' }}
                    </option>
                  </select>
                </label>
              </div>
              <small class="error" v-if="editForm.hasErrors">{{ editForm.errors.name || editForm.errors.email || editForm.errors.address_line1 || editForm.errors.postcode }}</small>
              <div class="form-actions">
                <button type="button" class="ghost" @click="resetEditForm" :disabled="editForm.processing">Reset</button>
                <button type="submit" :disabled="editForm.processing">Save changes</button>
              </div>
            </form>
          </article>

          <article class="info-card">
            <h4>Contact</h4>
            <dl>
              <dt>Email</dt>
              <dd>{{ selected.email || '—' }}</dd>
              <dt>Phone</dt>
              <dd>{{ selected.phone || '—' }}</dd>
              <dt>Joined</dt>
              <dd>{{ selected.created_at || '—' }}</dd>
            </dl>
          </article>

          <article class="info-card">
            <h4>Addresses</h4>
            <ul>
              <li v-for="address in selected.addresses || []" :key="address.id || address.line1">
                <strong>{{ address.line1 }}</strong>
                <small>{{ [address.line2, address.city, address.postcode].filter(Boolean).join(', ') }}</small>
              </li>
              <li v-if="!(selected.addresses || []).length">No saved addresses.</li>
            </ul>
          </article>

          <CustomerQuotePanel :customer="selected" />
        </div>

        <section class="jobs-board">
          <div class="jobs-column" v-if="selected.jobs?.upcoming?.length">
            <div class="jobs-head">
              <h4>Upcoming</h4>
            </div>
            <article v-for="job in selected.jobs.upcoming" :key="job.id" class="job-pill">
              <div>
                <p class="job-date">{{ job.day_label || job.date }}</p>
                <p class="job-time">{{ job.eta_window || 'TBC' }}</p>
              </div>
              <span class="status" :class="job.status_badge">{{ job.status_label }}</span>
            </article>
          </div>
          <div class="jobs-column" v-if="selected.jobs?.recent?.length">
            <div class="jobs-head">
              <h4>Recent</h4>
            </div>
            <article v-for="job in selected.jobs.recent" :key="job.id" class="job-pill">
              <div>
                <p class="job-date">{{ job.day_label || job.date }}</p>
                <p class="job-time">{{ job.eta_window || 'TBC' }}</p>
              </div>
              <span class="status" :class="job.status_badge">{{ job.status_label }}</span>
            </article>
          </div>
        </section>

        <section class="lead-ops" v-if="selected.is_lead">
          <article>
            <h4>Create customer profile</h4>
            <form @submit.prevent="submitConvert">
              <input type="hidden" v-model="convertForm.job_id" />
              <label>
                <span>Name</span>
                <input v-model="convertForm.name" type="text" required />
              </label>
              <label>
                <span>Email</span>
                <input v-model="convertForm.email" type="email" placeholder="customer@example.com" />
              </label>
              <label>
                <span>Phone</span>
                <input v-model="convertForm.phone" type="text" placeholder="+44" />
              </label>
              <small class="error" v-if="convertForm.errors.name || convertForm.errors.email || convertForm.errors.phone">
                {{ convertForm.errors.name || convertForm.errors.email || convertForm.errors.phone }}
              </small>
              <button type="submit" :disabled="convertForm.processing">Create customer</button>
            </form>
          </article>
          <article>
            <h4>Merge into existing</h4>
            <form @submit.prevent="submitMerge">
              <input type="hidden" v-model="mergeForm.job_id" />
              <label>
                <span>Existing customer</span>
                <select v-model="mergeForm.customer_id" required>
                  <option value="" disabled>Select customer</option>
                  <option v-for="customer in realCustomers" :key="customer.id" :value="customer.id">{{ customer.name || customer.email }}</option>
                </select>
              </label>
              <small class="error" v-if="mergeForm.errors.customer_id">{{ mergeForm.errors.customer_id }}</small>
              <button type="submit" :disabled="mergeForm.processing || !realCustomers.length">Attach job</button>
            </form>
          </article>
        </section>
      </section>

      <section class="customers-empty" v-else>
        <p>Select a customer or job lead to view details.</p>
      </section>
    </div>
  </WorkspaceLayout>

  <JobComposerModal
    :show="showJobModal"
    :tenant="selectedTenant"
    :customers="customersForModal"
    :staff="staff"
    :recent-jobs="jobs.recent || []"
    :default-customer-id="jobModalCustomerId"
    :prefill-job="jobModalPrefill"
    @close="showJobModal = false"
    @submitted="handleJobCreated"
  />

  <div v-if="showMergeModal && pendingMergeTarget" class="modal-overlay">
    <div class="modal-card">
      <h3>Merge with existing record?</h3>
      <p>
        We found another customer with the same address but no name. Merge details into the existing
        record so their jobs stay linked?
      </p>
      <p class="modal-target">Address: {{ pendingMergeTarget.address_line1 }} {{ pendingMergeTarget.postcode }}</p>
      <div class="modal-actions">
        <button type="button" class="ghost" @click="cancelMerge">Cancel</button>
        <button type="button" @click="confirmMerge" :disabled="editForm.processing">Merge and replace</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import { roleNav } from '../../PageRegistry/nav'
import JobComposerModal from '../../Components/JobComposerModal.vue'
import CustomerQuotePanel from '../../Components/CustomerQuotePanel.vue'
import { resolveCsrfToken } from '../../utils/csrf'

const props = defineProps({
  tenants: { type: Array, default: () => [] },
  selectedTenant: { type: Object, default: null },
  customers: { type: Array, default: () => [] },
  jobs: { type: Object, default: () => ({}) },
  staff: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({ customers: 0, leads: 0 }) },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const nav = roleNav.glint
const breadcrumbs = [
  { label: 'Platform control', href: '/glint/platform' },
  { label: 'Customers' },
]

const csrfToken = computed(() => page.props?.csrfToken || resolveCsrfToken())
const tenants = computed(() => props.tenants || [])
const selectedTenant = computed(() => props.selectedTenant || null)
const jobs = computed(() => props.jobs || { upcoming: [], recent: [] })
const customerRows = ref(Array.isArray(props.customers) ? [...props.customers] : [])
watch(
  () => props.customers,
  value => {
    customerRows.value = Array.isArray(value) ? [...value] : []
  },
  { immediate: true },
)
const realCustomers = computed(() => customerRows.value.filter(customer => !customer.is_lead))
const placeholderOptions = computed(() =>
  realCustomers.value.filter(customer => looksLikePlaceholder(customer) && customer.id !== selectedId.value)
)
const leadCustomers = computed(() => customerRows.value.filter(customer => customer.is_lead))
const staff = computed(() => props.staff || [])

const stats = computed(() => ({
  customers: realCustomers.value.length,
  leads: leadCustomers.value.length,
}))

const tenantFilter = ref(props.filters?.tenant || props.selectedTenant?.id || '')
const search = ref('')
const selectedId = ref(props.filters?.customer || null)
const editForm = useForm({
  name: '',
  email: '',
  phone: '',
  address_line1: '',
  address_line2: '',
  city: '',
  postcode: '',
  merge_target_id: '',
}).transform(data => ({
  ...data,
  _token: csrfToken.value,
}))
const showMergeModal = ref(false)
const pendingMergeTarget = ref(null)

watch(
  customerRows,
  rows => {
    if (!rows.length) {
      selectedId.value = null
      return
    }
    if (!selectedId.value || !rows.find(row => row.id === selectedId.value)) {
      selectedId.value = rows[0].id
    }
  },
  { immediate: true },
)

watch(
  () => props.filters?.customer,
  value => {
    if (value) {
      selectedId.value = value
    }
  },
)

watch(
  () => props.filters?.tenant,
  value => {
    if (value) {
      tenantFilter.value = value
    }
  },
)

const filteredCustomers = computed(() => filterRows(realCustomers.value))
const filteredLeads = computed(() => filterRows(leadCustomers.value))

function filterRows(rows) {
  const term = search.value.trim().toLowerCase()
  if (!term) return rows
  return rows.filter(row => {
    return [displayName(row), row.email, row.address_line1, row.city, row.postcode]
      .filter(Boolean)
      .some(value => String(value).toLowerCase().includes(term))
  })
}

const selected = computed(() => customerRows.value.find(customer => customer.id === selectedId.value) || null)

function selectCustomer(id) {
  selectedId.value = id
}

function displayName(customer) {
  if (!customer) return 'Customer'
  const name = (customer.name || '').trim()
  const addr = (customer.address_line1 || '').trim()
  if (!name || (addr && name.toLowerCase() === addr.toLowerCase())) {
    return addr || '[NO NAME]'
  }
  return name
}

function selectNextLead() {
  if (!leadCustomers.value.length) return
  const currentIndex = leadCustomers.value.findIndex(lead => lead.id === selectedId.value)
  const nextLead = leadCustomers.value[(currentIndex + 1) % leadCustomers.value.length]
  if (nextLead) {
    selectedId.value = nextLead.id
  }
}

const convertForm = useForm({ job_id: '', name: '', email: '', phone: '' }).transform(data => ({
  ...data,
  _token: csrfToken.value,
}))
const mergeForm = useForm({ job_id: '', customer_id: '' }).transform(data => ({
  ...data,
  _token: csrfToken.value,
}))

watch(
  selected,
  customer => {
    if (customer?.is_lead) {
      convertForm.name = customer.name || ''
      convertForm.email = customer.email || ''
      convertForm.phone = customer.phone || ''
      convertForm.job_id = customer.source_job_id || ''
      mergeForm.job_id = customer.source_job_id || ''
      if (!mergeForm.customer_id && realCustomers.value.length) {
        mergeForm.customer_id = realCustomers.value[0].id
      }
    } else {
      convertForm.reset()
      mergeForm.reset()
    }
  },
  { immediate: true },
)

function submitConvert() {
  if (!selected.value?.is_lead || !tenantFilter.value) return
  convertForm.job_id = selected.value.source_job_id || ''
  convertForm.post(`/glint/tenants/${tenantFilter.value}/customers/from-job`, {
    preserveScroll: true,
    onSuccess: () => convertForm.reset(),
  })
}

function submitMerge() {
  if (!selected.value?.is_lead || !tenantFilter.value) return
  mergeForm.job_id = selected.value.source_job_id || ''
  mergeForm.post(`/glint/tenants/${tenantFilter.value}/customers/merge-job`, {
    preserveScroll: true,
  })
}

function changeTenant() {
  router.visit('/glint/customers', {
    data: { tenant: tenantFilter.value },
    preserveScroll: true,
    preserveState: false,
  })
}

const normalized = value => (value || '').toString().trim().toLowerCase()

const selectedHasAddress = computed(() => {
  const customer = selected.value
  if (!customer) return false
  if (customer.address_line1) return true
  return (customer.addresses || []).some(addr => addr.line1)
})

function hydrateEditForm(customer) {
  if (!customer) {
    editForm.reset()
    return
  }
  editForm.name = customer.name || ''
  editForm.email = customer.email || ''
  editForm.phone = customer.phone || ''
  const primaryAddress = customer.addresses?.[0] || {}
  editForm.address_line1 = primaryAddress.line1 || customer.address_line1 || ''
  editForm.address_line2 = primaryAddress.line2 || ''
  editForm.city = primaryAddress.city || customer.city || ''
  editForm.postcode = primaryAddress.postcode || customer.postcode || ''
  editForm.merge_target_id = ''
}

watch(
  selected,
  customer => {
    hydrateEditForm(customer)
  },
  { immediate: true },
)

function resetEditForm() {
  hydrateEditForm(selected.value)
}

function looksLikePlaceholder(customer) {
  if (!customer) return false
  const name = normalized(customer.name)
  const addressLine = normalized(customer.address_line1)
  return name !== '' && name === addressLine
}

function findMergeTarget() {
  const line = normalized(editForm.address_line1)
  if (!line) return null
  const code = normalized(editForm.postcode)
  return realCustomers.value.find(customer => {
    if (customer.id === selectedId.value) return false
    if (!looksLikePlaceholder(customer)) return false
    if (normalized(customer.address_line1) !== line) return false
    if (code && normalized(customer.postcode) !== code) return false
    return true
  })
}

function submitEdit() {
  if (!selected.value || !tenantFilter.value) return
  const candidate = findMergeTarget()
  if (candidate) {
    pendingMergeTarget.value = candidate
    showMergeModal.value = true
    return
  }
  performCustomerUpdate()
}

function performCustomerUpdate() {
  if (!selected.value || !tenantFilter.value) return
  editForm.patch(`/glint/tenants/${tenantFilter.value}/customers/${selected.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showMergeModal.value = false
      pendingMergeTarget.value = null
      editForm.merge_target_id = ''
    },
  })
}

function confirmMerge() {
  if (!pendingMergeTarget.value) return
  editForm.merge_target_id = pendingMergeTarget.value.id
  performCustomerUpdate()
}

function cancelMerge() {
  pendingMergeTarget.value = null
  editForm.merge_target_id = ''
  showMergeModal.value = false
}

const showJobModal = ref(false)
const jobModalCustomerId = ref('')
const jobModalPrefill = ref(null)

const customersForModal = computed(() => realCustomers.value)

function openJobModal(record = null) {
  jobModalCustomerId.value = record && !record.is_lead ? record.id || '' : ''
  if (record) {
    jobModalPrefill.value = {
      customer: record.is_lead ? { name: record.name, email: record.email, phone: record.phone } : null,
      address: {
        line1: record.address_line1 || record.addresses?.[0]?.line1 || '',
        line2: record.addresses?.[0]?.line2 || '',
        city: record.city || record.addresses?.[0]?.city || '',
        postcode: record.postcode || record.addresses?.[0]?.postcode || '',
      },
    }
  } else {
    jobModalPrefill.value = null
  }
  showJobModal.value = true
}

function handleJobCreated() {
  showJobModal.value = false
  jobModalPrefill.value = null
  router.reload({ preserveScroll: true, only: ['customers', 'jobs'] })
}
</script>

<style scoped>
.customers-page {
  display: grid;
  grid-template-columns: minmax(300px, 360px) minmax(0, 1fr);
  gap: 24px;
}

.customers-list {
  background: linear-gradient(160deg, #0c1512, #050b09);
  border-radius: 28px;
  border: 1px solid rgba(79, 225, 193, 0.2);
  padding: 22px;
  display: flex;
  flex-direction: column;
  color: #f8fafc;
  box-shadow: 0 25px 70px rgba(5, 11, 9, 0.55);
}

.list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.list-header h2 {
  margin: 0;
  font-size: 1.2rem;
}

.list-header input {
  flex: 1;
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  padding: 10px 14px;
  background: rgba(255, 255, 255, 0.05);
  color: #f8fafc;
}

.list-header input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.list-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 14px;
  flex-wrap: wrap;
}

.tenant-select {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.75);
}

.tenant-select select {
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  padding: 10px 14px;
  background: rgba(255, 255, 255, 0.05);
  color: #f8fafc;
}


.stats {
  display: flex;
  flex-direction: column;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
  text-align: right;
}

.list-scroll {
  margin-top: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 70vh;
  overflow-y: auto;
  padding-right: 6px;
}

.group-label {
  margin: 10px 0 0;
  font-size: 0.75rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.55);
}

.customer-tile {
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 14px;
  background: rgba(255, 255, 255, 0.02);
  color: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  text-align: left;
  gap: 12px;
  transition: border-color 0.2s ease, transform 0.2s ease;
}

.customer-tile .name {
  margin: 0;
  font-weight: 600;
  color: #fff;
}

.customer-tile small {
  color: rgba(255, 255, 255, 0.7);
}

.customer-tile.active {
  border-color: var(--brand-accent, #4fe1c1);
  box-shadow: 0 0 0 2px rgba(79, 225, 193, 0.25);
  transform: translateY(-1px);
}

.customer-tile.lead {
  border-color: rgba(79, 225, 193, 0.55);
  background: rgba(79, 225, 193, 0.08);
}

.tile-copy {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.empty {
  color: rgba(255, 255, 255, 0.6);
  text-align: center;
  margin-top: 10px;
}

.customers-detail {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 28px;
  background: #fff;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.detail-head {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 16px;
  align-items: flex-start;
}

.detail-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.detail-actions .ghost {
  border: 1px solid rgba(15, 23, 42, 0.15);
  border-radius: 999px;
  padding: 8px 16px;
  background: transparent;
  cursor: pointer;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}

.info-card {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 18px;
  padding: 16px;
}

.edit-card form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.form-grid label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.85rem;
  color: #475467;
}

.form-grid input {
  border-radius: 10px;
  border: 1px solid rgba(15, 23, 42, 0.15);
  padding: 8px 10px;
  font-size: 0.9rem;
}

.merge-select {
  margin-top: 4px;
}

.merge-select label {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.85rem;
  color: #475467;
}

.merge-select select {
  border-radius: 10px;
  border: 1px solid rgba(15, 23, 42, 0.15);
  padding: 8px 10px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.form-actions button {
  border-radius: 999px;
  border: none;
  padding: 8px 16px;
  font-weight: 600;
  cursor: pointer;
}

.form-actions button:not(.ghost) {
  background: #0f172a;
  color: #fff;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 20px;
}

.modal-card {
  background: #fff;
  border-radius: 20px;
  padding: 24px;
  max-width: 480px;
  width: 100%;
  box-shadow: 0 25px 70px rgba(15, 23, 42, 0.2);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.modal-target {
  font-weight: 600;
  color: #0f172a;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.info-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.info-card dl {
  margin: 0;
  display: grid;
  grid-template-columns: auto 1fr;
  row-gap: 6px;
  column-gap: 12px;
}

.jobs-board {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.jobs-column {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 18px;
  padding: 16px;
}

.job-pill {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 14px;
  padding: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
}

.job-date {
  margin: 0;
  font-weight: 600;
}

.job-time {
  margin: 0;
  color: #475467;
}

.lead-ops {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
}

.lead-ops article {
  border: 1px solid rgba(249, 115, 22, 0.2);
  border-radius: 18px;
  padding: 16px;
}

.lead-ops form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.lead-ops input,
.lead-ops select {
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  padding: 8px 10px;
}

.lead-ops button {
  border-radius: 999px;
  border: none;
  padding: 10px 16px;
  background: #0f172a;
  color: #fff;
  cursor: pointer;
}

.error {
  color: #b42318;
  font-size: 0.85rem;
}

.customers-empty {
  border: 1px dashed rgba(15, 23, 42, 0.2);
  border-radius: 24px;
  padding: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #475467;
}

@media (max-width: 900px) {
  .customers-page {
    grid-template-columns: 1fr;
  }
}
</style>
