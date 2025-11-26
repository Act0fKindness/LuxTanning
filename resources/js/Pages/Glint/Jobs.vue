<template>
  <WorkspaceLayout role="glint" :title="pageTitle" :breadcrumbs="breadcrumbs" :nav="nav">
    <div class="jobs-page">
      <header class="jobs-page__head">
        <div>
          <p class="eyebrow">Tenant</p>
          <select v-model="selectedTenantId" @change="changeTenant">
            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">{{ tenant.name }}</option>
          </select>
        </div>
        <button type="button" class="btn primary" @click="openJobComposer()" :disabled="!currentTenant">Create job</button>
      </header>

      <section class="jobs-panels">
        <div class="panel" v-if="upcomingJobs.length">
          <div class="panel-head">
            <h3>Upcoming jobs</h3>
          </div>
          <div class="panel-body">
            <article v-for="job in upcomingJobs" :key="job.id" class="job-row">
              <div>
                <p class="job-date">{{ job.day_label || job.date }}</p>
                <p class="job-time">{{ job.eta_window || 'TBC' }}</p>
              </div>
              <div>
                <p class="job-customer">{{ job.customer?.name || 'Customer' }}</p>
                <p class="job-meta">{{ job.address?.line1 }}</p>
              </div>
              <div class="status" :class="job.status_badge">{{ job.status_label }}</div>
              <div>
                <button type="button" class="mini" @click="openJobComposer(job.customer?.id || '', job)">Duplicate</button>
              </div>
            </article>
          </div>
        </div>
        <div class="panel" v-if="recentJobs.length">
          <div class="panel-head">
            <h3>Recent jobs</h3>
          </div>
          <div class="panel-body">
            <article v-for="job in recentJobs" :key="`recent-${job.id}`" class="job-row">
              <div>
                <p class="job-date">{{ job.day_label || job.date }}</p>
                <p class="job-time">{{ job.eta_window || 'TBC' }}</p>
              </div>
              <div>
                <p class="job-customer">{{ job.customer?.name || 'Customer' }}</p>
                <p class="job-meta">{{ job.address?.line1 }}</p>
              </div>
              <div class="status" :class="job.status_badge">{{ job.status_label }}</div>
              <div>
                <button type="button" class="mini" @click="openJobComposer(job.customer?.id || '', job)">Use details</button>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section class="job-leads" v-if="jobLeads.length">
        <header class="job-leads__head">
          <div>
            <h3>Unassigned job leads</h3>
            <p class="muted">Leads created from bookings that are not yet linked to a customer.</p>
          </div>
          <a class="ghost" :href="`/glint/customers?tenant=${currentTenant?.id || ''}`">Open customers</a>
        </header>
        <article v-for="lead in jobLeads" :key="lead.id" class="lead-card">
          <div>
            <p class="lead-name">{{ lead.name || 'Job lead' }}</p>
            <p class="lead-meta">{{ lead.address_line1 || lead.postcode || 'Address pending' }}</p>
            <p class="lead-meta" v-if="lead.created_at">Last seen {{ lead.created_at }}</p>
          </div>
          <div class="lead-actions">
            <button type="button" class="ghost" @click="useLeadForJob(lead)">Use for job</button>
          </div>
        </article>
      </section>
    </div>
  </WorkspaceLayout>
  <JobComposerModal
    :show="showJobModal"
    :tenant="currentTenant"
    :customers="customers"
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
import { router } from '@inertiajs/vue3'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import { roleNav } from '../../PageRegistry/nav'
import JobComposerModal from '../../Components/JobComposerModal.vue'

const props = defineProps({
  tenants: { type: Array, default: () => [] },
  selectedTenant: { type: Object, default: null },
  jobs: { type: Object, default: () => ({}) },
  customers: { type: Array, default: () => [] },
  jobLeads: { type: Array, default: () => [] },
  staff: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const nav = roleNav.glint
const breadcrumbs = [
  { label: 'Platform control', href: '/glint/platform' },
  { label: 'Jobs' },
]

const selectedTenantId = ref(props.filters?.tenant || props.selectedTenant?.id || '')
const currentTenant = computed(() => props.selectedTenant || null)
const upcomingJobs = computed(() => props.jobs?.upcoming || [])
const recentJobs = computed(() => props.jobs?.recent || [])
const jobLeads = computed(() => props.jobLeads || [])
const pageTitle = computed(() => currentTenant.value ? `Jobs · ${currentTenant.value.name}` : 'Jobs')
const showJobModal = ref(false)
const jobModalCustomerId = ref('')
const jobModalPrefill = ref(null)

watch(
  () => props.selectedTenant,
  value => {
    if (value?.id) {
      selectedTenantId.value = value.id
    }
  },
)

function changeTenant() {
  router.visit('/glint/jobs', {
    data: { tenant: selectedTenantId.value },
    preserveScroll: true,
    preserveState: true,
    only: ['selectedTenant', 'jobs', 'customers', 'staff', 'filters'],
  })
}

function openJobComposer(customerId = '', job = null) {
  jobModalCustomerId.value = customerId || ''
  jobModalPrefill.value = job || null
  showJobModal.value = true
}

function useLeadForJob(lead) {
  jobModalCustomerId.value = ''
  jobModalPrefill.value = {
    customer: { name: lead.name, email: lead.email, phone: lead.phone },
    address: {
      line1: lead.address_line1 || '',
      line2: lead.addresses?.[0]?.line2 || '',
      city: lead.city || lead.addresses?.[0]?.city || '',
      postcode: lead.postcode || lead.addresses?.[0]?.postcode || '',
    },
  }
  showJobModal.value = true
}

function handleJobCreated() {
  showJobModal.value = false
  jobModalPrefill.value = null
  router.reload({ preserveScroll: true, only: ['jobs', 'customers'] })
}
</script>

<style scoped>
.jobs-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.jobs-page__head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.jobs-page__head select {
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  padding: 8px 12px;
  font-size: 14px;
}

.jobs-panels {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 18px;
}

.panel {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 18px;
  background: #fff;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.job-row {
  display: grid;
  grid-template-columns: 120px 1fr 120px 100px;
  gap: 12px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 12px;
  padding: 12px;
  align-items: center;
}

.job-leads {
  margin-top: 24px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 24px;
  background: #fff;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.job-leads__head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
}

.job-leads__head .ghost {
  border: 1px solid rgba(15, 23, 42, 0.15);
  border-radius: 999px;
  padding: 8px 16px;
  text-decoration: none;
  color: #0f172a;
}

.lead-card {
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 16px;
  padding: 16px;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.lead-name {
  margin: 0;
  font-weight: 600;
}

.lead-meta {
  margin: 0;
  color: #475467;
  font-size: 0.9rem;
}

.lead-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.lead-actions .ghost {
  border-radius: 999px;
  border: 1px solid rgba(15, 23, 42, 0.2);
  padding: 8px 16px;
  background: transparent;
  cursor: pointer;
}

.job-date {
  font-weight: 600;
  margin: 0;
}

.job-time,
.job-meta {
  color: #475467;
  margin: 0;
}

.job-customer {
  font-weight: 600;
  margin: 0;
}

.status {
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

button.mini {
  border: 1px solid rgba(15, 23, 42, 0.15);
  border-radius: 999px;
  background: transparent;
  padding: 6px 12px;
  font-size: 12px;
  cursor: pointer;
}

.btn.primary {
  border: none;
  border-radius: 999px;
  background: #0f172a;
  color: #fff;
  padding: 10px 20px;
  font-weight: 600;
  cursor: pointer;
}
</style>
