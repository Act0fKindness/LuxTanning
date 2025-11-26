<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <div class="code-card">
      <pre><code :class="`language-${language}`">{{ code }}</code></pre>
      <button type="button" class="copy-btn" @click="copyCode">
        <i class="bi" :class="copied ? 'bi-clipboard-check' : 'bi-clipboard'" aria-hidden="true"></i>
        <span>{{ copied ? 'Copied' : 'Copy snippet' }}</span>
      </button>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import { formatText } from '../../utils/contextFormatter'

const props = defineProps({ section: { type: Object, required: true }, context: { type: Object, default: () => ({}) } })
const format = text => formatText(text, props.context)
const code = computed(() => props.section.props?.code || '')
const language = computed(() => props.section.props?.language || 'html')
const copied = ref(false)

const copyCode = async () => {
  try {
    await navigator.clipboard?.writeText(code.value)
    copied.value = true
    setTimeout(() => (copied.value = false), 2000)
  } catch (error) {
    copied.value = false
  }
}
</script>

<style scoped>
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.code-card { position: relative; border-radius: 16px; border: 1px solid rgba(15,23,42,.08); padding: 16px; background: #0f172a; color: #e2e8f0; }
pre { margin: 0; overflow-x: auto; font-size: 13px; line-height: 1.5; }
code { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; white-space: pre-wrap; word-break: break-word; display: block; }
.copy-btn { position: absolute; top: 12px; right: 12px; background: rgba(15,23,42,.7); color: #e2e8f0; border: 1px solid rgba(226,232,240,.2); border-radius: 999px; padding: 6px 12px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
.copy-btn:hover { background: rgba(15,23,42,.9); }
</style>
