<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <ol class="timeline">
      <li v-for="event in events" :key="event.id || event.title" class="timeline-item">
        <div class="dot" :class="event.state || 'info'"></div>
        <div class="content">
          <div class="row">
            <p class="title">{{ format(event.title) }}</p>
            <span class="time">{{ format(event.time) }}</span>
          </div>
          <p class="detail">{{ format(event.detail) }}</p>
          <div class="meta" v-if="event.meta?.length">
            <span class="pill" v-for="meta in event.meta" :key="meta">{{ format(meta) }}</span>
          </div>
        </div>
      </li>
    </ol>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { formatText, getContextValue } from '../../utils/contextFormatter'

const props = defineProps({ section: { type: Object, required: true }, context: { type: Object, default: () => ({}) } })
const format = text => formatText(text, props.context)

const events = computed(() => {
  if (props.section.props.eventsKey) {
    const resolved = getContextValue(props.context, props.section.props.eventsKey, [])
    return Array.isArray(resolved) ? resolved : []
  }
  return Array.isArray(props.section.props.events) ? props.section.props.events : []
})
</script>

<style scoped>
section { color: #0f172a; }
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.timeline { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 18px; }
.timeline-item { display: flex; gap: 16px; }
.dot { width: 14px; height: 14px; border-radius: 50%; margin-top: 6px; }
.dot.info { background: #3b82f6; }
.dot.success { background: #10b981; }
.dot.warning { background: #f59e0b; }
.dot.danger { background: #ef4444; }
.content { flex: 1; border: 1px solid rgba(15,23,42,.08); border-radius: 14px; padding: 14px 16px; background: #fff; }
.row { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
.title { margin: 0; font-weight: 600; color: #0f172a; }
.time { font-size: 13px; color: #94a3b8; }
.detail { margin: 6px 0 0; color: #475467; }
.meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.pill { border-radius: 999px; background: #f1f5f9; padding: 2px 10px; font-size: 12px; color: #475467; }
</style>
