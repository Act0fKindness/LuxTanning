<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <div class="action-grid">
      <component
        v-for="action in section.props.actions"
        :is="action.href ? 'a' : 'button'"
        :href="action.href || null"
        :key="action.label"
        type="button"
        class="action-tile"
        :class="{ link: action.href }"
      >
        <div class="icon" v-if="action.icon"><i :class="['bi', action.icon]"></i></div>
        <div>
          <p class="label">{{ format(action.label) }}</p>
          <p class="hint">{{ format(action.description) }}</p>
        </div>
        <span class="chevron">→</span>
      </component>
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
.action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
.action-tile { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 16px; display: flex; align-items: center; gap: 12px; text-align: left; cursor: pointer; background: #f8fafc; transition: border .2s, transform .2s; text-decoration: none; color: inherit; }
.action-tile:hover { border-color: #4f46e5; transform: translateY(-2px); }
.icon { width: 40px; height: 40px; border-radius: 12px; background: #eef2ff; color: #4338ca; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
.label { margin: 0; font-weight: 600; }
.hint { margin: 2px 0 0; color: #475467; font-size: 13px; }
.chevron { margin-left: auto; color: #94a3b8; }
</style>
