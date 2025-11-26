<template>
  <section class="detail-card">
    <header class="section-head">
      <div>
        <p v-if="section.badge" class="eyebrow">{{ format(section.badge) }}</p>
        <h2>{{ section.title }}</h2>
        <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
      </div>
      <p v-if="section.props.status" class="status-pill" :class="section.props.statusVariant || 'info'">{{ format(section.props.status) }}</p>
    </header>

    <div v-if="fields.length" class="field-list">
      <div v-for="field in fields" :key="field.label" class="field-row">
        <div class="field-about">
          <p class="field-label">
            <i v-if="field.icon" :class="['bi', field.icon]"></i>
            <span>{{ format(field.label) }}</span>
            <span v-if="field.badge" class="chip" :class="field.badgeVariant || 'info'">{{ format(field.badge) }}</span>
          </p>
          <p v-if="field.hint" class="field-hint">{{ format(field.hint) }}</p>
        </div>
        <div class="field-value">
          <template v-if="Array.isArray(field.tags) && field.tags.length">
            <span v-for="tag in field.tags" :key="tag" class="tag">{{ format(tag) }}</span>
          </template>
          <template v-else-if="Array.isArray(field.list) && field.list.length">
            <ul class="value-list">
              <li v-for="item in field.list" :key="item.label || item.value || item">
                <strong v-if="item.label">{{ format(item.label) }}</strong>
                <span>{{ format(item.value || item) }}</span>
                <small v-if="item.meta">{{ format(item.meta) }}</small>
              </li>
            </ul>
          </template>
          <template v-else-if="Array.isArray(field.lines) && field.lines.length">
            <p v-for="(line, index) in field.lines" :key="`${field.label}-${index}`" class="value-line" :class="field.state">{{ format(line) }}</p>
          </template>
          <p v-else class="value-text" :class="field.state">{{ format(field.value) }}</p>
          <p v-if="field.meta" class="value-meta">{{ format(field.meta) }}</p>
          <div class="field-actions" v-if="fieldActions(field).length">
            <component
              v-for="action in fieldActions(field)"
              :key="action.label"
              :is="action.href ? 'a' : 'button'"
              class="field-action"
              :href="action.href ? format(action.href) : null"
              type="button"
            >
              <i v-if="action.icon" :class="['bi', action.icon]"></i>
              {{ format(action.label) }}
            </component>
          </div>
        </div>
      </div>
    </div>

    <p v-if="section.props.footer" class="foot">{{ format(section.props.footer) }}</p>

    <div v-if="actions.length" class="card-actions">
      <component
        v-for="action in actions"
        :key="action.label"
        :is="action.href ? 'a' : 'button'"
        class="btn"
        :class="['btn-' + (action.variant || 'secondary')]"
        :href="action.href ? format(action.href) : null"
        type="button"
      >
        <i v-if="action.icon" :class="['bi', action.icon, 'me-2']"></i>
        {{ format(action.label) }}
      </component>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { formatText } from '../../utils/contextFormatter'

const props = defineProps({ section: { type: Object, required: true }, context: { type: Object, default: () => ({}) } })
const format = text => formatText(text, props.context)

const fields = computed(() => (Array.isArray(props.section.props?.fields) ? props.section.props.fields : []))
const actions = computed(() => (Array.isArray(props.section.props?.actions) ? props.section.props.actions : []))

const fieldActions = field => {
  if (Array.isArray(field?.actions)) return field.actions
  if (field?.action) return [field.action]
  return []
}
</script>

<style scoped>
.detail-card { color: #0f172a; }
.section-head { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 16px; align-items: baseline; }
.section-head h2 { margin: 2px 0; font-size: 22px; }
.eyebrow { text-transform: uppercase; font-size: 12px; letter-spacing: .18em; color: #6366f1; margin: 0; }
.muted { color: #475467; margin: 0; max-width: 540px; }
.status-pill { border-radius: 999px; padding: 4px 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
.status-pill.info { background: rgba(59,130,246,.15); color: #1d4ed8; }
.status-pill.success { background: rgba(16,185,129,.18); color: #047857; }
.status-pill.warning { background: rgba(251,191,36,.2); color: #92400e; }
.status-pill.danger { background: rgba(248,113,113,.2); color: #b91c1c; }
.field-list { display: flex; flex-direction: column; border: 1px solid rgba(15,23,42,.08); border-radius: 20px; overflow: hidden; }
.field-row { display: grid; grid-template-columns: minmax(200px, 260px) 1fr; gap: 18px; padding: 18px 20px; background: #fff; }
.field-row + .field-row { border-top: 1px solid rgba(15,23,42,.08); }
.field-label { margin: 0; font-weight: 600; display: flex; gap: 8px; align-items: center; }
.field-label .bi { color: #475467; }
.field-hint { margin: 4px 0 0; color: #94a3b8; font-size: 13px; }
.field-value { display: flex; flex-direction: column; gap: 6px; }
.value-text { margin: 0; font-weight: 600; font-size: 16px; }
.value-text.success, .value-line.success { color: #047857; }
.value-text.warning, .value-line.warning { color: #b45309; }
.value-text.danger, .value-line.danger { color: #b91c1c; }
.value-text.info, .value-line.info { color: #1d4ed8; }
.value-line { margin: 0; }
.value-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
.value-list li { display: flex; justify-content: space-between; gap: 12px; font-size: 14px; color: #0f172a; }
.value-list strong { font-weight: 600; }
.value-list small { color: #94a3b8; }
.tag { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; background: #f1f5f9; font-size: 13px; font-weight: 500; }
.chip { border-radius: 999px; padding: 2px 8px; font-size: 11px; letter-spacing: .08em; text-transform: uppercase; }
.chip.info { background: rgba(59,130,246,.12); color: #1d4ed8; }
.chip.success { background: rgba(16,185,129,.18); color: #047857; }
.chip.warning { background: rgba(251,191,36,.2); color: #92400e; }
.chip.danger { background: rgba(248,113,113,.24); color: #b91c1c; }
.field-actions { display: flex; flex-wrap: wrap; gap: 8px; }
.field-action { align-self: flex-start; display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: #0f172a; text-decoration: none; border: 1px solid rgba(15,23,42,.12); border-radius: 999px; padding: 6px 12px; background: #fff; }
.field-action:hover { border-color: #4f46e5; color: #312e81; }
.value-meta { margin: 0; color: #94a3b8; font-size: 13px; }
.card-actions { margin-top: 18px; display: flex; flex-wrap: wrap; gap: 10px; }
.btn { border: none; border-radius: 999px; padding: 10px 18px; font-weight: 600; cursor: pointer; text-decoration: none; }
.btn-primary { background: #0ea5e9; color: #fff; }
.btn-secondary { background: #e0e7ff; color: #312e81; }
.btn-ghost { background: transparent; border: 1px solid rgba(15,23,42,.18); color: #0f172a; }
.foot { margin-top: 14px; font-size: 13px; color: #94a3b8; }

@media (max-width: 768px) {
  .field-row { grid-template-columns: 1fr; }
  .field-about { order: 1; }
  .field-value { order: 2; }
}
</style>
