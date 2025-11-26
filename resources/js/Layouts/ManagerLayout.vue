<template>
  <OwnerLayout>
    <template #default>
      <slot />
    </template>
  </OwnerLayout>
</template>

<script>
// For now, ManagerLayout inherits Owner shell. We can tailor menus later.
import OwnerLayout from './OwnerLayout.vue'
export default { name:'ManagerLayout', components:{ OwnerLayout } }
</script>

