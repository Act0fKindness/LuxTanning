<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <div class="status-grid">
      <article v-for="item in items" :key="item.label" class="status-card">
        <p class="label">{{ format(item.label) }}</p>
        <p class="value" :class="item.state || 'info'">{{ format(item.value) }}</p>
        <p class="hint" v-if="item.hint">{{ format(item.hint) }}</p>
      </article>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { formatText, getContextValue } from '../../utils/contextFormatter'

const props = defineProps({ section: { type: Object, required: true }, context: { type: Object, default: () => ({}) } })
const format = text => formatText(text, props.context)

const items = computed(() => {
  if (props.section.props.itemsKey) {
    const resolved = getContextValue(props.context, props.section.props.itemsKey, [])
    return Array.isArray(resolved) ? resolved : []
  }
  return Array.isArray(props.section.props.items) ? props.section.props.items : []
})
</script>

<style scoped>
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
.status-card { border: 1px solid rgba(15,23,42,.08); border-radius: 16px; padding: 14px 16px; background: #fff; box-shadow: 0 10px 20px rgba(15,23,42,.05); }
.label { margin: 0 0 4px; font-weight: 600; }
.value { margin: 0; font-size: 24px; font-weight: 700; }
.value.info { color: #2563eb; }
.value.success { color: #059669; }
.value.warning { color: #d97706; }
.value.danger { color: #dc2626; }
.hint { margin: 4px 0 0; color: #475467; font-size: 13px; }
</style>
