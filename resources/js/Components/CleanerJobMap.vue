<template>
  <div class="cleaner-map">
    <div ref="mapElement" class="cleaner-map__canvas"></div>
    <div v-if="!jobLocation" class="cleaner-map__placeholder">
      Job location is not available yet.
    </div>
    <div v-if="routeSummary" class="cleaner-map__summary">
      <span class="cleaner-map__dot"></span>
      <span>{{ routeSummary }}</span>
    </div>
    <div v-if="mapError" class="cleaner-map__error">
      {{ mapError }}
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { loadGoogleMaps } from '../utils/googleMaps'

const props = defineProps({
  apiKey: { type: String, default: '' },
  job: { type: Object, default: () => null },
  currentLocation: { type: Object, default: () => null }
})

const mapElement = ref(null)
const mapInstance = ref(null)
const googleRef = ref(null)
const directionsService = ref(null)
const directionsRenderer = ref(null)
const jobMarker = ref(null)
const cleanerMarker = ref(null)
const mapError = ref('')
const routeSummary = ref('')
const localPosition = ref(null)
const mapReady = ref(false)
const geocoder = ref(null)
const geocodedJobLocation = ref(null)
const lastRouteKey = ref(null)
const hasCenteredOnJob = ref(false)
const userAdjustedViewport = ref(false)

function parseCoordinate(value) {
  if (value == null) return null
  if (typeof value === 'string' && value.trim() === '') return null
  const num = Number(value)
  if (Number.isNaN(num) || !Number.isFinite(num)) return null
  return num
}

const rawJobLocation = computed(() => {
  if (!props.job) return null
  const location = props.job.location || props.job.address
  if (!location) return null
  const lat = parseCoordinate(location.lat)
  const lng = parseCoordinate(location.lng)
  if (lat == null || lng == null) return null
  return { lat, lng }
})

const jobLocation = computed(() => rawJobLocation.value || geocodedJobLocation.value || null)

const cleanerLocation = computed(() => {
  const provided = props.currentLocation
  if (provided) {
    const lat = parseCoordinate(provided.lat)
    const lng = parseCoordinate(provided.lng)
    if (lat != null && lng != null) {
      return { lat, lng }
    }
  }
  return localPosition.value
})

watch(() => props.apiKey, () => {
  resetMap()
  initialiseIfPossible()
})

watch(jobLocation, (value, previous) => {
  if (!value) {
    lastRouteKey.value = null
    if (directionsRenderer.value) {
      directionsRenderer.value.setDirections({ routes: [] })
    }
    return
  }

  if (!mapReady.value) {
    initialiseIfPossible()
    return
  }

  const changed = !previous || value.lat !== previous.lat || value.lng !== previous.lng
  if (changed) {
    placeJobMarker(value)
    if (!hasCenteredOnJob.value && mapInstance.value) {
      mapInstance.value.setCenter(value)
      hasCenteredOnJob.value = true
    }
    updateRoute()
  }
})

watch(cleanerLocation, () => {
  updateRoute()
})

onMounted(() => {
  ensureLocalPosition()
  initialiseIfPossible()
})

onBeforeUnmount(() => {
  if (jobMarker.value) {
    jobMarker.value.setMap(null)
    jobMarker.value = null
  }
  if (cleanerMarker.value) {
    cleanerMarker.value.setMap(null)
    cleanerMarker.value = null
  }
  if (directionsRenderer.value) {
    directionsRenderer.value.setMap(null)
    directionsRenderer.value = null
  }
  mapInstance.value = null
  googleRef.value = null
  mapReady.value = false
  userAdjustedViewport.value = false
})

function ensureLocalPosition() {
  if (localPosition.value || props.currentLocation || !('geolocation' in navigator)) return
  navigator.geolocation.getCurrentPosition(
    position => {
      localPosition.value = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      }
      updateRoute()
    },
    () => {},
    { enableHighAccuracy: true, timeout: 15000 }
  )
}

async function initialiseIfPossible() {
  if (!props.apiKey) {
    mapError.value = 'Google Maps is not configured.'
    return
  }
  if (!mapElement.value) return

  try {
    const google = await loadGoogleMaps(props.apiKey, ['routes'])
    googleRef.value = google
    if (!mapInstance.value) {
      const center = jobLocation.value || { lat: 51.509865, lng: -0.118092 }
      mapInstance.value = new google.maps.Map(mapElement.value, {
        center,
        zoom: 14,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false
      })
      mapInstance.value.addListener('zoom_changed', () => { userAdjustedViewport.value = true })
      mapInstance.value.addListener('dragstart', () => { userAdjustedViewport.value = true })
      directionsService.value = new google.maps.DirectionsService()
      directionsRenderer.value = new google.maps.DirectionsRenderer({
        map: mapInstance.value,
        suppressMarkers: true,
        polylineOptions: {
          strokeColor: '#0ea5e9',
          strokeOpacity: 0.9,
          strokeWeight: 5
        }
      })
      geocoder.value = new google.maps.Geocoder()
    }
    mapReady.value = true
    userAdjustedViewport.value = false
    mapError.value = ''
    await resolveJobLocation()
    updateRoute()
  } catch (error) {
    console.warn('Failed to load Google Maps', error)
    mapError.value = 'Unable to load the map right now.'
  }
}

function resetMap() {
  routeSummary.value = ''
  lastRouteKey.value = null
  hasCenteredOnJob.value = false
  userAdjustedViewport.value = false
  if (directionsRenderer.value) {
    directionsRenderer.value.setDirections({ routes: [] })
  }
}

function placeJobMarker(position) {
  if (!mapReady.value || !googleRef.value || !mapInstance.value || !position) return
  if (!jobMarker.value) {
    jobMarker.value = new googleRef.value.maps.Marker({
      map: mapInstance.value,
      position,
      title: props.job?.address_line1 || 'Job location',
      icon: buildMarkerIcon(1)
    })
  } else {
    jobMarker.value.setPosition(position)
    jobMarker.value.setIcon(buildMarkerIcon(1))
  }
}

function placeCleanerMarker(position) {
  if (!mapReady.value || !googleRef.value || !mapInstance.value || !position) {
    if (cleanerMarker.value) {
      cleanerMarker.value.setMap(null)
      cleanerMarker.value = null
    }
    return
  }

  if (!cleanerMarker.value) {
    cleanerMarker.value = new googleRef.value.maps.Marker({
      map: mapInstance.value,
      position,
      title: 'Your location',
      icon: {
        path: googleRef.value.maps.SymbolPath.CIRCLE,
        scale: 6,
        fillColor: '#22c55e',
        fillOpacity: 1,
        strokeColor: '#166534',
        strokeWeight: 2
      }
    })
  } else {
    cleanerMarker.value.setPosition(position)
  }
}

function updateRoute() {
  if (!mapReady.value || !directionsService.value || !directionsRenderer.value || !jobLocation.value) {
    return
  }

  placeJobMarker(jobLocation.value)

  const origin = cleanerLocation.value
  if (!origin) {
    placeCleanerMarker(null)
    directionsRenderer.value.setDirections({ routes: [] })
    routeSummary.value = ''
    lastRouteKey.value = null
    if (mapInstance.value && !userAdjustedViewport.value) {
      mapInstance.value.setCenter(jobLocation.value)
      mapInstance.value.setZoom(14)
    }
    return
  }

  placeCleanerMarker(origin)

  const destination = jobLocation.value
  const key = `${origin.lat},${origin.lng}-${destination.lat},${destination.lng}`
  if (lastRouteKey.value === key) {
    return
  }

  lastRouteKey.value = key

  directionsService.value.route({
    origin,
    destination,
    travelMode: googleRef.value.maps.TravelMode.DRIVING
  }, (result, status) => {
    if (status === googleRef.value.maps.DirectionsStatus.OK && result?.routes?.length) {
      directionsRenderer.value.setDirections(result)
      const leg = result.routes[0]?.legs?.[0]
      if (leg) {
        routeSummary.value = formatRouteSummary(leg)
        if (!userAdjustedViewport.value) {
          fitBounds(leg.start_location, leg.end_location)
        }
      } else {
        routeSummary.value = ''
      }
      mapError.value = ''
    } else {
      directionsRenderer.value.setDirections({ routes: [] })
      routeSummary.value = fallbackSummary(origin, destination)
      mapError.value = ''
      fitManualBounds(origin, destination)
      lastRouteKey.value = null
    }
  })
}

function fitBounds(start, end) {
  if (!mapInstance.value || !googleRef.value) return
  const bounds = new googleRef.value.maps.LatLngBounds()
  bounds.extend(start)
  bounds.extend(end)
  mapInstance.value.fitBounds(bounds, 60)
}

function formatRouteSummary(leg) {
  const distance = leg?.distance?.text
  const duration = leg?.duration?.text
  if (distance && duration) {
    return `Distance ${distance} • ETA ${duration}`
  }
  if (distance) return `Distance ${distance}`
  if (duration) return `ETA ${duration}`
  return ''
}

function fitManualBounds(origin, destination) {
  if (!mapInstance.value || !googleRef.value || !origin || !destination) return
  const bounds = new googleRef.value.maps.LatLngBounds()
  bounds.extend(origin)
  bounds.extend(destination)
  mapInstance.value.fitBounds(bounds, 60)
}

function fallbackSummary(origin, destination) {
  if (!origin || !destination) {
    return 'Preview unavailable'
  }
  const distance = haversineMeters(origin.lat, origin.lng, destination.lat, destination.lng)
  if (!distance) return 'Preview unavailable'
  if (distance >= 1000) {
    return `Distance ${(distance / 1000).toFixed(1)} km`
  }
  return `Distance ${Math.round(distance)} m`
}

function haversineMeters(lat1, lon1, lat2, lon2) {
  const R = 6371000
  const dLat = deg2rad(lat2 - lat1)
  const dLon = deg2rad(lon2 - lon1)
  const a = Math.sin(dLat / 2) ** 2 + Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLon / 2) ** 2
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  return R * c
}

function deg2rad(value) {
  return (value * Math.PI) / 180
}

function buildMarkerIcon(scale = 1) {
  if (!googleRef.value) return null
  const base = 36
  const size = base * scale
  return {
    url: 'https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-drop-pin.png?v=1762253393',
    scaledSize: new googleRef.value.maps.Size(size, size * 1.12),
    anchor: new googleRef.value.maps.Point(size / 2, size * 1.08)
  }
}

async function resolveJobLocation() {
  if (!mapReady.value) return

  if (rawJobLocation.value) {
    geocodedJobLocation.value = null
    placeJobMarker(rawJobLocation.value)
    if (!hasCenteredOnJob.value && mapInstance.value) {
      mapInstance.value.setCenter(rawJobLocation.value)
      hasCenteredOnJob.value = true
    }
    return
  }

  if (!props.job || !geocoder.value) {
    geocodedJobLocation.value = null
    return
  }

  const parts = [props.job.address_line1, props.job.address?.line1, props.job.postcode, props.job.address?.postcode, props.job.address?.city]
    .filter(Boolean)
  const query = [...new Set(parts)].join(', ')
  if (!query) {
    geocodedJobLocation.value = null
    mapError.value = 'Job address missing for directions.'
    return
  }

  geocoder.value.geocode({ address: query }, (results, status) => {
    if (status === 'OK' && results && results[0] && results[0].geometry?.location) {
      const loc = results[0].geometry.location
      geocodedJobLocation.value = { lat: loc.lat(), lng: loc.lng() }
      placeJobMarker(geocodedJobLocation.value)
      if (!hasCenteredOnJob.value && mapInstance.value) {
        mapInstance.value.setCenter(geocodedJobLocation.value)
        hasCenteredOnJob.value = true
      }
      mapError.value = ''
      updateRoute()
    } else {
      geocodedJobLocation.value = null
      mapError.value = 'Directions unavailable at the moment.'
    }
  })
}

watch(() => props.job?.id, () => {
  geocodedJobLocation.value = null
  hasCenteredOnJob.value = false
  lastRouteKey.value = null
  userAdjustedViewport.value = false
  if (mapReady.value) {
    resolveJobLocation()
  }
})
</script>

<style scoped>
.cleaner-map {
  border-radius: 16px;
  overflow: hidden;
  background: #f8fafc;
  border: 1px solid rgba(15, 23, 42, 0.08);
  position: relative;
}

.cleaner-map__canvas {
  width: 100%;
  height: 220px;
}

@media (min-width: 576px) {
  .cleaner-map__canvas {
    height: 260px;
  }
}

.cleaner-map__placeholder {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 1.5rem;
  text-align: center;
  font-size: 0.95rem;
  color: #64748b;
  background: linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(248, 250, 252, 0.9));
}

.cleaner-map__summary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  font-size: 0.9rem;
  background: #fff;
  border-top: 1px solid rgba(15, 23, 42, 0.08);
}

.cleaner-map__dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #0ea5e9;
}

.cleaner-map__error {
  padding: 0.75rem 1rem;
  font-size: 0.9rem;
  color: #b91c1c;
  background: #fef2f2;
  border-top: 1px solid rgba(185, 28, 28, 0.2);
}
</style>
