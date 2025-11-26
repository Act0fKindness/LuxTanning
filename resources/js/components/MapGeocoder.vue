<template>
  <div ref="mapEl" class="map-component"></div>
</template>

<script setup>
const props = defineProps({ googleKey: String })
const mapEl = ref(null)
onMounted(() => {
  if (!props.googleKey) return
})
</script>

<style scoped>
.map-component { width: 100%; height: 200px; }
</style>
