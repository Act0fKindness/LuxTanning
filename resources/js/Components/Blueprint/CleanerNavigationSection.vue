<template>
  <section>
    <header class="section-head">
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>

    <div v-if="job" class="nav-shell">
      <div class="chip-row">
        <span class="chip">ETA {{ etaLabel }}</span>
        <span class="chip">{{ distanceLabel }}</span>
        <span class="chip">Arrive by {{ arriveByLabel }}</span>
      </div>

      <div v-if="permissionDenied" class="permission-card">
        <h3>Enable location to keep customers in the loop</h3>
        <p>We only collect GPS while you navigate or work on a job.</p>
        <div class="permission-actions">
          <button class="btn btn-primary" type="button" @click="requestLocation">Allow location</button>
          <button class="btn btn-ghost" type="button" @click="navigateWithoutTracking">Navigate without tracking</button>
        </div>
      </div>

      <div class="map-card">
        <div class="map-head">
          <span class="live-dot"></span>
          <div>
            <p class="map-title">Navigation live</p>
            <p class="map-desc">{{ navigationStatus }}</p>
          </div>
        </div>
        <CleanerJobMap :api-key="googleKey" :job="job" :current-location="currentLocation" class="map-canvas" />
        <div class="nav-metrics">
          <div>
            <p class="metric-label">ETA</p>
            <p class="metric-value">{{ etaLabel }}</p>
          </div>
          <div>
            <p class="metric-label">Distance</p>
            <p class="metric-value">{{ distanceLabel }}</p>
          </div>
          <div>
            <p class="metric-label">Geofence</p>
            <p class="metric-value">{{ geofenceLabel }}</p>
          </div>
        </div>
      </div>

      <div class="action-card">
        <div v-if="showArrivedBanner" class="arrival-banner">
          <strong>You’re within 200 m.</strong>
          <span>Ready to start?</span>
        </div>
        <div v-else-if="showCloseBanner" class="arrival-banner arrival-banner--soft">
          <strong>Close to the job.</strong>
          <span>Start unlocks at 200 m.</span>
        </div>

        <div class="buttons">
          <button type="button" class="btn btn-secondary" @click="handleReopenMaps">Reopen Google Maps</button>
          <button
            type="button"
            class="btn btn-primary"
            :disabled="!startEnabled || startingJob"
            @click="handleStartJob"
          >
            <span v-if="startingJob" class="spinner" aria-hidden="true"></span>
            {{ startButtonLabel }}
          </button>
          <button type="button" class="btn btn-ghost" @click="handleCancel">Cancel navigation</button>
        </div>
        <p class="helper" v-if="!showArrivedBanner && !overrideArmed">
          Start unlocks when you’re close, or tap Start anyway and give a reason.
        </p>
        <p class="status-line">
          GPS: {{ gpsQuality }} · {{ online ? 'Online' : 'Offline' }} · Geofence: {{ geofenceLabel }} · Accuracy: {{ accuracyLabel }}
        </p>
      </div>
    </div>

    <div v-else class="empty">
      <p>Navigation will appear once we load that job. Refresh to try again.</p>
    </div>
  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import CleanerJobMap from '../CleanerJobMap.vue'
import { formatText, getContextValue } from '../../utils/contextFormatter'

const props = defineProps({
  section: { type: Object, required: true },
  context: { type: Object, default: () => ({}) },
  googleKey: { type: String, default: '' },
})

const format = text => formatText(text, props.context)

const jobId = computed(() => {
  const key = props.section.props.jobIdKey
  return key ? getContextValue(props.context, key, null) : null
})

const jobLookup = computed(() => {
  const key = props.section.props.jobMapKey
  const value = key ? getContextValue(props.context, key, {}) : {}
  return value || {}
})

const job = computed(() => {
  const id = jobId.value
  if (!id) return null
  const lookup = jobLookup.value
  if (Array.isArray(lookup)) {
    return lookup.find(item => item?.id === id) || null
  }
  if (typeof lookup === 'object') {
    return lookup[id] ?? null
  }
  return null
})

const destination = computed(() => {
  const value = job.value
  if (!value) return null
  const location = value.location || value.address || {}
  const lat = parseFloat(location.lat ?? location.latitude)
  const lng = parseFloat(location.lng ?? location.longitude)
  if (Number.isFinite(lat) && Number.isFinite(lng)) {
    return { lat, lng }
  }
  return null
})

const sessionId = ref(null)
const currentLocation = ref(null)
const permissionDenied = ref(false)
const online = ref(typeof navigator === 'undefined' ? true : navigator.onLine)
const navigationStatus = ref('Opening Google Maps…')
const navTickMs = ref(15000)
const lastPingAt = ref(0)
const lastEtaAt = ref(0)
const etaMinutes = ref(null)
const distanceMeters = ref(null)
const accuracyMeters = ref(null)
const hasAnnouncedArrival = ref(false)
const overrideArmed = ref(false)
const startingJob = ref(false)
const locationWatchId = ref(null)
const visibilityHandler = () => {
  if (document.visibilityState === 'visible') {
    track('nav_visibility_resumed', { job_id: jobId.value })
    startWatch()
    refreshEta()
  } else {
    track('nav_visibility_paused', { job_id: jobId.value })
    stopWatch()
  }
}

const onlineHandler = () => { online.value = true }
const offlineHandler = () => { online.value = false }

const etaLabel = computed(() => (etaMinutes.value !== null ? `${etaMinutes.value} min` : '—'))
const distanceLabel = computed(() => formatDistance(distanceMeters.value))
const geofenceLabel = computed(() =>
  distanceMeters.value !== null ? formatDistance(distanceMeters.value, true) : '—'
)
const accuracyLabel = computed(() => (accuracyMeters.value ? `${Math.round(accuracyMeters.value)} m` : '—'))
const arriveByLabel = computed(() => formatArriveBy(job.value?.eta_window))
const gpsQuality = computed(() => {
  if (!accuracyMeters.value) return 'Unknown'
  if (accuracyMeters.value <= 30) return 'Good'
  if (accuracyMeters.value <= 75) return 'Fair'
  return 'Weak'
})

const showArrivedBanner = computed(() => distanceMeters.value !== null && distanceMeters.value <= 200)
const showCloseBanner = computed(() => !showArrivedBanner.value && distanceMeters.value !== null && distanceMeters.value <= 500)
const startEnabled = computed(() => showArrivedBanner.value || overrideArmed.value)
const startButtonLabel = computed(() => (showArrivedBanner.value ? 'Start job' : 'Start anyway'))

const mapUrl = computed(() => {
  if (!destination.value) return '#'
  return `https://www.google.com/maps/dir/?api=1&destination=${destination.value.lat},${destination.value.lng}&travelmode=driving&dir_action=navigate`
})

function track(event, props = {}) {
  if (window?.analytics?.track) {
    window.analytics.track(event, props)
  } else if (window?.mixpanel?.track) {
    window.mixpanel.track(event, props)
  } else {
    console.debug('[analytics]', event, props)
  }
}

async function bootstrapNavigation() {
  if (!job.value || sessionId.value) return
  if (!destination.value) {
    navigationStatus.value = 'Job has no map location. Call dispatch.'
    track('nav_no_destination', { job_id: jobId.value })
    return
  }

  track('nav_opened', { job_id: jobId.value })
  try {
    await startEnrouteSession()
    launchMaps()
    configureTickInterval()
    startWatch()
  } catch (error) {
    navigationStatus.value = 'Unable to start navigation right now.'
    console.error(error)
  }
}

function configureTickInterval() {
  const saveData = navigator?.connection?.saveData
  if (saveData) {
    navTickMs.value = 30000
  } else if (job.value?.status_badge === 'danger') {
    navTickMs.value = 10000
  } else {
    navTickMs.value = 15000
  }
}

function launchMaps() {
  if (!destination.value) return
  try {
    window.open(mapUrl.value, '_blank')
    track('nav_maps_launched', { job_id: jobId.value, provider: 'google' })
  } catch (error) {
    navigationStatus.value = 'Couldn’t open Maps—tap retry.'
    track('nav_maps_launch_failed', { job_id: jobId.value })
  }
}

function handleReopenMaps() {
  launchMaps()
}

function requestLocation() {
  permissionDenied.value = false
  startWatch(true)
}

function navigateWithoutTracking() {
  permissionDenied.value = false
  navigationStatus.value = 'Tracking paused. Location will resume when granted.'
  launchMaps()
}

function startWatch(forcePrompt = false) {
  if (!('geolocation' in navigator)) {
    navigationStatus.value = 'Device does not support GPS.'
    return
  }
  if (locationWatchId.value !== null) {
    return
  }
  locationWatchId.value = navigator.geolocation.watchPosition(
    position => handlePosition(position.coords),
    error => {
      if (error.code === error.PERMISSION_DENIED && !permissionDenied.value) {
        permissionDenied.value = true
        navigationStatus.value = 'Location permission needed to keep ETA fresh.'
        track('nav_gps_denied', { job_id: jobId.value })
      }
    },
    { enableHighAccuracy: true, maximumAge: 5000, timeout: forcePrompt ? 1000 : 10000 }
  )
}

function stopWatch() {
  if (locationWatchId.value !== null) {
    navigator.geolocation.clearWatch(locationWatchId.value)
    locationWatchId.value = null
  }
}

function handlePosition(coords) {
  currentLocation.value = { lat: coords.latitude, lng: coords.longitude }
  accuracyMeters.value = coords.accuracy || null
  permissionDenied.value = false

  if (destination.value) {
    distanceMeters.value = haversine(coords.latitude, coords.longitude, destination.value.lat, destination.value.lng)
    if (distanceMeters.value <= 200 && !hasAnnouncedArrival.value) {
      track('nav_arrived_geofence', { job_id: jobId.value })
      hasAnnouncedArrival.value = true
    }
  }

  const now = Date.now()
  if (sessionId.value && online.value && now - lastPingAt.value >= navTickMs.value) {
    lastPingAt.value = now
    sendPing(coords)
  }

  if (now - lastEtaAt.value >= 15000) {
    lastEtaAt.value = now
    refreshEta(coords)
  }
}

async function startEnrouteSession() {
  const { data } = await axios.post('/api/tracking/session', {
    job_id: jobId.value,
    phase: 'enroute',
  })
  sessionId.value = data.session_id
}

async function closeSession(reason) {
  if (!sessionId.value) return
  try {
    await axios.post('/api/tracking/session/close', {
      session_id: sessionId.value,
      reason,
    })
  } catch (error) {
    console.warn('close session failed', error)
  }
}

async function sendPing(coords) {
  if (!sessionId.value) return
  try {
    await axios.post('/api/tracking/ping', {
      session_id: sessionId.value,
      lat: coords.latitude,
      lng: coords.longitude,
      accuracy: coords.accuracy,
      ts: new Date().toISOString(),
    })
    track('nav_tick', {
      job_id: jobId.value,
      distance_m: Math.round(distanceMeters.value ?? 0),
      eta_min: etaMinutes.value,
      gps_accuracy_m: Math.round(coords.accuracy ?? 0),
    })
  } catch (error) {
    if (!online.value) {
      navigationStatus.value = 'Offline—updates will sync when back online.'
    }
  }
}

async function refreshEta(coords = currentLocation.value) {
  if (!coords || !jobId.value) return
  try {
    const { data } = await axios.get(`/api/eta/${jobId.value}`, {
      params: { fromLat: coords.lat ?? coords.latitude, fromLng: coords.lng ?? coords.longitude },
    })
    etaMinutes.value = data.eta_minutes
    distanceMeters.value = data.distance_m
    routeSummary.value = `${data.eta_minutes} min · ${formatDistance(data.distance_m)}`
    navigationStatus.value = 'Navigation running. Keep this screen open.'
  } catch (error) {
    console.warn('ETA error', error)
    navigationStatus.value = 'Unable to pull ETA right now.'
  }
}

async function handleStartJob() {
  if (!startEnabled.value && !overrideArmed.value) {
    if (!confirm('You are still far away. Start anyway and notify dispatch?')) {
      return
    }
    overrideArmed.value = true
  }
  startingJob.value = true
  track('nav_start_job_pressed', { job_id: jobId.value, override: !showArrivedBanner.value })
  try {
    await axios.post(`/api/jobs/${jobId.value}/start`, { idempotency_key: randomId() })
    await closeSession('job_started')
    window.location.assign(`/cleaner/jobs/${jobId.value}/start`)
  } catch (error) {
    console.error('start job failed', error)
  } finally {
    startingJob.value = false
  }
}

async function handleCancel() {
  if (!confirm('Stop navigating to this job?')) {
    return
  }
  track('nav_canceled', { job_id: jobId.value })
  await closeSession('navigation_canceled')
  window.location.assign('/cleaner/today')
}

watch(destination, value => {
  if (!value && job.value) {
    navigationStatus.value = 'Job has no map location. Contact manager.'
  }
})

onMounted(() => {
  window.addEventListener('online', onlineHandler)
  window.addEventListener('offline', offlineHandler)
  document.addEventListener('visibilitychange', visibilityHandler)
  bootstrapNavigation()
})

onBeforeUnmount(() => {
  stopWatch()
  window.removeEventListener('online', onlineHandler)
  window.removeEventListener('offline', offlineHandler)
  document.removeEventListener('visibilitychange', visibilityHandler)
})

watch(job, () => {
  bootstrapNavigation()
})

function formatDistance(meters, compact = false) {
  if (!meters || Number.isNaN(meters)) return '—'
  if (meters >= 1000) {
    const value = meters / 1000
    return compact ? `${value.toFixed(1)} km` : `${value.toFixed(1)} km`
  }
  return `${Math.round(meters)} m`
}

function formatArriveBy(windowValue) {
  if (!windowValue || typeof windowValue !== 'string') return '—'
  const start = windowValue.split('-')[0]?.trim()
  if (!start) return '—'
  const [hours, minutes] = start.split(':').map(Number)
  if (!Number.isFinite(hours) || !Number.isFinite(minutes)) return start
  const date = new Date()
  date.setHours(hours, minutes, 0, 0)
  return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
}

function haversine(lat1, lon1, lat2, lon2) {
  const R = 6371000
  const dLat = deg2rad(lat2 - lat1)
  const dLon = deg2rad(lon2 - lon1)
  const a = Math.sin(dLat / 2) ** 2 + Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLon / 2) ** 2
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  return R * c
}

const deg2rad = value => (value * Math.PI) / 180
const randomId = () => (crypto?.randomUUID ? crypto.randomUUID() : Math.random().toString(36).slice(2))
</script>

<style scoped>
.section-head { margin-bottom: 12px; }
.section-head h2 { margin: 0 0 6px; font-size: 20px; }
.muted { color: #475467; margin: 0; }
.nav-shell { display: flex; flex-direction: column; gap: 16px; }
.chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
.chip { background: #0f172a; color: #fff; border-radius: 999px; padding: 6px 14px; font-size: 13px; font-weight: 600; }
.permission-card { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 16px; background: #fff7ed; }
.permission-card h3 { margin: 0 0 6px; font-size: 18px; }
.permission-card p { margin: 0 0 12px; color: #92400e; }
.permission-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.map-card { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; overflow: hidden; background: #0f172a; color: #fff; }
.map-head { display: flex; gap: 12px; padding: 18px 20px; align-items: center; border-bottom: 1px solid rgba(255,255,255,.12); }
.live-dot { width: 12px; height: 12px; border-radius: 50%; background: #4fe1c1; box-shadow: 0 0 12px rgba(79,225,193,.8); animation: pulse 1.6s infinite; }
.map-title { margin: 0; font-weight: 600; }
.map-desc { margin: 2px 0 0; color: rgba(255,255,255,.72); font-size: 13px; }
.map-canvas :deep(.cleaner-map__canvas), .map-canvas.cleaner-map__canvas { height: 320px; }
.nav-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; padding: 14px 20px; background: rgba(6,12,24,.55); }
.metric-label { margin: 0; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.7); }
.metric-value { margin: 4px 0 0; font-size: 18px; font-weight: 600; }
.action-card { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 18px; background: #fff; display: flex; flex-direction: column; gap: 12px; }
.arrival-banner { border-radius: 16px; padding: 12px 14px; background: #ecfdf5; color: #065f46; display: flex; flex-direction: column; gap: 4px; }
.arrival-banner--soft { background: #eef2ff; color: #1d4ed8; }
.buttons { display: flex; flex-wrap: wrap; gap: 10px; }
.btn { border-radius: 999px; padding: 12px 18px; font-weight: 600; border: none; cursor: pointer; }
.btn-primary { background: #4fe1c1; color: #062f25; }
.btn-secondary { background: #0f172a; color: #fff; }
.btn-ghost { background: transparent; color: #475467; border: 1px solid rgba(15,23,42,.14); }
.btn:disabled { opacity: .5; cursor: not-allowed; }
.helper { margin: 0; font-size: 13px; color: #94a3b8; }
.status-line { margin: 0; font-size: 13px; color: #475467; }
.spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; margin-right: 8px; animation: spin 1s linear infinite; display: inline-block; }
.empty { border: 1px dashed rgba(15,23,42,.2); border-radius: 16px; padding: 24px; text-align: center; color: #475467; background: #f8fafc; }
@media (min-width: 1024px) {
  .map-canvas :deep(.cleaner-map__canvas), .map-canvas.cleaner-map__canvas { height: 400px; }
}
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes pulse {
  0% { transform: scale(.9); opacity: .8; }
  50% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(.9); opacity: .8; }
}
</style>
