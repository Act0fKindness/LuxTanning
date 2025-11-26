<template>
  <section :style="sectionStyle">
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th v-for="col in section.props.columns" :key="col.key" :class="col.align || 'left'">{{ col.label }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, idx) in rows" :key="idx">
            <td
              v-for="col in section.props.columns"
              :key="col.key"
              :class="col.align || 'left'"
              :data-label="col.label"
            >
              <span>{{ format(row?.[col.key]) }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p class="foot" v-if="section.props.footer">{{ format(section.props.footer) }}</p>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { formatText, getContextValue } from '../../utils/contextFormatter'

const props = defineProps({ section: { type: Object, required: true }, context: { type: Object, default: () => ({}) } })
const format = text => formatText(text, props.context)

const sectionStyle = computed(() => {
  const maxWidth = props.section.props.maxWidth
  if (!maxWidth) {
    return null
  }
  return { maxWidth, margin: '0 auto' }
})

const rows = computed(() => {
  if (props.section.props.rowsKey) {
    const resolved = getContextValue(props.context, props.section.props.rowsKey, [])
    return Array.isArray(resolved) ? resolved : []
  }
  return Array.isArray(props.section.props.rows) ? props.section.props.rows : []
})
</script>

<style scoped>
section { color: #0f172a; }
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.table-wrapper { overflow-x: auto; border-radius: 16px; border: 1px solid rgba(15,23,42,.08); background: #fff; }
table { width: 100%; border-collapse: collapse; font-size: 14px; color: inherit; }
thead { background: #f8fafc; }
th, td { padding: 12px 16px; border-bottom: 1px solid rgba(15,23,42,.08); text-align: left; color: inherit; }
th:last-child, td:last-child { text-align: right; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:nth-child(odd) { background: #f8fafc; }
.foot { margin-top: 10px; color: #94a3b8; font-size: 13px; }
@media (max-width: 640px) {
  .table-wrapper { border: none; background: transparent; }
  table, thead, tbody, th, td, tr { display: block; width: 100%; }
  thead { display: none; }
  tbody tr { margin-bottom: 12px; border: 1px solid rgba(15,23,42,.08); border-radius: 14px; background: #fff; }
  tbody td { border-bottom: 1px solid rgba(15,23,42,.08); padding: 10px 14px; text-align: left !important; }
  tbody td:last-child { border-bottom: none; }
  tbody td::before { content: attr(data-label); display: block; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin-bottom: 4px; }
}
</style>
