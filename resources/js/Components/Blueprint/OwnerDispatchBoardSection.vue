<template>
  <section class="dispatch-board">
    <header class="board-header">
      <div>
        <p class="eyebrow">Live dispatch</p>
        <h2>Jobs for {{ readableDate }}</h2>
        <p class="muted">List and Kanban stay in sync with the filters below.</p>
      </div>
      <div class="board-header-actions">
        <span class="sync-pill"><i class="bi bi-clock-history me-1"></i>Synced {{ lastSyncedCopy }}</span>
        <button type="button" class="btn ghost" @click="refreshBoard">
          <i class="bi bi-arrow-repeat me-1"></i>
          Refresh
        </button>
      </div>
    </header>

    <div class="kpi-grid">
      <button type="button" class="kpi-card" @click="resetFilters">
        <p class="label">Jobs today</p>
        <p class="value">{{ stats.scheduled }} scheduled · {{ stats.completed }} completed</p>
        <p class="hint">Tap to clear filters.</p>
      </button>
      <button type="button" class="kpi-card" @click="filterUnassigned">
        <div class="kpi-flex">
          <div>
            <p class="label">Unassigned</p>
            <p class="value">{{ stats.unassigned }}</p>
          </div>
          <span class="pill">Action</span>
        </div>
        <p class="hint">Show only jobs needing a cleaner.</p>
      </button>
      <button type="button" class="kpi-card" @click="filterAtRisk">
        <p class="label">At risk</p>
        <p class="value">{{ stats.at_risk }}</p>
        <p class="hint">Window passed but not finished.</p>
      </button>
      <button type="button" class="kpi-card" @click="filterFailedPayments">
        <p class="label">Failed payments</p>
        <p class="value">{{ stats.failed_payments }}</p>
        <p class="hint">Follow up with finance.</p>
      </button>
    </div>

    <div class="filters-card">
      <div class="filters-grid">
        <label class="field">
          <span>Date</span>
          <input type="date" v-model="selectedDate" @change="handleDateChange" />
        </label>
        <label class="field">
          <span>Status</span>
          <select v-model="statusFilter" @change="clearSpecialFilter">
            <option value="all">All</option>
            <option value="scheduled">Scheduled</option>
            <option value="unassigned">Unassigned</option>
            <option value="en_route">Cleaner en route</option>
            <option value="on_site">On site</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed / No access</option>
          </select>
        </label>
        <label class="field">
          <span>Cleaner</span>
          <select v-model="cleanerFilter" @change="clearSpecialFilter">
            <option value="all">All cleaners</option>
            <option value="none">Unassigned</option>
            <option v-for="cleaner in cleanerOptions" :key="cleaner" :value="cleaner">
              {{ cleaner }}
            </option>
          </select>
        </label>
        <label class="field">
          <span>Route</span>
          <select v-model="routeFilter" @change="clearSpecialFilter">
            <option value="all">All routes</option>
            <option v-for="route in routeOptions" :key="route" :value="route">{{ route }}</option>
          </select>
        </label>
        <label class="field">
          <span>Search</span>
          <div class="search-input">
            <i class="bi bi-search"></i>
            <input type="search" v-model="searchTerm" placeholder="Customer or address" />
          </div>
        </label>
        <div class="view-toggle">
          <span class="view-label">View</span>
          <div class="tabs">
            <button type="button" :class="['tab', { active: viewMode === 'list' }]" @click="viewMode = 'list'">
              <i class="bi bi-list-ul me-1"></i>List
            </button>
            <button type="button" :class="['tab', { active: viewMode === 'kanban' }]" @click="viewMode = 'kanban'">
              <i class="bi bi-kanban me-1"></i>Kanban
            </button>
          </div>
        </div>
      </div>
      <p class="filters-hint">{{ filtersSummary }}</p>
    </div>

    <div class="view-pane" v-show="viewMode === 'list'">
      <div v-if="filteredJobs.length" class="table-responsive">
        <table class="jobs-table">
          <thead>
            <tr>
              <th>Time</th>
              <th>Status</th>
              <th>Customer</th>
              <th>Address</th>
              <th>Route</th>
              <th>Cleaner</th>
              <th>Type</th>
              <th>Price</th>
              <th>Payment</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="job in filteredJobs" :key="job.id">
              <td><strong>{{ job.window_start || '—' }}</strong> – {{ job.window_end || '—' }}</td>
              <td><span class="status-pill" :class="statusClass(job.board_status)">{{ job.status_label }}</span></td>
              <td>{{ job.customer }}</td>
              <td>{{ job.address }}</td>
              <td>{{ job.route?.name || '—' }}</td>
              <td>
                <span v-if="job.cleaner">{{ job.cleaner.name }}</span>
                <span v-else class="text-warning">Unassigned</span>
              </td>
              <td>{{ job.type }}</td>
              <td>{{ job.price }}</td>
              <td>
                <span class="payment-pill" :class="paymentClass(job.payment_status)">
                  <i v-if="job.payment_status === 'paid'" class="bi bi-credit-card"></i>
                  <i v-else-if="job.payment_status === 'failed'" class="bi bi-exclamation-triangle"></i>
                  <i v-else class="bi bi-hourglass-split"></i>
                  {{ job.payment_label }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="empty-state">No jobs match the current filters.</div>
    </div>

    <div class="view-pane" v-show="viewMode === 'kanban'">
      <div v-if="filteredJobs.length" class="kanban-board">
        <div v-for="column in kanbanColumns" :key="column.id" class="kanban-column">
          <header>
            <p class="title">{{ column.title }}</p>
            <span class="count">{{ jobsByColumn(column.id).length }}</span>
          </header>
          <div class="kanban-body">
            <article v-for="job in jobsByColumn(column.id)" :key="job.id" class="kanban-card">
              <p class="time">{{ job.window_start || '—' }} – {{ job.window_end || '—' }}</p>
              <p class="customer">{{ job.customer }}</p>
              <p class="meta">{{ job.address }}</p>
              <p class="meta">
                <span class="chip route">{{ job.route?.name || 'Route' }}</span>
                <span class="chip cleaner">{{ job.cleaner?.name || 'Unassigned' }}</span>
              </p>
            </article>
          </div>
        </div>
      </div>
      <div v-else class="empty-state">No jobs available for this view.</div>
    </div>

  </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { getContextValue } from '../../utils/contextFormatter'

const props = defineProps({
  section: { type: Object, required: true },
  context: { type: Object, default: () => ({}) },
})

const page = usePage()
const dataKey = computed(() => props.section?.props?.dataKey || 'owner.dispatch_board')
const boardData = computed(() => getContextValue(props.context, dataKey.value, {}) || {})

const jobs = computed(() => Array.isArray(boardData.value?.jobs) ? boardData.value.jobs : [])
const stats = computed(() => boardData.value?.stats || { scheduled: 0, completed: 0, unassigned: 0, at_risk: 0, failed_payments: 0 })

const selectedDate = ref(boardData.value?.date || todayIso())
const statusFilter = ref('all')
const cleanerFilter = ref('all')
const routeFilter = ref('all')
const searchTerm = ref('')
const viewMode = ref('list')
const specialFilter = ref(null)

watch(() => boardData.value?.date, value => {
  if (value && value !== selectedDate.value) {
    selectedDate.value = value
  }
})

const readableDate = computed(() => formatDate(selectedDate.value))
const lastSyncedCopy = computed(() => formatRelative(boardData.value?.last_synced_at))

const cleanerOptions = computed(() => {
  const names = new Set()
  jobs.value.forEach(job => {
    if (job?.cleaner?.name) {
      names.add(job.cleaner.name)
    }
  })
  return Array.from(names).sort()
})

const routeOptions = computed(() => {
  const routes = new Set()
  jobs.value.forEach(job => {
    if (job?.route?.name) {
      routes.add(job.route.name)
    }
  })
  return Array.from(routes).sort()
})

const filteredJobs = computed(() => {
  const search = searchTerm.value.trim().toLowerCase()
  return jobs.value.filter(job => {
    if (statusFilter.value !== 'all') {
      if (statusFilter.value === 'unassigned') {
        if (job.board_status !== 'unassigned') return false
      } else if (job.board_status !== statusFilter.value) {
        return false
      }
    }

    if (cleanerFilter.value !== 'all') {
      if (cleanerFilter.value === 'none' && job.cleaner) {
        return false
      }
      if (cleanerFilter.value !== 'none' && job.cleaner?.name !== cleanerFilter.value) {
        return false
      }
    }

    if (routeFilter.value !== 'all' && job.route?.name !== routeFilter.value) {
      return false
    }

    if (search && !(job.customer?.toLowerCase().includes(search) || job.address?.toLowerCase().includes(search))) {
      return false
    }

    if (specialFilter.value === 'atRisk' && !job.at_risk) {
      return false
    }

    if (specialFilter.value === 'failedPayments' && job.payment_status !== 'failed') {
      return false
    }

    return true
  })
})

const filtersSummary = computed(() => {
  const parts = []
  if (statusFilter.value !== 'all') parts.push(statusLabel(statusFilter.value))
  if (cleanerFilter.value === 'none') parts.push('Cleaner: Unassigned')
  if (cleanerFilter.value !== 'all' && cleanerFilter.value !== 'none') parts.push(`Cleaner: ${cleanerFilter.value}`)
  if (routeFilter.value !== 'all') parts.push(`Route: ${routeFilter.value}`)
  if (searchTerm.value.trim()) parts.push(`Search: “${searchTerm.value.trim()}”`)
  if (specialFilter.value === 'atRisk') parts.push('At risk only')
  if (specialFilter.value === 'failedPayments') parts.push('Failed payments only')
  return parts.length ? `Filters: ${parts.join(' · ')}` : 'Filters: all jobs'
})

const kanbanColumns = [
  { id: 'unassigned', title: 'Unassigned' },
  { id: 'scheduled', title: 'Scheduled' },
  { id: 'en_route', title: 'Cleaner en route' },
  { id: 'on_site', title: 'On site' },
  { id: 'completed', title: 'Completed' },
  { id: 'failed', title: 'Failed / no access' },
]

const jobsByColumn = status => filteredJobs.value.filter(job => job.board_status === status)

const statusClass = status => ({
  scheduled: 'scheduled',
  unassigned: 'unassigned',
  en_route: 'en-route',
  on_site: 'on-site',
  completed: 'completed',
  failed: 'failed',
}[status] || 'scheduled')

const paymentClass = status => ({
  paid: 'paid',
  failed: 'failed',
  pending: 'pending',
}[status] || 'pending')

function resetFilters() {
  statusFilter.value = 'all'
  cleanerFilter.value = 'all'
  routeFilter.value = 'all'
  searchTerm.value = ''
  specialFilter.value = null
}

function filterUnassigned() {
  statusFilter.value = 'unassigned'
  cleanerFilter.value = 'none'
  routeFilter.value = 'all'
  specialFilter.value = null
}

function filterAtRisk() {
  specialFilter.value = 'atRisk'
}

function filterFailedPayments() {
  specialFilter.value = 'failedPayments'
}

function clearSpecialFilter() {
  specialFilter.value = null
}

function handleDateChange() {
  const value = selectedDate.value || todayIso()
  const [path, search = ''] = page.url.split('?')
  const params = new URLSearchParams(search)
  params.set('date', value)
  const url = params.toString() ? `${path}?${params.toString()}` : path
  router.visit(url, { replace: true, preserveScroll: true, preserveState: false })
}

function refreshBoard() {
  router.visit(page.url, { replace: true, preserveScroll: true, preserveState: false })
}

function statusLabel(value) {
  return {
    scheduled: 'Scheduled',
    unassigned: 'Unassigned',
    en_route: 'Cleaner en route',
    on_site: 'On site',
    completed: 'Completed',
    failed: 'Failed / no access',
  }[value] || 'Status'
}

function todayIso() {
  return new Date().toISOString().slice(0, 10)
}

function formatDate(value) {
  if (!value) return 'today'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString(undefined, { weekday: 'long', month: 'short', day: 'numeric' })
}

function formatRelative(value) {
  if (!value) return 'just now'
  const timestamp = Date.parse(value)
  if (Number.isNaN(timestamp)) return 'just now'
  const diffSeconds = Math.max(0, Math.round((Date.now() - timestamp) / 1000))
  if (diffSeconds < 60) return diffSeconds === 0 ? 'just now' : `${diffSeconds}s ago`
  const minutes = Math.floor(diffSeconds / 60)
  if (minutes < 60) return `${minutes}m ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  return `${days}d ago`
}

</script>

<style scoped>
.dispatch-board { display: flex; flex-direction: column; gap: 20px; }
.board-header { display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.board-header h2 { margin: 0 0 6px; font-size: 1.6rem; }
.eyebrow { text-transform: uppercase; letter-spacing: 0.15em; color: #818cf8; font-size: 0.75rem; margin: 0 0 6px; }
.muted { margin: 0; color: #4b5563; }
.board-header-actions { display: flex; align-items: center; gap: 10px; }
.sync-pill { background: rgba(99,102,241,.1); color: #6366f1; border-radius: 999px; padding: 0.35rem 0.9rem; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; }
.btn { border: none; border-radius: 999px; font-weight: 600; padding: 0.5rem 1.1rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; }
.btn.ghost { background: #0f172a; color: #fff; }
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 12px; }
.kpi-card { text-align: left; border-radius: 18px; border: 1px solid rgba(15,23,42,.1); padding: 1rem; background: #fff; box-shadow: 0 10px 30px rgba(15,23,42,.08); cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 15px 35px rgba(15,23,42,.12); }
.kpi-card .label { text-transform: uppercase; letter-spacing: 0.12em; color: #6b7280; font-size: 0.75rem; margin: 0 0 4px; }
.kpi-card .value { margin: 0; font-size: 1.2rem; font-weight: 600; color: #0f172a; }
.kpi-card .hint { margin: 6px 0 0; color: #6b7280; font-size: 0.85rem; }
.kpi-flex { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.pill { border-radius: 999px; padding: 0.2rem 0.6rem; font-size: 0.7rem; text-transform: uppercase; background: #ecfdf5; color: #047857; font-weight: 600; }
.filters-card { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 1rem 1.2rem; background: #fff; box-shadow: inset 0 0 0 1px rgba(79,225,193,.05); }
.filters-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 12px; }
.field { display: flex; flex-direction: column; gap: 6px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; }
.field input, .field select { border-radius: 12px; border: 1px solid rgba(15,23,42,.12); padding: 0.45rem 0.75rem; font-size: 0.95rem; font-weight: 500; color: #0f172a; }
.search-input { display: flex; align-items: center; gap: 6px; border-radius: 12px; border: 1px solid rgba(15,23,42,.12); padding: 0 0.75rem; background: #fff; }
.search-input input { border: none; flex: 1; padding: 0.45rem 0; font-size: 0.95rem; }
.search-input input:focus { outline: none; }
.view-toggle { display: flex; align-items: center; gap: 10px; grid-column: span 1; min-width: 0; }
.view-toggle .view-label { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.12em; color: #6b7280; white-space: nowrap; }
.view-toggle .tabs { display: inline-flex; background: rgba(15,23,42,.05); border-radius: 999px; padding: 4px; max-width: 100%; }
.view-toggle .tab { border: none; background: transparent; color: #475467; padding: 0.5rem 1.2rem; border-radius: 999px; font-weight: 600; cursor: pointer; transition: background .15s ease, color .15s ease; display: inline-flex; align-items: center; white-space: nowrap; }
.view-toggle .tab.active { background: #0f172a; color: #fff; }
.filters-hint { margin: 0.8rem 0 0; color: #475467; font-size: 0.85rem; }
.view-pane { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 1rem; background: #fff; min-height: 320px; }
.table-responsive { overflow-x: auto; }
.jobs-table { width: 100%; border-collapse: collapse; }
.jobs-table th { text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: #6b7280; border-bottom: 1px solid rgba(15,23,42,.1); padding: 0.75rem; }
.jobs-table td { padding: 0.75rem; border-bottom: 1px solid rgba(15,23,42,.05); font-size: 0.92rem; }
.status-pill { border-radius: 999px; padding: 0.15rem 0.65rem; font-size: 0.78rem; font-weight: 600; }
.status-pill.scheduled { background: #eff6ff; color: #1d4ed8; }
.status-pill.unassigned { background: #fef2f2; color: #b91c1c; }
.status-pill.en-route { background: #ecfeff; color: #0369a1; }
.status-pill.on-site { background: #fef3c7; color: #92400e; }
.status-pill.completed { background: #ecfdf5; color: #15803d; }
.status-pill.failed { background: #fee2e2; color: #b91c1c; }
.payment-pill { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 0.2rem 0.6rem; font-size: 0.78rem; font-weight: 600; }
.payment-pill.paid { background: #e0f2fe; color: #0369a1; }
.payment-pill.pending { background: #fef3c7; color: #92400e; }
.payment-pill.failed { background: #fee2e2; color: #b91c1c; }
.empty-state { text-align: center; color: #6b7280; padding: 3rem 1rem; border: 1px dashed rgba(15,23,42,.1); border-radius: 16px; }
.kanban-board { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 0.5rem; }
.kanban-column { flex: 1; min-width: 230px; max-width: 280px; border: 1px solid rgba(15,23,42,.08); border-radius: 16px; background: #f9fafb; display: flex; flex-direction: column; max-height: 520px; }
.kanban-column header { border-bottom: 1px solid rgba(15,23,42,.08); padding: 0.7rem 0.9rem; display: flex; justify-content: space-between; }
.kanban-column .title { margin: 0; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.12em; color: #6b7280; }
.kanban-column .count { font-weight: 600; color: #4b5563; }
.kanban-body { padding: 0.8rem; overflow-y: auto; flex: 1; }
.kanban-card { border-radius: 14px; border: 1px solid rgba(15,23,42,.1); background: #fff; padding: 0.6rem 0.75rem; margin-bottom: 0.6rem; box-shadow: 0 8px 20px rgba(15,23,42,.08); font-size: 0.85rem; }
.kanban-card .time { font-weight: 600; margin: 0 0 4px; color: #0f172a; }
.kanban-card .customer { margin: 0; font-weight: 500; }
.kanban-card .meta { margin: 4px 0; color: #6b7280; font-size: 0.78rem; }
.kanban-card .chip { border-radius: 999px; padding: 0.1rem 0.45rem; font-size: 0.72rem; font-weight: 600; }
.kanban-card .chip.route { background: #eff6ff; color: #1d4ed8; margin-right: 0.35rem; }
.kanban-card .chip.cleaner { background: #ecfdf5; color: #15803d; }
.text-warning { color: #ca8a04; }

@media (max-width: 768px) {
  .board-header-actions { width: 100%; justify-content: space-between; }
  .view-toggle { flex-direction: column; align-items: stretch; }
  .view-toggle .tabs { width: 100%; }
  .view-toggle .tab { flex: 1; justify-content: center; }
}
</style>
