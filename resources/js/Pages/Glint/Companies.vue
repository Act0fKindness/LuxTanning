<template>
  <WorkspaceLayout
    role="glint"
    :mode="'workspace'"
    title="Companies"
    :breadcrumbs="breadcrumbs"
    :nav="nav"
  >
    <div class="companies">
      <div v-if="flash.success" class="flash success">{{ flash.success }}</div>
      <section class="companies-list">
        <div class="list-header">
          <h2>Companies</h2>
          <input v-model="search" type="search" placeholder="Search companies" />
        </div>
        <div class="list-scroll">
          <button
            v-for="company in filteredCompanies"
            :key="company.id"
            class="company-tile"
            :class="{ active: normalizeId(company.id) === currentCompanyId }"
            type="button"
            @click="selectCompany(company.id)"
          >
            <div>
              <p class="name">{{ company.name }}</p>
              <small>{{ company.plan }}</small>
            </div>
          </button>
          <p v-if="!filteredCompanies.length" class="empty">No companies match that search.</p>
        </div>
      </section>

      <div class="companies-main">
        <section class="company-create">
          <header class="create-head">
            <div>
              <p class="eyebrow">Provisioning</p>
              <h2>Create company workspace</h2>
              <p class="hint">Mirror the public register flow but keep it in-platform.</p>
            </div>
            <button type="button" class="action-btn" @click="openCreateModal">
              <i class="bi bi-building-add"></i>
              New company
            </button>
          </header>
        </section>

        <section class="company-detail" v-if="selected">
        <div class="detail-intro">
          <div>
            <p class="eyebrow">Plan {{ selected.plan }}</p>
            <h1>{{ selected.name }}</h1>
            <p class="domain" v-if="selected.domain">{{ selected.domain }}</p>
          </div>
          <div class="stat-grid">
            <div class="stat-card">
              <span>Staff</span>
              <strong>{{ staff.length }}</strong>
            </div>
            <div class="stat-card">
              <span>Customers</span>
              <strong>{{ customers.length }}</strong>
            </div>
            <div class="stat-card">
              <span>Status</span>
              <strong>{{ selected.status }}</strong>
            </div>
            <div class="stat-card">
              <span>Plan</span>
              <strong>{{ selected.plan }}</strong>
            </div>
          </div>
        </div>

        <div class="tabs-bar">
          <div class="tabs">
            <button type="button" :class="['tab', { active: activeTab === 'team' }]" @click="activeTab = 'team'">Team</button>
            <button type="button" :class="['tab', { active: activeTab === 'customers' }]" @click="activeTab = 'customers'">Customers</button>
          </div>
          <div class="tab-actions">
            <button v-if="activeTab === 'team'" type="button" class="action-btn" @click="openStaffModal">
              <i class="bi bi-person-plus"></i>
              Add team member
            </button>
            <button v-if="activeTab === 'customers'" type="button" class="action-btn" @click="openCustomerModal">
              <i class="bi bi-person-plus"></i>
              Add customer
            </button>
          </div>
        </div>

        <div class="tab-panels">
          <section v-show="activeTab === 'team'" class="tab-panel">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Role</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="person in staff" :key="person.id">
                  <td>{{ person.name }}</td>
                  <td>{{ person.email || '—' }}</td>
                  <td>{{ person.phone || '—' }}</td>
                  <td><span class="role-pill">{{ person.role }}</span></td>
                </tr>
                <tr v-if="!staff.length">
                  <td colspan="4" class="empty">No staff yet.</td>
                </tr>
              </tbody>
            </table>
          </section>

          <section v-show="activeTab === 'customers'" class="tab-panel">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Address</th>
                  <th>Jobs</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in customers" :key="customer.id">
                  <td>{{ customer.displayName }}</td>
                  <td>{{ customer.displayEmail }}</td>
                  <td>{{ customer.phone || '—' }}</td>
                  <td>{{ customer.address || '—' }}</td>
                  <td class="jobs-cell">
                    <p><strong>{{ customer.jobs?.upcoming?.length || 0 }}</strong> upcoming</p>
                    <p><strong>{{ customer.jobs?.recent?.length || 0 }}</strong> recent</p>
                    <button type="button" class="mini" @click="openJobComposer(customer.id)">New job</button>
                  </td>
                </tr>
                <tr v-if="!customers.length">
                  <td colspan="5" class="empty">No customers recorded.</td>
                </tr>
              </tbody>
            </table>
          </section>
        </div>
        <section class="job-board" v-if="upcomingJobs.length || recentJobs.length">
          <div class="job-board__column" v-if="upcomingJobs.length">
            <div class="job-board__head">
              <h3>Upcoming jobs</h3>
              <button type="button" class="ghost" @click="openJobComposer()">Create job</button>
            </div>
            <article v-for="job in upcomingJobs" :key="job.id" class="job-card">
              <div class="job-card__time">
                <p class="job-date">{{ job.day_label || job.date }}</p>
                <p class="job-time">{{ job.eta_window || 'TBC' }}</p>
              </div>
              <div class="job-card__meta">
                <p class="job-customer">{{ job.customer?.name || 'Unassigned' }}</p>
                <p class="job-status" :class="job.status_badge">{{ job.status_label }}</p>
              </div>
              <div class="job-card__actions">
                <button type="button" class="mini" @click="openJobComposer(job.customer?.id || '', job)">Use details</button>
              </div>
            </article>
          </div>
          <div class="job-board__column" v-if="recentJobs.length">
            <div class="job-board__head">
              <h3>Recent jobs</h3>
            </div>
            <article v-for="job in recentJobs" :key="job.id" class="job-card">
              <div class="job-card__time">
                <p class="job-date">{{ job.day_label || job.date }}</p>
                <p class="job-time">{{ job.eta_window || 'TBC' }}</p>
              </div>
              <div class="job-card__meta">
                <p class="job-customer">{{ job.customer?.name || 'Unknown customer' }}</p>
                <p class="job-status" :class="job.status_badge">{{ job.status_label }}</p>
              </div>
              <div class="job-card__actions">
                <button type="button" class="mini" @click="openJobComposer(job.customer?.id || '', job)">Duplicate</button>
              </div>
            </article>
          </div>
        </section>
      </section>

      <section class="company-detail empty" v-else>
        <p>Select a company to view details.</p>
      </section>
      </div>
    </div>
  </WorkspaceLayout>

  <div v-if="showCreateModal" class="modal" @click.self="closeCreateModal">
    <div class="modal-card wide">
      <header class="modal-head">
        <div>
          <p class="eyebrow">Provisioning</p>
          <h3>New company workspace</h3>
        </div>
        <button type="button" class="icon-btn" @click="closeCreateModal"><i class="bi bi-x-lg"></i></button>
      </header>
      <form class="modal-body" @submit.prevent="submitCompany">
        <div class="grid two-col">
          <label>
            <span>Company name</span>
            <input v-model="createForm.name" type="text" required placeholder="e.g. North Star Cleaning" />
            <small v-if="createForm.errors.name" class="error">{{ createForm.errors.name }}</small>
          </label>
          <label>
            <span>Slug (optional)</span>
            <input v-model="createForm.slug" type="text" placeholder="auto-generated" />
            <small v-if="createForm.errors.slug" class="error">{{ createForm.errors.slug }}</small>
          </label>
        </div>
        <div class="grid two-col">
          <label>
            <span>Domain</span>
            <input v-model="createForm.domain" type="text" placeholder="tenant.glintlabs.com" />
            <small v-if="createForm.errors.domain" class="error">{{ createForm.errors.domain }}</small>
          </label>
          <label>
            <span>Plan</span>
            <select v-model="createForm.fee_tier">
              <option value="Starter">Starter</option>
              <option value="Growth">Growth</option>
              <option value="Scale">Scale</option>
              <option value="Custom">Custom</option>
            </select>
          </label>
        </div>
        <div class="grid two-col">
          <label>
            <span>Country</span>
            <input v-model="createForm.country" type="text" maxlength="2" placeholder="GB" />
          </label>
          <label>
            <span>Status</span>
            <select v-model="createForm.status">
              <option value="active">Active</option>
              <option value="paused">Paused</option>
              <option value="disabled">Disabled</option>
            </select>
          </label>
        </div>
        <ServiceAreaPicker v-model="serviceArea" />
        <div v-if="serviceAreaErrors.length" class="error-list">
          <p v-for="err in serviceAreaErrors" :key="err">{{ err }}</p>
        </div>
        <footer class="modal-actions">
          <button type="button" class="ghost" @click="closeCreateModal" :disabled="createForm.processing">Cancel</button>
          <button type="submit" :disabled="createForm.processing">
            {{ createForm.processing ? 'Creating…' : 'Create company' }}
          </button>
        </footer>
      </form>
    </div>
  </div>

  <div v-if="showStaffModal" class="modal" @click.self="closeStaffModal">
    <div class="modal-card">
      <header class="modal-head">
        <h3>Add team member</h3>
        <button type="button" class="icon-btn" @click="closeStaffModal"><i class="bi bi-x-lg"></i></button>
      </header>
      <form class="modal-body" @submit.prevent="submitStaff">
        <label>
          <span>Name</span>
          <input v-model="staffForm.name" type="text" required />
        </label>
        <label>
          <span>Email</span>
          <input v-model="staffForm.email" type="email" required />
        </label>
        <label>
          <span>Phone</span>
          <input v-model="staffForm.phone" type="text" placeholder="Optional" />
        </label>
        <label>
          <span>Role</span>
          <select v-model="staffForm.role">
            <option value="owner">Owner</option>
            <option value="manager">Manager</option>
            <option value="accountant">Accountant</option>
            <option value="cleaner">Cleaner</option>
          </select>
        </label>
        <small v-if="staffFirstError" class="error modal-error">{{ staffFirstError }}</small>
        <footer class="modal-actions">
          <button type="button" class="ghost" @click="closeStaffModal">Cancel</button>
          <button type="submit" :disabled="staffForm.processing">Save</button>
        </footer>
      </form>
    </div>
  </div>

  <div v-if="showCustomerModal" class="modal" @click.self="closeCustomerModal">
    <div class="modal-card wide">
      <header class="modal-head">
        <h3>Add customer</h3>
        <button type="button" class="icon-btn" @click="closeCustomerModal"><i class="bi bi-x-lg"></i></button>
      </header>
      <form class="modal-body" @submit.prevent="submitCustomer">
        <div class="grid two-col">
          <label>
            <span>Name</span>
            <input v-model="customerForm.name" type="text" required />
          </label>
          <label>
            <span>Email</span>
            <input v-model="customerForm.email" type="email" placeholder="Optional" />
          </label>
        </div>
        <div class="grid two-col">
          <label>
            <span>Phone</span>
            <input v-model="customerForm.phone" type="text" placeholder="Optional" />
          </label>
          <label>
            <span>SMS updates</span>
            <select v-model="customerForm.sms_opt_in">
              <option :value="true">Opt-in</option>
              <option :value="false">No texts</option>
            </select>
          </label>
        </div>
        <div class="grid two-col">
          <label>
            <span>Address line 1</span>
            <input v-model="customerForm.address_line1" type="text" placeholder="10 Downing Street" />
          </label>
          <label>
            <span>Address line 2</span>
            <input v-model="customerForm.address_line2" type="text" placeholder="Flat, building" />
          </label>
        </div>
        <div class="location-tool">
          <label>
            <span>Map / location search</span>
            <div class="location-row">
              <input v-model="locationQuery" type="text" placeholder="Start with postcode or street" />
              <button type="button" class="ghost" @click="clearLocation">Clear</button>
              <button type="button" @click="lookupAddress" :disabled="locationLoading">
                {{ locationLoading ? 'Searching…' : 'Search' }}
              </button>
            </div>
          </label>
          <p class="hint">Uses the same Mapbox-powered lookup as the customer signup flow.</p>
          <div v-if="mapPreviewUrl" class="map-preview">
            <img :src="mapPreviewUrl" alt="Location preview" />
          </div>
          <small v-if="locationError" class="error">{{ locationError }}</small>
        </div>
        <div class="grid two-col">
          <label>
            <span>Latitude</span>
            <input v-model="customerForm.lat" type="text" placeholder="51.5014" />
          </label>
          <label>
            <span>Longitude</span>
            <input v-model="customerForm.lng" type="text" placeholder="-0.1419" />
          </label>
        </div>
        <div class="grid two-col">
          <label>
            <span>Town / City</span>
            <input v-model="customerForm.city" type="text" />
          </label>
          <label>
            <span>Postcode</span>
            <input v-model="customerForm.postcode" type="text" />
          </label>
        </div>
        <div class="grid two-col">
          <label>
            <span>Property type</span>
            <select v-model="customerForm.property_type">
              <option value="House">House</option>
              <option value="Flat">Flat</option>
              <option value="Bungalow">Bungalow</option>
              <option value="Other">Other</option>
            </select>
          </label>
          <label>
            <span>Storeys</span>
            <select v-model="customerForm.storeys">
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3+">3+</option>
            </select>
          </label>
        </div>
        <div class="grid two-col">
          <label>
            <span>Frequency</span>
            <select v-model="customerForm.frequency">
              <option value="Every 4 weeks">Every 4 weeks</option>
              <option value="Every 8 weeks">Every 8 weeks</option>
              <option value="One-off">One-off</option>
            </select>
          </label>
          <label>
            <span>Access notes</span>
            <input v-model="customerForm.access_notes" type="text" placeholder="Gate code, pets, parking…" />
          </label>
        </div>
        <small v-if="customerFirstError" class="error modal-error">{{ customerFirstError }}</small>
        <footer class="modal-actions">
          <button type="button" class="ghost" @click="closeCustomerModal">Cancel</button>
      <button type="submit" :disabled="customerForm.processing">Save customer</button>
      </footer>
      </form>
    </div>
  </div>

  <JobComposerModal
    :show="showJobModal"
    :tenant="selected"
    :customers="customersRaw"
    :staff="staff"
    :recent-jobs="recentJobs"
    :default-customer-id="jobModalCustomerId"
    :prefill-job="jobModalPrefill"
    @close="showJobModal = false"
    @submitted="handleJobCreated"
  />
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import { roleNav } from '../../PageRegistry/nav'
import ServiceAreaPicker from '../../Components/ServiceAreaPicker.vue'
import JobComposerModal from '../../Components/JobComposerModal.vue'

const props = defineProps({
  companies: { type: Array, default: () => [] },
  selected: { type: Object, default: null },
  staff: { type: Array, default: () => [] },
  customers: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
})

const nav = roleNav.glint
const breadcrumbs = [
  { label: 'Platform control', href: '/glint/platform' },
  { label: 'Companies' },
]

const normalizeId = value => {
  if (value === undefined || value === null) return null
  const stringValue = String(value)
  return stringValue.length ? stringValue : null
}

const search = ref('')
const activeCompanyId = computed(() => normalizeId(props.filters?.company) || normalizeId(props.selected?.id))
const currentCompanyId = ref(activeCompanyId.value)

watch(activeCompanyId, value => {
  currentCompanyId.value = value
})
const selected = computed(() => props.selected || null)
const staff = computed(() => props.staff || [])
const customerData = ref(Array.isArray(props.customers) ? [...props.customers] : [])
watch(
  () => props.customers,
  value => {
    customerData.value = Array.isArray(value) ? [...value] : []
  },
  { immediate: true },
)
const customers = computed(() =>
  customerData.value.map(customer => {
    const addressLine = customer.address_line1?.trim() || ''
    const name = customer.name?.trim() || ''
    const email = customer.email?.trim() || ''
    const displayName = !name || (addressLine && name.toLowerCase() === addressLine.toLowerCase()) ? '[NO NAME]' : name
    const displayEmail = email || '[NO EMAIL]'

    return {
      ...customer,
      displayName,
      displayEmail,
      address: addressLine || '—',
    }
  }),
)
const customersRaw = computed(() => customerData.value)
const activeTab = ref('team')
const showStaffModal = ref(false)
const showCustomerModal = ref(false)
const showJobModal = ref(false)
const jobModalCustomerId = ref('')
const jobModalPrefill = ref(null)
const jobLists = computed(() => props.jobs || { upcoming: [], recent: [] })
const upcomingJobs = computed(() => jobLists.value.upcoming || [])
const recentJobs = computed(() => jobLists.value.recent || [])
const selectedTenantMeta = computed(() => props.selected || null)

const filteredCompanies = computed(() => {
  if (!search.value) return props.companies
  return props.companies.filter(company =>
    company.name.toLowerCase().includes(search.value.toLowerCase())
  )
})

function selectCompany(id) {
  const normalized = normalizeId(id)
  if (normalized === currentCompanyId.value) return
  currentCompanyId.value = normalized
  router.visit('/glint/companies', {
    data: { company: normalized },
    preserveScroll: true,
    preserveState: true,
    only: ['companies', 'selected', 'staff', 'customers', 'stats', 'filters'],
  })
}

const showCreateModal = ref(false)
const createForm = useForm({
  name: '',
  slug: '',
  domain: '',
  country: 'GB',
  fee_tier: 'Starter',
  status: 'active',
  vat_scheme: 'standard',
  service_area_label: '',
  service_area_place_id: '',
  service_area_lat: '',
  service_area_lng: '',
  service_area_radius_km: 15,
})

const serviceArea = ref({
  label: '',
  place_id: '',
  lat: null,
  lng: null,
  radius_km: 15,
})

watch(
  serviceArea,
  value => {
    createForm.service_area_label = value?.label || ''
    createForm.service_area_place_id = value?.place_id || ''
    createForm.service_area_lat = value?.lat || ''
    createForm.service_area_lng = value?.lng || ''
    createForm.service_area_radius_km = value?.radius_km || ''
  },
  { deep: true }
)

function resetServiceArea() {
  serviceArea.value = { label: '', place_id: '', lat: null, lng: null, radius_km: 15 }
}

function openCreateModal() {
  createForm.clearErrors()
  showCreateModal.value = true
}

function closeCreateModal() {
  if (createForm.processing) return
  showCreateModal.value = false
}

function submitCompany() {
  createForm.post('/glint/companies', {
    preserveScroll: true,
    onSuccess: () => {
      createForm.reset()
      resetServiceArea()
      showCreateModal.value = false
    },
  })
}

const serviceAreaErrors = computed(() => {
  const errors = []
  if (createForm.errors.service_area_label) errors.push(createForm.errors.service_area_label)
  if (createForm.errors.service_area_lat) errors.push(createForm.errors.service_area_lat)
  if (createForm.errors.service_area_lng) errors.push(createForm.errors.service_area_lng)
  if (createForm.errors.service_area_radius_km) errors.push(createForm.errors.service_area_radius_km)
  return errors
})

const staffForm = useForm({
  name: '',
  email: '',
  phone: '',
  role: 'manager',
})

const customerForm = useForm({
  name: '',
  email: '',
  phone: '',
  address_line1: '',
  address_line2: '',
  city: '',
  postcode: '',
  property_type: 'House',
  storeys: '1',
  frequency: 'Every 4 weeks',
  access_notes: '',
  sms_opt_in: false,
  lat: '',
  lng: '',
})
const mapboxToken = computed(() => usePage().props?.mapbox?.token || '')
const locationQuery = ref('')
const locationError = ref('')
const locationLoading = ref(false)
const mapPreviewUrl = computed(() => {
  const lat = customerForm.lat
  const lng = customerForm.lng
  if (!lat || !lng || !mapboxToken.value) return null
  const coords = `${lng},${lat}`
  return `https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/pin-s+0f172a(${coords})/${coords},15,0/600x300@2x?access_token=${mapboxToken.value}`
})

const staffFirstError = computed(() => Object.values(staffForm.errors)[0] || '')
const customerFirstError = computed(() => Object.values(customerForm.errors)[0] || '')

watch(selected, () => {
  staffForm.reset()
  customerForm.reset()
  staffForm.clearErrors()
  customerForm.clearErrors()
  activeTab.value = 'team'
  showCreateModal.value = false
  showStaffModal.value = false
  showCustomerModal.value = false
  showJobModal.value = false
})

function openStaffModal() {
  staffForm.clearErrors()
  showStaffModal.value = true
}

function closeStaffModal() {
  if (staffForm.processing) return
  showStaffModal.value = false
}

function openCustomerModal() {
  customerForm.clearErrors()
  locationError.value = ''
  showCustomerModal.value = true
}

function closeCustomerModal() {
  if (customerForm.processing) return
  showCustomerModal.value = false
}

async function lookupAddress() {
  locationError.value = ''
  if (!locationQuery.value) {
    locationError.value = 'Enter a postcode or street before searching.'
    return
  }
  if (!mapboxToken.value) {
    locationError.value = 'Mapbox token missing; ask an admin to configure maps.'
    return
  }
  locationLoading.value = true
  try {
    const resp = await fetch(
      `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(locationQuery.value)}.json?access_token=${mapboxToken.value}&autocomplete=true&limit=1`
    )
    if (!resp.ok) {
      throw new Error('Lookup failed')
    }
    const data = await resp.json()
    const feature = data?.features?.[0]
    if (!feature) {
      locationError.value = 'No results for that search.'
      return
    }
    const [lng, lat] = feature.center || []
    if (lat && lng) {
      customerForm.lat = lat.toFixed(6)
      customerForm.lng = lng.toFixed(6)
    }
    const context = feature.context || []
    const getContext = type => context.find(c => c.id.startsWith(type))?.text
    customerForm.address_line1 = feature.text || customerForm.address_line1
    customerForm.city = getContext('place') || getContext('locality') || customerForm.city
    customerForm.postcode = getContext('postcode') || customerForm.postcode
  } catch (error) {
    console.error(error)
    locationError.value = 'Address lookup failed. Try again.'
  } finally {
    locationLoading.value = false
  }
}

function clearLocation() {
  locationQuery.value = ''
  customerForm.lat = ''
  customerForm.lng = ''
  locationError.value = ''
}

function submitStaff() {
  if (!selected.value) return
  staffForm.post(`/glint/companies/${selected.value.id}/staff`, {
    preserveScroll: true,
    onSuccess: () => {
      showStaffModal.value = false
      staffForm.reset()
    },
  })
}

function submitCustomer() {
  if (!selected.value) return
  customerForm.post(`/glint/companies/${selected.value.id}/customers`, {
    preserveScroll: true,
    onSuccess: () => {
      showCustomerModal.value = false
      customerForm.reset()
    },
  })
}

const flash = computed(() => usePage().props.flash || {})

function openJobComposer(customerId = '', job = null) {
  jobModalCustomerId.value = customerId || ''
  jobModalPrefill.value = job || null
  showJobModal.value = true
}

function handleJobCreated() {
  showJobModal.value = false
  jobModalPrefill.value = null
  router.reload({ preserveScroll: true, only: ['customers', 'jobs', 'staff', 'selected'] })
}

watch(showJobModal, value => {
  if (!value) {
    jobModalPrefill.value = null
  }
})
</script>

<style scoped>
.companies {
  --brand-dark: #050b0a;
  --brand-panel: #0f1815;
  --brand-card: #111f1b;
  --brand-muted: rgba(255, 255, 255, 0.65);
  --brand-accent: #4fe1c1;
  display: grid;
  grid-template-columns: minmax(300px, 360px) minmax(0, 1fr);
  gap: 24px;
}

.companies-main {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.companies-list {
  background: linear-gradient(160deg, #0c1512, #050b09);
  border-radius: 28px;
  border: 1px solid rgba(79, 225, 193, 0.2);
  padding: 22px;
  color: #f8fafc;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 70px rgba(5, 11, 9, 0.55);
}

.list-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.list-header h2 {
  margin: 0;
  font-size: 1.15rem;
}

.list-header input {
  flex: 1;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  padding: 10px 14px;
  background: rgba(255, 255, 255, 0.05);
  color: #f8fafc;
}

.list-header input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.list-scroll {
  margin-top: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 72vh;
  overflow-y: auto;
  padding-right: 6px;
}

.company-tile {
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 16px;
  background: rgba(255, 255, 255, 0.02);
  text-align: left;
  display: flex;
  flex-direction: column;
  gap: 2px;
  color: #e2e8f0;
  transition: border-color 0.2s ease, transform 0.2s ease;
  margin-top: 5px;
}

.company-tile.active {
  border-color: var(--brand-accent);
  box-shadow: 0 0 0 2px rgba(79, 225, 193, 0.25);
  transform: translateY(-2px);
}

.company-tile .name {
  font-weight: 600;
  margin: 0;
  color: #fff;
}

.company-detail {
  background: linear-gradient(180deg, #ffffff, #f4fbf9);
  border-radius: 28px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  padding: 28px;
  box-shadow: 0 20px 60px rgba(5, 11, 9, 0.18);
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.company-detail.empty {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #475467;
}

.detail-intro {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-intro h1 {
  margin: 0;
  font-size: 1.8rem;
}

.domain {
  color: #475467;
  margin: 4px 0 0;
}

.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
}

.stat-card {
  background: rgba(15, 23, 42, 0.04);
  border-radius: 16px;
  padding: 12px 16px;
}

.stat-card span {
  display: block;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: #94a3b8;
}

.stat-card strong {
  display: block;
  font-size: 1.4rem;
  margin-top: 4px;
}

.tabs-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.tabs {
  display: inline-flex;
  background: rgba(15, 23, 42, 0.05);
  border-radius: 999px;
  padding: 4px;
}

.tab {
  border: none;
  background: transparent;
  color: #475467;
  padding: 8px 16px;
  border-radius: 999px;
  font-weight: 600;
  cursor: pointer;
}

.tab.active {
  background: #0f172a;
  color: #fff;
}

.tab-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  border: none;
  background: #0f172a;
  color: #fff;
  border-radius: 999px;
  padding: 10px 16px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.tab-panels {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 24px;
  background: #fff;
  padding: 20px;
}

.tab-panel {
  animation: fadeIn .2s ease;
}

.role-pill {
  display: inline-flex;
  padding: 4px 10px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.08);
  font-size: 0.78rem;
}

.error {
  color: #b42318;
}

.error-list {
  background: rgba(244, 63, 94, 0.06);
  border: 1px solid rgba(244, 63, 94, 0.2);
  border-radius: 14px;
  padding: 10px 12px;
  color: #b91c1c;
}

.error-list p {
  margin: 0;
  font-size: 12px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 10px 8px;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
  text-align: left;
}

.data-table th {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: #94a3b8;
}

.data-table .empty {
  text-align: center;
  color: #b0bcc8;
}

.tabs-bar .action-btn i,
.modal-head i {
  font-size: 1rem;
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(5, 10, 26, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  z-index: 90;
}

.modal-card {
  background: #fff;
  border-radius: 24px;
  width: min(480px, 100%);
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 30px 80px rgba(15, 23, 42, 0.35);
}

.modal-card.wide {
  width: min(720px, 100%);
}

.modal-head {
  padding: 24px;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-head h3 {
  margin: 0;
}

.icon-btn {
  border: none;
  background: transparent;
  font-size: 1rem;
  cursor: pointer;
}

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-body label {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.9rem;
  color: #475467;
}

.modal-body input,
.modal-body select,
.modal-body textarea {
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.15);
  padding: 10px 12px;
  font-size: 0.95rem;
}

.grid.two-col {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.location-tool {
  border: 1px dashed rgba(15, 23, 42, 0.12);
  border-radius: 16px;
  padding: 16px;
}

.location-row {
  display: flex;
  gap: 8px;
  margin-top: 6px;
}

.location-row input {
  flex: 1;
}

.location-row button {
  white-space: nowrap;
}

.hint {
  font-size: 0.78rem;
  color: #94a3b8;
  margin-top: 6px;
}

.map-preview {
  margin-top: 12px;
  border-radius: 18px;
  overflow: hidden;
  border: 1px solid rgba(15, 23, 42, 0.08);
}

.map-preview img {
  display: block;
  width: 100%;
  height: auto;
}

.job-board {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 16px;
  margin-top: 20px;
}

.job-board__column {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 18px;
  padding: 16px;
  background: #fff;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.job-board__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.job-card {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 14px;
  padding: 12px;
  display: grid;
  grid-template-columns: 110px 1fr auto;
  gap: 12px;
  align-items: center;
  background: rgba(249, 250, 251, 0.8);
}

.job-card__time {
  font-size: 13px;
  color: #475467;
}

.job-card__meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.job-customer {
  font-weight: 600;
  margin: 0;
}

.job-status {
  font-size: 13px;
  margin: 0;
  color: #475467;
}

.job-card__actions {
  display: flex;
  justify-content: flex-end;
}

.job-card button.mini,
.jobs-cell .mini {
  border: 1px solid rgba(15, 23, 42, 0.15);
  border-radius: 999px;
  background: transparent;
  padding: 6px 12px;
  font-size: 12px;
  cursor: pointer;
}

.jobs-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 8px;
  padding-bottom: 0;
  background: transparent;
}

.modal-actions button {
  border-radius: 999px;
  border: none;
  padding: 10px 18px;
  font-weight: 600;
  cursor: pointer;
}

.modal-actions button.ghost {
  background: rgba(15, 23, 42, 0.05);
  color: #475467;
}

.modal-actions button:not(.ghost) {
  background: #0f172a;
  color: #fff;
}

.modal-error {
  color: #b42318;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@media (max-width: 1024px) {
  .companies {
    grid-template-columns: 1fr;
  }

  .companies-list {
    order: 2;
  }

  .companies-main {
    order: 1;
  }
}

@media (max-width: 640px) {
  .companies-list,
  .company-detail {
    padding: 20px;
  }

  .stat-grid {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  }
}
</style>
.company-create {
  background: #ffffff;
  border-radius: 26px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.08);
  padding: 24px;
}

.company-create .create-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.company-create .create-head .hint {
  margin: 4px 0 0;
  color: #475467;
}

button.ghost {
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 999px;
  background: transparent;
  padding: 8px 16px;
  cursor: pointer;
}
