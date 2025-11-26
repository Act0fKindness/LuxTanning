<template>
  <div class="sap-root">
    <div class="sap-field">
      <label class="sap-label">{{ title }}</label>
      <input
        ref="searchInput"
        type="text"
        class="sap-input"
        :placeholder="placeholder"
        @keydown.enter.prevent="handleLookupEnter"
      />
      <p class="sap-hint">Search a postcode, neighbourhood or drop a pin on the map.</p>
    </div>
    <div ref="mapEl" class="sap-map"></div>
    <div class="sap-radius">
      <label>Service radius</label>
      <div class="sap-radius-control">
        <input
          type="range"
          :min="minRadius"
          :max="maxRadius"
          step="1"
          :value="local.radiusKm"
          @input="onRadiusInput"
        />
        <span>{{ local.radiusKm.toFixed(0) }} km · ~{{ miles(local.radiusKm).toFixed(0) }} mi</span>
      </div>
    </div>
    <div class="sap-field inline">
      <label>Area label</label>
      <input
        type="text"
        class="sap-input"
        placeholder="e.g. Greater Manchester"
        :value="local.label"
        @input="event => emitChange({ label: event.target.value })"
      />
    </div>
    <p v-if="statusMessage" class="sap-status">{{ statusMessage }}</p>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  label: { type: String, default: 'Service area' },
  placeholder: { type: String, default: 'Search a postcode or city' },
  minRadius: { type: Number, default: 5 },
  maxRadius: { type: Number, default: 80 },
  mapsKey: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const page = usePage()
const mapEl = ref(null)
const searchInput = ref(null)
const statusMessage = ref('')

const googleKey = computed(() => props.mapsKey || page.props?.google?.maps_key || '')
const defaultState = () => ({
  label: props.modelValue?.label || props.modelValue?.service_area_label || '',
  address: props.modelValue?.address || '',
  placeId: props.modelValue?.place_id || props.modelValue?.service_area_place_id || '',
  lat: normalizeNumber(props.modelValue?.lat ?? props.modelValue?.service_area_lat),
  lng: normalizeNumber(props.modelValue?.lng ?? props.modelValue?.service_area_lng),
  radiusKm: normalizeRadius(props.modelValue?.radius_km ?? props.modelValue?.service_area_radius_km, props.minRadius, props.maxRadius),
})

const local = ref(defaultState())

let map = null
let marker = null
let circle = null
let autocomplete = null
let mapClickListener = null
let markerDragListener = null
let scriptPromise = null

watch(
  () => props.modelValue,
  value => {
    const incoming = {
      label: value?.label || value?.service_area_label || local.value.label,
      address: value?.address || local.value.address,
      placeId: value?.place_id || value?.service_area_place_id || local.value.placeId,
      lat: normalizeNumber(value?.lat ?? value?.service_area_lat ?? local.value.lat),
      lng: normalizeNumber(value?.lng ?? value?.service_area_lng ?? local.value.lng),
      radiusKm: normalizeRadius(value?.radius_km ?? value?.service_area_radius_km ?? local.value.radiusKm, props.minRadius, props.maxRadius),
    }
    local.value = incoming
    if (incoming.lat && incoming.lng) {
      placeMarker({ lat: incoming.lat, lng: incoming.lng }, false)
    }
    updateCircleRadius()
  },
  { deep: true }
)

function normalizeNumber(value) {
  const num = typeof value === 'string' ? parseFloat(value) : value
  return typeof num === 'number' && !Number.isNaN(num) ? num : null
}

function normalizeRadius(value, min, max) {
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (!num || Number.isNaN(num)) {
    return min
  }
  return Math.min(Math.max(num, min), max)
}

function miles(km) {
  return km * 0.621371
}

function emitChange(patch = {}) {
  local.value = { ...local.value, ...patch }
  emit('update:modelValue', {
    label: local.value.label,
    place_id: local.value.placeId,
    address: local.value.address,
    lat: local.value.lat,
    lng: local.value.lng,
    radius_km: local.value.radiusKm,
  })
  if (patch.lat && patch.lng) {
    placeMarker({ lat: patch.lat, lng: patch.lng })
  } else if (typeof patch.radiusKm === 'number') {
    updateCircleRadius()
  }
}

function handleLookupEnter(event) {
  event.preventDefault()
}

function onRadiusInput(event) {
  const value = Number(event.target.value)
  emitChange({ radiusKm: normalizeRadius(value, props.minRadius, props.maxRadius) })
}

async function initMap() {
  if (!googleKey.value) {
    statusMessage.value = 'Google Maps key missing; map disabled.'
    return
  }
  await ensureGoogle()
  if (!mapEl.value || !window.google) return
  map = new window.google.maps.Map(mapEl.value, {
    center: local.value.lat && local.value.lng ? { lat: local.value.lat, lng: local.value.lng } : { lat: 54.5, lng: -3.5 },
    zoom: local.value.lat ? 11 : 5,
    mapTypeControl: false,
    fullscreenControl: false,
    streetViewControl: false,
  })
  marker = new window.google.maps.Marker({
    map,
    draggable: true,
    visible: Boolean(local.value.lat && local.value.lng),
    position: local.value.lat && local.value.lng ? { lat: local.value.lat, lng: local.value.lng } : undefined,
  })
  circle = new window.google.maps.Circle({
    map,
    strokeColor: '#4FE1C1',
    strokeOpacity: 0.7,
    strokeWeight: 1.5,
    fillColor: '#4FE1C1',
    fillOpacity: 0.18,
    radius: local.value.radiusKm * 1000,
  })
  if (local.value.lat && local.value.lng) {
    circle.setCenter({ lat: local.value.lat, lng: local.value.lng })
  }
  markerDragListener = marker.addListener('dragend', event => {
    if (!event || !event.latLng) return
    const lat = event.latLng.lat()
    const lng = event.latLng.lng()
    circle.setCenter({ lat, lng })
    emitChange({ lat, lng })
  })
  mapClickListener = map.addListener('click', event => {
    if (!event || !event.latLng) return
    const lat = event.latLng.lat()
    const lng = event.latLng.lng()
    emitChange({ lat, lng })
  })
  setupAutocomplete()
  window.setTimeout(() => window.google.maps.event.trigger(map, 'resize'), 150)
}

function setupAutocomplete() {
  if (!searchInput.value || !window.google) return
  const options = { fields: ['name', 'formatted_address', 'geometry', 'place_id'], componentRestrictions: { country: ['gb', 'ie'] } }
  autocomplete = new window.google.maps.places.Autocomplete(searchInput.value, options)
  autocomplete.addListener('place_changed', () => {
    const place = autocomplete.getPlace()
    if (!place || !place.geometry || !place.geometry.location) return
    const lat = place.geometry.location.lat()
    const lng = place.geometry.location.lng()
    emitChange({
      lat,
      lng,
      label: local.value.label || place.name || place.formatted_address || '',
      placeId: place.place_id || local.value.placeId,
      address: place.formatted_address || place.name || '',
    })
    if (place.formatted_address) {
      statusMessage.value = place.formatted_address
    }
  })
}

function placeMarker(position, pan = true) {
  if (!marker || !circle || !map) return
  marker.setVisible(true)
  marker.setPosition(position)
  circle.setCenter(position)
  if (pan) {
    map.panTo(position)
    if (map.getZoom() < 10) {
      map.setZoom(10)
    }
  }
}

function updateCircleRadius() {
  if (!circle) return
  circle.setRadius(local.value.radiusKm * 1000)
}

function ensureGoogle() {
  if (typeof window === 'undefined') return Promise.resolve()
  if (window.google && window.google.maps) return Promise.resolve()
  if (scriptPromise) return scriptPromise
  const existing = document.getElementById('google-maps-script')
  if (existing) {
    scriptPromise = new Promise(resolve => existing.addEventListener('load', () => resolve(), { once: true }))
    return scriptPromise
  }
  scriptPromise = new Promise(resolve => {
    const script = document.createElement('script')
    script.id = 'google-maps-script'
    script.src = `https://maps.googleapis.com/maps/api/js?key=${googleKey.value}&libraries=places`
    script.async = true
    script.defer = true
    script.onload = () => resolve()
    document.head.appendChild(script)
  })
  return scriptPromise
}

onMounted(() => {
  initMap()
})

onBeforeUnmount(() => {
  if (mapClickListener) mapClickListener.remove()
  if (markerDragListener) markerDragListener.remove()
  autocomplete = null
})
</script>

<style scoped>
.sap-root {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.sap-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.sap-field.inline {
  margin-top: 6px;
}

.sap-label {
  font-weight: 600;
}

.sap-input {
  border-radius: 14px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  padding: 10px 14px;
  font-size: 14px;
}

.sap-hint {
  font-size: 12px;
  color: #64748b;
  margin: 0;
}

.sap-map {
  width: 100%;
  height: 260px;
  border-radius: 18px;
  border: 1px solid rgba(15, 23, 42, 0.1);
  overflow: hidden;
}

.sap-radius {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.sap-radius-control {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sap-radius-control input[type='range'] {
  flex: 1;
}

.sap-status {
  font-size: 13px;
  color: #0f172a;
  opacity: 0.8;
}
</style>
