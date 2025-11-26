<template>
  <div class="track-map">
    <div ref="mapElement" class="track-map__canvas"></div>
    <div v-if="!hasCoordinates" class="track-map__placeholder">
      <p>{{ placeholderCopy }}</p>
      <small v-if="errorMessage">{{ errorMessage }}</small>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { loadGoogleMaps } from '../utils/googleMaps'

const props = defineProps({
  apiKey: { type: String, default: '' },
  destination: { type: Object, default: null },
  latestPing: { type: Object, default: null },
})

const mapElement = ref(null)
const mapInstance = ref(null)
const googleRef = ref(null)
const destinationMarker = ref(null)
const cleanerMarker = ref(null)
const ready = ref(false)
const errorMessage = ref('')

const destinationCoords = computed(() => normalisePoint(props.destination))
const cleanerCoords = computed(() => normalisePoint(props.latestPing))
const hasCoordinates = computed(() => !!destinationCoords.value || !!cleanerCoords.value)
const placeholderCopy = computed(() => (props.apiKey ? 'Waiting for the latest location from your cleaner.' : 'Map is not yet configured.'))

watch(() => props.apiKey, () => initialise())
watch([destinationCoords, cleanerCoords], () => {
  if (!ready.value) {
    initialise()
    return
  }
  drawMarkers()
})

onMounted(() => initialise())

async function initialise() {
  if (!mapElement.value || ready.value) {
    drawMarkers()
    return
  }

  if (!props.apiKey) {
    errorMessage.value = 'Missing Google Maps API key.'
    return
  }

  try {
    const google = await loadGoogleMaps(props.apiKey)
    googleRef.value = google
    const center = cleanerCoords.value || destinationCoords.value || { lat: 51.509865, lng: -0.118092 }
    mapInstance.value = new google.maps.Map(mapElement.value, {
      center,
      zoom: 13,
      fullscreenControl: false,
      streetViewControl: false,
      mapTypeControl: false,
    })
    ready.value = true
    errorMessage.value = ''
    drawMarkers()
  } catch (error) {
    console.warn('Failed to load Google Maps', error)
    errorMessage.value = 'Unable to load the map right now.'
  }
}

function drawMarkers() {
  if (!ready.value || !mapInstance.value || !googleRef.value) return

  updateDestinationMarker()
  updateCleanerMarker()
  fitBoundsIfNeeded()
}

function updateDestinationMarker() {
  const destination = destinationCoords.value
  if (!destination) {
    if (destinationMarker.value) {
      destinationMarker.value.setMap(null)
      destinationMarker.value = null
    }
    return
  }

  const label = destination.label || 'Home'
  if (!destinationMarker.value) {
    destinationMarker.value = new googleRef.value.maps.Marker({
      map: mapInstance.value,
      position: destination,
      title: label,
      icon: buildMarkerIcon('#0ea5e9'),
    })
  } else {
    destinationMarker.value.setPosition(destination)
  }
}

function updateCleanerMarker() {
  const cleaner = cleanerCoords.value
  if (!cleaner) {
    if (cleanerMarker.value) {
      cleanerMarker.value.setMap(null)
      cleanerMarker.value = null
    }
    return
  }

  const label = cleaner.label || 'Cleaner location'
  if (!cleanerMarker.value) {
    cleanerMarker.value = new googleRef.value.maps.Marker({
      map: mapInstance.value,
      position: cleaner,
      title: label,
      icon: buildMarkerIcon('#22c55e'),
    })
  } else {
    cleanerMarker.value.setPosition(cleaner)
  }
}

function fitBoundsIfNeeded() {
  const destination = destinationCoords.value
  const cleaner = cleanerCoords.value
  if (!destination && !cleaner) {
    return
  }

  if (destination && cleaner) {
    const bounds = new googleRef.value.maps.LatLngBounds()
    bounds.extend(destination)
    bounds.extend(cleaner)
    mapInstance.value.fitBounds(bounds, { top: 80, right: 80, bottom: 80, left: 80 })
    return
  }

  const target = cleaner || destination
  mapInstance.value.setCenter(target)
  mapInstance.value.setZoom(14)
}

function normalisePoint(point) {
  if (!point) return null
  const lat = Number(point.lat)
  const lng = Number(point.lng)
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
    return null
  }
  return {
    lat,
    lng,
    label: point.label ?? null,
  }
}

function buildMarkerIcon(color) {
  if (!googleRef.value) return undefined
  return {
    path: googleRef.value.maps.SymbolPath.CIRCLE,
    fillColor: color,
    fillOpacity: 0.9,
    strokeWeight: 2,
    strokeColor: '#ffffff',
    scale: 8,
  }
}
</script>

<style scoped>
.track-map {
  position: relative;
  min-height: 320px;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(15,23,42,0.08);
  background: #f8fafc;
}

.track-map__canvas {
  width: 100%;
  height: 100%;
  min-height: 320px;
}

.track-map__placeholder {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
  text-align: center;
  color: #475467;
  background: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(14,165,233,0.08));
}

.track-map__placeholder p {
  margin-bottom: 8px;
  font-weight: 600;
}

.track-map__placeholder small {
  color: #0f172a;
  opacity: 0.7;
}
</style>
