<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <ul class="insight-list">
      <li v-for="item in section.props.items" :key="item.title" class="insight-item">
        <div class="icon" v-if="item.icon">
          <i :class="['bi', item.icon]"></i>
        </div>
        <div class="body">
          <p class="title">{{ format(item.title) }}</p>
          <p class="detail">{{ format(item.description) }}</p>
          <div class="meta" v-if="item.meta?.length">
            <span v-for="meta in item.meta" :key="meta" class="pill">{{ format(meta) }}</span>
          </div>
        </div>
        <span v-if="item.badge" class="badge" :class="item.badgeVariant || 'info'">{{ format(item.badge) }}</span>
      </li>
    </ul>
  </section>
</template>

<script setup>
import { formatText } from '../../utils/contextFormatter'
const props = defineProps({ section: { type: Object, required: true }, context: { type: Object, default: () => ({}) } })
const format = text => formatText(text, props.context)
</script>

<style scoped>
section { color: #0f172a; }
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; color: inherit; }
.muted { color: #475467; margin: 0; }
.insight-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }
.insight-item { display: flex; gap: 14px; border: 1px solid rgba(15,23,42,.08); border-radius: 16px; padding: 14px 16px; align-items: flex-start; background: #f8fafc; }
.icon { width: 36px; height: 36px; border-radius: 10px; background: #eef2ff; display: inline-flex; align-items: center; justify-content: center; color: #4338ca; font-size: 18px; }
.body { flex: 1; }
.title { margin: 0 0 4px; font-weight: 600; color: inherit; }
.detail { margin: 0; color: #475467; font-size: 14px; }
.meta { margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px; }
.pill { border-radius: 999px; background: #f4f4f5; padding: 2px 10px; font-size: 12px; color: #52525b; }
.badge { align-self: center; border-radius: 8px; padding: 4px 8px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
.badge.info { background: rgba(59,130,246,.15); color: #1d4ed8; }
.badge.success { background: rgba(16,185,129,.15); color: #047857; }
.badge.warning { background: rgba(251,191,36,.20); color: #92400e; }
.badge.danger { background: rgba(248,113,113,.20); color: #991b1b; }
</style>
