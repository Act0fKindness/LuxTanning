<template>
  <WorkspaceLayout :role="role" :mode="page?.layout === 'pwa' ? 'pwa' : 'workspace'" :title="page?.title" :breadcrumbs="page?.breadcrumbs" :nav="nav">
    <PageBlueprint v-if="page" :page="page" :context="context" />
    <div v-else class="empty-state">
      <p>Missing page definition for <code>{{ pageKey }}</code>.</p>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { computed } from 'vue'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import PageBlueprint from '../../Components/Blueprint/PageBlueprint.vue'
import { pageRegistry } from '../../PageRegistry'
import { roleNav } from '../../PageRegistry/nav'

const props = defineProps({
  role: { type: String, required: true },
  pageKey: { type: String, required: true },
  context: { type: Object, default: () => ({}) },
})

const page = computed(() => pageRegistry[props.pageKey])
const nav = computed(() => roleNav[props.role] || null)
</script>

<style scoped>
.empty-state { padding: 40px; border: 1px dashed rgba(15,23,42,.2); border-radius: 16px; background: rgba(15,23,42,.02); }
</style>
