<template>
  <PublicLayout :use-tenant-branding="useTenantBranding">
    <div class="public-shell">
      <PageBlueprint v-if="page" :page="page" :context="context" />
      <div v-else class="empty">Missing page definition for <code>{{ pageKey }}</code></div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue'
import PublicLayout from '../../Layouts/PublicLayout.vue'
import PageBlueprint from '../../Components/Blueprint/PageBlueprint.vue'
import { pageRegistry } from '../../PageRegistry'

const props = defineProps({ pageKey: { type: String, required: true }, context: { type: Object, default: () => ({}) } })
const page = computed(() => pageRegistry[props.pageKey])
const useTenantBranding = computed(() => page.value?.host === 'tenant')
</script>

<style scoped>
.public-shell { padding-top: 0; }
.empty { padding: 24px; border: 1px dashed rgba(15,23,42,.2); border-radius: 12px; }
</style>
