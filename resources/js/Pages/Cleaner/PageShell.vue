<template>
  <WorkspacePage role="cleaner" :page-key="pageKey" :context="context" />
</template>

<script setup>
import WorkspacePage from '../Workspaces/WorkspacePage.vue'
const props = defineProps({ pageKey: { type: String, required: true }, context: { type: Object, default: () => ({}) } })
</script>
