<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <article v-if="job" class="next-card">
      <div class="info">
        <p class="eyebrow">Next stop</p>
        <h3>{{ format(job.address) }}</h3>
        <div class="chips">
          <span class="chip" v-if="jobWindow">Arrive window {{ jobWindow }}</span>
          <span class="chip" v-for="(meta, idx) in job.meta || []" :key="idx">{{ format(meta) }}</span>
        </div>
        <div class="status-wrap" v-if="job.status">
          <span class="status-pill" :class="job.status_badge || 'secondary'">{{ format(job.status) }}</span>
        </div>
      </div>
      <div class="actions">
        <template v-for="action in job.actions || []" :key="actionKey(action)">
          <component
            v-if="isLinkAction(action)"
            :is="isInternal(action.href) ? Link : 'a'"
            class="btn"
            :class="variantClass(action)"
            v-bind="actionProps(action)"
          >
            {{ format(action.label) }}
          </component>
          <button
            v-else
            type="button"
            class="btn"
            :disabled="pendingAction === action.status_action"
            :class="variantClass(action)"
            @click="performStatusAction(action)"
          >
            <span v-if="pendingAction === action.status_action" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            {{ format(action.label) }}
          </button>
        </template>
      </div>
    </article>
    <div v-else class="empty">
      <p>No active or upcoming jobs right now.</p>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { formatText, getContextValue } from '../../utils/contextFormatter'
import { updateCleanerJobStatus } from '../../utils/cleanerJobs'

const props = defineProps({ section: { type: Object, required: true }, context: { type: Object, default: () => ({}) } })
const format = text => formatText(text, props.context)
const page = usePage()
const pendingAction = ref(null)

const job = computed(() => {
  if (!props.section.props.jobKey) {
    return null
  }
  const resolved = getContextValue(props.context, props.section.props.jobKey, null)
  return resolved || null
})

const jobWindow = computed(() => job.value?.window || job.value?.eta_window || job.value?.start_time || '—')

const isInternal = href => typeof href === 'string' && href.startsWith('/')

const actionProps = action => {
  if (isInternal(action.href)) {
    return { href: action.href }
  }
  return { href: action.href, target: '_blank', rel: 'noopener' }
}

const actionKey = action => action.href || action.status_action || action.label
const isLinkAction = action => !!action.href
const variantClass = action => 'btn-' + (action.variant || 'secondary')

const performStatusAction = async action => {
  if (!job.value?.job_id || !action.status_action) return
  if (action.confirm && !window.confirm(action.confirm)) {
    return
  }
  pendingAction.value = action.status_action
  try {
    await updateCleanerJobStatus(job.value.job_id, action.status_action, page.props)
    router.reload({ only: ['context'], preserveScroll: true })
  } catch (error) {
    console.error('Failed to update job status', error)
    window.alert('Could not update the job. Please try again or contact support.')
  } finally {
    pendingAction.value = null
  }
}
</script>

<style scoped>
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.next-card { display: flex; flex-direction: column; gap: 16px; border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 20px; background: linear-gradient(135deg, #0f172a, #111c34); color: #fff; }
.info h3 { margin: 6px 0; font-size: 24px; }
.detail { margin: 0; opacity: .85; }
.eyebrow { text-transform: uppercase; letter-spacing: .2em; font-size: 12px; opacity: .8; margin: 0; }
.chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.chip { background: rgba(255,255,255,.12); border-radius: 999px; padding: 4px 12px; font-size: 13px; }
.status-wrap { margin-top: 10px; }
.status-pill { border-radius: 999px; padding: 4px 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; display: inline-flex; gap: 6px; align-items: center; }
.status-pill.secondary { background: rgba(148,163,184,.2); color: #e2e8f0; }
.status-pill.success { background: rgba(34,197,94,.25); color: #bbf7d0; }
.status-pill.info { background: rgba(59,130,246,.25); color: #bfdbfe; }
.status-pill.warning { background: rgba(251,191,36,.25); color: #fef3c7; }
.status-pill.danger { background: rgba(239,68,68,.25); color: #fecaca; }
.actions { display: flex; flex-wrap: wrap; gap: 10px; }
.btn { border-radius: 999px; padding: 10px 18px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #4fe1c1; color: #062f25; }
.btn-success { background: #22c55e; color: #052e16; }
.btn-danger { background: #ef4444; color: #fff; }
.btn-secondary { background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.2); }
.empty { border: 1px dashed rgba(15,23,42,.3); border-radius: 16px; padding: 20px; color: #475467; background: #f8fafc; text-align: center; }
</style>
