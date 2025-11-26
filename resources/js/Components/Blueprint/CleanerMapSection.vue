<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <CleanerJobMap
      v-if="job"
      :api-key="googleKey"
      :job="job"
      :current-location="currentLocation"
    />
    <div v-else class="empty">
      <p>No active job to navigate right now.</p>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import CleanerJobMap from '../CleanerJobMap.vue'
import { formatText, getContextValue } from '../../utils/contextFormatter'

const props = defineProps({
  section: { type: Object, required: true },
  context: { type: Object, default: () => ({}) },
  googleKey: { type: String, default: '' },
})

const format = text => formatText(text, props.context)

const job = computed(() => {
  if (!props.section.props.jobKey) {
    return null
  }
  const resolved = getContextValue(props.context, props.section.props.jobKey, null)
  return resolved || null
})

const currentLocation = computed(() => {
  if (!props.section.props.currentLocationKey) {
    return null
  }
  const resolved = getContextValue(props.context, props.section.props.currentLocationKey, null)
  return resolved || null
})
</script>

<style scoped>
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.empty { border: 1px dashed rgba(15,23,42,.2); border-radius: 16px; padding: 20px; color: #475467; background: #f8fafc; text-align: center; }
</style>
