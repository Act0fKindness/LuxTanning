<template>
  <AuthLayout>
    <PageBlueprint v-if="page" :page="page" :context="context" />
    <div v-else class="empty">Missing auth page blueprint for <code>{{ pageKey }}</code>.</div>
  </AuthLayout>
</template>

<script setup>
import { computed } from 'vue'
import AuthLayout from '../../Layouts/AuthLayout.vue'
import PageBlueprint from '../../Components/Blueprint/PageBlueprint.vue'
import { pageRegistry } from '../../PageRegistry'

const props = defineProps({ pageKey: { type: String, required: true }, context: { type: Object, default: () => ({}) } })
const page = computed(() => pageRegistry[props.pageKey])
</script>

<style scoped>
.empty { padding: 24px; border: 1px dashed rgba(15,23,42,.2); border-radius: 12px; }
</style>
