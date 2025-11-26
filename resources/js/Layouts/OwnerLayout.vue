<template>
  <WorkspaceLayout
    :role="role"
    :mode="mode"
    :title="resolvedTitle"
    :breadcrumbs="resolvedBreadcrumbs"
    :nav="resolvedNav"
  >
    <slot />
  </WorkspaceLayout>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import WorkspaceLayout from './WorkspaceLayout.vue'
import { roleNav } from '../PageRegistry/nav'

const props = defineProps({
  title: { type: String, default: '' },
  breadcrumbs: { type: Array, default: () => [] },
  nav: { type: Object, default: null },
  role: { type: String, default: 'owner' },
  mode: { type: String, default: 'workspace' },
})

const page = usePage()
const resolvedTitle = computed(() => props.title || page.props?.title || '')
const resolvedBreadcrumbs = computed(() => {
  if (props.breadcrumbs?.length) {
    return props.breadcrumbs
  }
  return page.props?.breadcrumbs || []
})
const resolvedNav = computed(() => props.nav || roleNav[props.role] || roleNav.owner || null)
</script>
