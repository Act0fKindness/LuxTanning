<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>
    <div class="map-shell" ref="mapContainer">
      <div v-if="!googleKey" class="map-placeholder">
        Provide a Google Maps key to render live tracking.
      </div>
      <div v-else-if="mapError" class="map-placeholder">
        {{ mapError }}
      </div>
    </div>
    <ul class="marker-legend" v-if="section.props.markers?.length">
      <li v-for="marker in section.props.markers" :key="marker.id || marker.title">
        <span class="dot" :class="marker.state || 'info'"></span>
        <div>
          <p class="label">{{ format(marker.title) }}</p>
          <p class="hint">{{ format(marker.detail) }}</p>
        </div>
      </li>
    </ul>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Loader } from '@googlemaps/js-api-loader'
import { formatText, getContextValue } from '../../utils/contextFormatter'

const props = defineProps({
  section: { type: Object, required: true },
  context: { type: Object, default: () => ({}) },
  googleKey: { type: String, default: '' },
})

const mapContainer = ref(null)
const mapError = ref('')
const mapLoading = ref(false)
let mapInstance = null
let markersInstances = []

const format = text => formatText(text, props.context)

const markers = computed(() => {
  if (props.section.props.markersKey) {
    const resolved = getContextValue(props.context, props.section.props.markersKey, [])
    return Array.isArray(resolved) ? resolved : []
  }
  return Array.isArray(props.section.props.markers) ? props.section.props.markers : []
})

const renderMarkers = () => {
  if (!mapInstance) return
  markersInstances.forEach(marker => marker.setMap(null))
  markersInstances = []

  markers.value.forEach(marker => {
    const pin = new google.maps.Marker({
      position: { lat: marker.lat, lng: marker.lng },
      title: format(marker.title),
      map: mapInstance,
    })
    markersInstances.push(pin)
  })
}

const initMap = async () => {
  if (!props.googleKey || !mapContainer.value || mapLoading.value) return

  mapError.value = ''
  mapLoading.value = true

  try {
    const loader = new Loader({ apiKey: props.googleKey, version: 'weekly' })
    const { Map } = await loader.importLibrary('maps')

    const center = markers.value?.[0]
      ? { lat: markers.value[0].lat, lng: markers.value[0].lng }
      : { lat: 51.5072, lng: -0.1276 }

    mapInstance = new Map(mapContainer.value, {
      center,
      zoom: props.section.props.zoom || 11,
      disableDefaultUI: true,
    })

    renderMarkers()
  } catch (error) {
      console.warn('[Lux] Map load failed', error)
    mapError.value = 'Map preview unavailable right now.'
  } finally {
    mapLoading.value = false
  }
}

onMounted(() => initMap())

watch(() => props.googleKey, () => {
  if (mapInstance) {
    renderMarkers()
  } else {
    initMap()
  }
})

watch(markers, () => {
  if (mapInstance) {
    renderMarkers()
  }
}, { deep: true })
</script>

<style scoped>
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.map-shell { width: 100%; height: 320px; border-radius: 18px; overflow: hidden; border: 1px solid rgba(15,23,42,.08); background: #eef2ff; position: relative; }
.map-placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: 500; }
.marker-legend { list-style: none; display: flex; flex-wrap: wrap; gap: 12px; margin: 14px 0 0; padding: 0; }
.marker-legend li { display: flex; gap: 8px; align-items: center; border: 1px solid rgba(15,23,42,.08); border-radius: 12px; padding: 8px 12px; }
.dot { width: 10px; height: 10px; border-radius: 50%; display: inline-flex; }
.dot.info { background: #3b82f6; }
.dot.success { background: #10b981; }
.dot.warning { background: #f59e0b; }
.dot.danger { background: #ef4444; }
.label { margin: 0; font-weight: 600; }
.hint { margin: 0; color: #475467; font-size: 13px; }
</style>
