<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <div class="tile-grid">
      <article v-for="tile in tiles" :key="tile.label" class="tile" :class="tile.state || 'info'">
        <div class="tile-head">
          <div>
            <p class="tile-label">{{ format(tile.label) }}</p>
            <p v-if="tile.hint" class="tile-hint">{{ format(tile.hint) }}</p>
          </div>
          <span class="tile-status">{{ format(tile.status) }}</span>
        </div>
        <p v-if="tile.description" class="tile-desc">{{ format(tile.description) }}</p>
        <p v-if="tile.meta" class="tile-meta">{{ format(tile.meta) }}</p>
        <div class="tile-footer">
          <p v-if="tile.subtext" class="tile-subtext">{{ format(tile.subtext) }}</p>
          <component
            v-if="tile.action"
            :is="tile.action.href ? 'a' : 'button'"
            class="tile-action"
            :href="tile.action.href || null"
            type="button"
          >
            <i v-if="tile.action.icon" :class="['bi', tile.action.icon]"></i>
            {{ format(tile.action.label) }}
          </component>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { formatText } from '../../utils/contextFormatter'

const props = defineProps({ section: { type: Object, required: true }, context: { type: Object, default: () => ({}) } })
const format = text => formatText(text, props.context)
const tiles = computed(() => (Array.isArray(props.section.props?.tiles) ? props.section.props.tiles : []))
</script>

<style scoped>
.section-head { margin-bottom: 14px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.tile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
.tile { border-radius: 18px; padding: 18px; border: 1px solid rgba(15,23,42,.08); background: #fff; box-shadow: 0 12px 24px rgba(15,23,42,.05); display: flex; flex-direction: column; gap: 10px; }
.tile.info { border-color: rgba(59,130,246,.2); }
.tile.success { border-color: rgba(16,185,129,.3); }
.tile.warning { border-color: rgba(251,191,36,.35); }
.tile.danger { border-color: rgba(248,113,113,.35); }
.tile-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.tile-label { margin: 0; font-weight: 600; }
.tile-hint { margin: 2px 0 0; color: #64748b; font-size: 13px; }
.tile-status { border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; background: rgba(15,23,42,.04); color: #0f172a; }
.tile.success .tile-status { background: rgba(16,185,129,.18); color: #047857; }
.tile.warning .tile-status { background: rgba(251,191,36,.2); color: #92400e; }
.tile.danger .tile-status { background: rgba(248,113,113,.24); color: #b91c1c; }
.tile-desc { margin: 0; color: #0f172a; font-size: 15px; }
.tile-meta { margin: 0; color: #94a3b8; font-size: 13px; }
.tile-footer { margin-top: auto; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
.tile-subtext { margin: 0; color: #475467; font-size: 13px; }
.tile-action { border-radius: 999px; padding: 8px 14px; border: none; font-weight: 600; font-size: 14px; background: #0f172a; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
.tile-action:hover { opacity: .9; }
.tile.info .tile-action { background: #1d4ed8; }
.tile.success .tile-action { background: #047857; }
.tile.warning .tile-action { background: #b45309; }
.tile.danger .tile-action { background: #b91c1c; }
</style>
