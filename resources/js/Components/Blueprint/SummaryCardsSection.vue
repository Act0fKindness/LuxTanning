<template>
  <section>
    <header class="section-head">
      <div>
        <p class="eyebrow" v-if="section.badge">{{ format(section.badge) }}</p>
        <h2>{{ section.title }}</h2>
        <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
      </div>
    </header>
    <div class="card-grid">
      <article v-for="card in section.props.cards" :key="card.label" class="stat-card">
        <div class="card-head">
          <span class="label">{{ format(card.label) }}</span>
          <span v-if="card.badge" class="badge" :class="card.badgeVariant || 'info'">{{ format(card.badge) }}</span>
        </div>
        <p class="value">{{ format(card.value) }}</p>
        <p class="meta" v-if="card.delta">{{ format(card.delta) }}</p>
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
section { color: #0f172a; }
.section-head { margin-bottom: 16px; }
.section-head h2 { margin: 4px 0; font-size: 20px; color: inherit; }
.eyebrow { text-transform: uppercase; font-size: 12px; letter-spacing: .2em; color: #6366f1; margin: 0; }
.muted { color: #475467; margin: 0; }
.card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; }
.stat-card { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 18px; background: #f8fafc; }
.card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-weight: 600; color: inherit; }
.value { font-size: 28px; font-weight: 700; margin: 0 0 4px; color: inherit; }
.meta { margin: 0; font-size: 14px; color: #475467; }
.badge { border-radius: 999px; padding: 2px 10px; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
.badge.info { background: rgba(79,225,193,.18); color: #0f766e; }
.badge.success { background: rgba(52,211,153,.2); color: #065f46; }
.badge.warning { background: rgba(251,191,36,.2); color: #92400e; }
.badge.danger { background: rgba(248,113,113,.2); color: #991b1b; }
</style>
