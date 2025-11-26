<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <div class="checklist">
      <label v-for="item in section.props.items" :key="item.label" class="row">
        <input type="checkbox" checked disabled />
        <div>
          <p class="label">{{ format(item.label) }}</p>
          <p class="detail">{{ format(item.detail) }}</p>
        </div>
      </label>
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
.checklist { display: flex; flex-direction: column; gap: 10px; }
.row { display: flex; gap: 10px; border: 1px solid rgba(15,23,42,.08); border-radius: 14px; padding: 12px; background: #fff; }
input { accent-color: #4f46e5; }
.label { margin: 0; font-weight: 600; }
.detail { margin: 0; color: #475467; font-size: 13px; }
</style>
