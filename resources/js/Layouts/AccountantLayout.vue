<template>
  <OwnerLayout>
    <template #default>
      <slot />
    </template>
  </OwnerLayout>
</template>

<script>
// Accountant reuses Owner shell; customize nav later.
import OwnerLayout from './OwnerLayout.vue'
export default { name:'AccountantLayout', components:{ OwnerLayout } }
</script>

