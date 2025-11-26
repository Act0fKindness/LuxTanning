<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <div class="panels">
      <article v-for="panel in section.props.panels" :key="panel.title" class="panel">
        <p class="title">{{ format(panel.title) }}</p>
        <p class="detail">{{ format(panel.description) }}</p>
        <ul v-if="panel.items?.length">
          <li v-for="item in panel.items" :key="item">{{ format(item) }}</li>
        </ul>
      </article>
    </div>
  </section>
</template>

<script setup>
import { formatText } from '../../utils/contextFormatter'
const props = defineProps({ section: { type: Object, required: true }, context: { type: Object, default: () => ({}) } })
const format = text => formatText(text, props.context)
</script>

<style scoped>
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.panels { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
.panel { border: 1px solid rgba(15,23,42,.08); border-radius: 16px; padding: 16px; background: #fdf4ff; }
.title { margin: 0 0 4px; font-weight: 600; }
.detail { margin: 0 0 8px; color: #475467; font-size: 14px; }
ul { list-style: disc; padding-left: 18px; margin: 0; color: #0f172a; font-size: 14px; }
</style>
