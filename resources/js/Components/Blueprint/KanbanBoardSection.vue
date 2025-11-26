<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <div class="board">
      <article v-for="column in section.props.columns" :key="column.title" class="column">
        <header>
          <p class="label">{{ format(column.title) }}</p>
          <span class="count">{{ column.items?.length || 0 }}</span>
        </header>
        <ul>
          <li v-for="item in column.items" :key="item.id || item.title">
            <p class="title">{{ format(item.title) }}</p>
            <p class="detail">{{ format(item.detail) }}</p>
            <div class="meta">
              <span v-if="item.assignee" class="pill">{{ format(item.assignee) }}</span>
              <span v-if="item.eta" class="pill soft">{{ format(item.eta) }}</span>
            </div>
          </li>
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
.section-head { margin-bottom: 14px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.board { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
.column { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 16px; background: #f8fafc; display: flex; flex-direction: column; gap: 12px; }
.column header { display: flex; justify-content: space-between; align-items: center; font-weight: 600; }
.column ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }
.column li { background: #fff; border-radius: 14px; padding: 12px; border: 1px solid rgba(15,23,42,.08); }
.title { margin: 0 0 4px; font-weight: 600; }
.detail { margin: 0; font-size: 13px; color: #475467; }
.meta { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px; }
.pill { border-radius: 999px; padding: 2px 10px; background: #0f172a; color: #fff; font-size: 12px; }
.pill.soft { background: rgba(15,23,42,.08); color: #0f172a; }
</style>
