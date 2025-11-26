<template>
  <div class="quote-hero">
    <div class="hero-copy">
      <p class="eyebrow">{{ format(section.badge || 'Instant pricing') }}</p>
      <h2>{{ format(section.title || 'Instant window cleaning quote') }}</h2>
      <p class="lead">{{ format(section.description || 'Pick home size, service cadence, and any extras (frames, gutters, solar) to see live pricing exactly how a lead would. No spreadsheets, no surprises.') }}</p>
      <ul class="hero-points">
        <li v-for="point in heroPoints" :key="point">
          <i class="bi bi-check2"></i>
          <span>{{ point }}</span>
        </li>
      </ul>
      <div class="hero-links" v-if="!hideHeroLinks">
        <a class="btn primary" href="/book">Start a booking</a>
        <a class="btn ghost" href="/status">View status</a>
      </div>
    </div>

    <div class="generator-card">
      <div class="generator-layout">
        <div class="generator-left">
          <header class="generator-head">
            <div>
              <p class="eyebrow">Instant pricing</p>
              <h3>Send me this plan</h3>
            </div>
            <span class="live-pill"><i class="bi bi-lightning-charge me-1"></i>Live demo</span>
          </header>

          <section class="street-view" v-if="addressCoords">
            <header class="street-view-head">
              <div>
                <p class="eyebrow">Street view capture</p>
                <p class="muted small">Auto-generated once the service address is confirmed.</p>
              </div>
              <a
                v-if="!hideStreetMeta && googleMapsLink && !showMapView && streetViewImages.length && hasHouseNumber"
                :href="googleMapsLink"
                target="_blank"
                rel="noopener"
                class="btn ghost mini"
              >
                Open in Maps
              </a>
            </header>
            <div v-if="!showMapView && streetViewImages.length && hasHouseNumber" class="street-view-layout">
              <figure v-if="frontImage" class="street-view-front">
                <img :src="frontImage.url" alt="Street view front" loading="lazy" />
              </figure>
              <div class="street-view-sides">
                <figure v-for="image in sideImages" :key="image.key">
                  <img :src="image.url" :alt="`Street view ${image.label}`" loading="lazy" />
                </figure>
              </div>
            </div>
            <div v-else class="street-view-map">
              <img v-if="staticMapUrl" :src="staticMapUrl" alt="Google map preview" loading="lazy" />
              <p v-else class="muted small">Add a full address to preview the map.</p>
            </div>
            <div class="street-view-actions" v-if="!hideStreetMeta">
              <button
                type="button"
                class="btn ghost mini"
                :disabled="!streetViewImages.length || !hasHouseNumber"
                @click="toggleMapView"
              >
                {{ showMapView || !streetViewImages.length || !hasHouseNumber ? 'Show images' : 'View map' }}
              </button>
              <button
                type="button"
                class="btn ghost mini"
                :disabled="!googleMapsLink"
                @click="reportImageIssue"
              >
                These images are wrong
              </button>
            </div>
            <p class="muted small" v-if="!hideStreetMeta">{{ streetViewStatus }}</p>
            <p v-if="streetViewError" class="address-error">{{ streetViewError }}</p>
          </section>

          <form class="generator-controls" @submit.prevent>
            <div class="accordion">
              <section class="accordion-item" :class="{ open: isOpen('address') }">
                <button type="button" class="accordion-toggle" @click="toggleSection('address')" :aria-expanded="isOpen('address')">
                  <span>Service address</span>
                  <i class="bi" :class="isOpen('address') ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div class="accordion-body" v-show="isOpen('address')">
                  <label class="field emphasized">
                    <div class="field-label">Service address</div>
                    <div class="address-input">
                      <i class="bi bi-geo-alt"></i>
                      <input
                        ref="addressInput"
                        type="text"
                        class="address-field"
                        placeholder="Start typing postcode or street"
                        v-model="addressDisplay"
                      />
                      <button v-if="addressDisplay" type="button" class="clear-btn" @click="clearAddress" aria-label="Clear address">
                        <i class="bi bi-x"></i>
                      </button>
                    </div>
                    <p v-if="addressError" class="address-error">{{ addressError }}</p>
                    <p v-else-if="addressMeta" class="address-meta">{{ addressMeta }}</p>
                  </label>
                </div>
              </section>

              <section class="accordion-item" :class="{ open: isOpen('windows') }">
                <button type="button" class="accordion-toggle" @click="toggleSection('windows')" :aria-expanded="isOpen('windows')">
                  <span>Window count</span>
                  <i class="bi" :class="isOpen('windows') ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div class="accordion-body" v-show="isOpen('windows')">
                  <label class="field">
                    <div class="field-label slider-label">
                      <span>Window count</span>
                      <strong>{{ windowLabel }}</strong>
                    </div>
                    <div class="range-track">
                      <input
                        class="window-range"
                        type="range"
                        :min="windowRange.min"
                        :max="windowRange.max"
                        step="1"
                        v-model.number="windowCount"
                      />
                      <div class="range-labels">
                        <span
                          v-for="mark in windowMarks"
                          :key="mark.value"
                          :class="{ 'is-start': mark.isStart, 'is-end': mark.isEnd }"
                          :style="{ left: `${mark.position}%` }"
                        >
                          {{ mark.value }}
                        </span>
                      </div>
                    </div>
                  </label>
                </div>
              </section>

              <section class="accordion-item" :class="{ open: isOpen('cadence') }">
                <button type="button" class="accordion-toggle" @click="toggleSection('cadence')" :aria-expanded="isOpen('cadence')">
                  <span>Cadence</span>
                  <i class="bi" :class="isOpen('cadence') ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div class="accordion-body" v-show="isOpen('cadence')">
                  <label class="field">
                    <span class="field-label">Cadence</span>
                    <div class="pill-row">
                      <button
                        v-for="option in frequencyOptions"
                        :key="option.value"
                        type="button"
                        class="pill"
                        :class="{ active: option.value === frequency }"
                        @click="frequency = option.value"
                      >
                        <strong>{{ option.label }}</strong>
                        <small>{{ option.meta }}</small>
                      </button>
                    </div>
                  </label>
                </div>
              </section>

              <section class="accordion-item" :class="{ open: isOpen('size') }">
                <button type="button" class="accordion-toggle" @click="toggleSection('size')" :aria-expanded="isOpen('size')">
                  <span>Property size</span>
                  <i class="bi" :class="isOpen('size') ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div class="accordion-body" v-show="isOpen('size')">
                  <label class="field">
                    <span class="field-label">Property size</span>
                    <div class="pill-row compact">
                      <button
                        v-for="option in sizeOptions"
                        :key="option.value"
                        type="button"
                        class="pill"
                        :class="{ active: option.value === size }"
                        @click="size = option.value"
                      >
                        <strong>{{ option.label }}</strong>
                        <small>{{ option.meta }}</small>
                      </button>
                    </div>
                  </label>
                </div>
              </section>

              <section class="accordion-item" :class="{ open: isOpen('addons') }">
                <button type="button" class="accordion-toggle" @click="toggleSection('addons')" :aria-expanded="isOpen('addons')">
                  <span>Add-ons</span>
                  <i class="bi" :class="isOpen('addons') ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div class="accordion-body" v-show="isOpen('addons')">
                  <div class="field">
                    <span class="field-label">Add-ons</span>
                    <div class="addon-grid">
                      <label v-for="addon in addOnOptions" :key="addon.key" class="addon">
                        <input type="checkbox" :value="addon.key" v-model="selectedAddOns">
                        <div class="addon-content">
                          <div class="addon-info">
                            <p>{{ addon.label }}</p>
                            <small>{{ addon.description }}</small>
                          </div>
                          <span class="price">+{{ formatCurrency(addon.cost) }}</span>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>
              </section>
            </div>
          </form>
        </div>

        <aside class="estimate-panel">
          <p class="eyebrow">Estimate</p>
          <h4>{{ formatCurrency(totalPerVisit) }} per visit</h4>
          <p class="muted">Includes {{ hoursDisplay }} hours, {{ addOnCopy }}, and live dispatch availability.</p>

          <dl class="summary-list">
            <div>
              <dt>Plan</dt>
              <dd>{{ plan.label }}</dd>
            </div>
            <div>
              <dt>Time estimate</dt>
              <dd>{{ timeEstimate }}</dd>
            </div>
            <div>
              <dt>Monthly</dt>
              <dd>{{ formatCurrency(monthlyTotal) }}</dd>
            </div>
            <div>
              <dt>Next slot</dt>
              <dd>{{ plan.nextSlot }}</dd>
            </div>
            <div>
              <dt>Saving vs one-off</dt>
              <dd :class="{ positive: savings > 0 }">{{ savings > 0 ? '-' + formatCurrency(savings) : '—' }}</dd>
            </div>
          </dl>

          <ul class="line-items">
            <li>
              <span>{{ sizeLabel }}</span>
              <strong>{{ formatCurrency(baseRate[size]) }}</strong>
            </li>
            <li>
              <span>Windows ({{ windowCount }})</span>
              <strong>{{ windowUpcharge >= 0 ? '+' : '-' }}{{ formatCurrency(Math.abs(windowUpcharge)) }}</strong>
            </li>
            <li>
              <span>Add-ons</span>
              <strong>{{ selectedAddOns.length ? '+' + formatCurrency(addOnTotals.cost) : formatCurrency(0) }}</strong>
            </li>
            <li>
              <span>Plan multiplier</span>
              <strong>{{ Math.round(plan.multiplier * 100) }}%</strong>
            </li>
          </ul>

          <button type="button" class="btn primary full">
            <i class="bi bi-send me-1"></i>
            Send me this plan
          </button>
          <p class="footnote">Estimate shown is indicative only and may change if property details are inaccurate, access is obstructed, or scope is mis-evaluated.</p>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { formatText } from '../../utils/contextFormatter'
import { loadGoogleMaps } from '../../utils/googleMaps'

const props = defineProps({
  section: { type: Object, required: true },
  context: { type: Object, default: () => ({}) },
  googleKey: { type: String, default: '' },
})

const format = text => formatText(text, props.context)

const heroPoints = [
  'Quote exterior + interior glass in seconds.',
  'Upsell frames, gutters, or conservatory panes with live maths.',
  'Hand-off straight into the booking flow once approved.',
]

const sectionConfig = computed(() => props.section?.props || {})
const hideHeroLinks = computed(() => !!sectionConfig.value.hideHeroLinks)
const hideStreetMeta = computed(() => !!sectionConfig.value.hideStreetMeta)
const hasHouseNumber = computed(() => Boolean(addressParts.value?.number))

const DEFAULT_PITCH = 0
const SEARCH_RADIUS = 75
const BFS_MAX_DEPTH = 4
const BFS_MAX_NODES = 40
const SIDE_MAX_DIST_M = 60
const ANGLE_MIN = 15
const ANGLE_MAX = 160
const SAMPLE_DIST_M = 20
const SAMPLE_BEARINGS = [0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330]
const DEC_SELECTED = 6
const DEC_SIDES = 5

const toRad = deg => (deg * Math.PI) / 180
const toDeg = rad => (rad * 180) / Math.PI
const normalizeHeading = value => (value % 360 + 360) % 360
const signedDelta = (a, b) => {
  const delta = normalizeHeading(a - b)
  return delta > 180 ? delta - 360 : delta
}

const bearing = (fromLL, toLL) => {
  const phi1 = toRad(fromLL.lat())
  const phi2 = toRad(toLL.lat())
  const deltaLambda = toRad(toLL.lng() - fromLL.lng())
  const y = Math.sin(deltaLambda) * Math.cos(phi2)
  const x = Math.cos(phi1) * Math.sin(phi2) - Math.sin(phi1) * Math.cos(phi2) * Math.cos(deltaLambda)
  return normalizeHeading(toDeg(Math.atan2(y, x)))
}

const haversineMeters = (a, b) => {
  const R = 6378137
  const dPhi = toRad(b.lat() - a.lat())
  const dLambda = toRad(b.lng() - a.lng())
  const phi1 = toRad(a.lat())
  const phi2 = toRad(b.lat())
  const s = Math.sin(dPhi / 2) ** 2 + Math.cos(phi1) * Math.cos(phi2) * Math.sin(dLambda / 2) ** 2
  return 2 * R * Math.asin(Math.min(1, Math.sqrt(s)))
}

const offsetLatLng = (originLL, distM, headingDeg) => {
  const R = 6378137
  const delta = distM / R
  const theta = toRad(headingDeg)
  const phi1 = toRad(originLL.lat())
  const lambda1 = toRad(originLL.lng())
  const sinPhi2 = Math.sin(phi1) * Math.cos(delta) + Math.cos(phi1) * Math.sin(delta) * Math.cos(theta)
  const phi2 = Math.asin(sinPhi2)
  const lambda2 = lambda1 + Math.atan2(
    Math.sin(theta) * Math.sin(delta) * Math.cos(phi1),
    Math.cos(delta) - Math.sin(phi1) * Math.sin(phi2),
  )
  return new window.google.maps.LatLng(toDeg(phi2), toDeg(lambda2))
}

const canonicalStreet = raw => {
  if (!raw) return ''
  let text = raw.toLowerCase().replace(/,\s*(united kingdom|uk|england)$/g, '').replace(/\./g, '')
  const replacements = {
    ' close ': ' cl ',
    ' road ': ' rd ',
    ' street ': ' st ',
    ' avenue ': ' ave ',
    ' lane ': ' ln ',
    ' drive ': ' dr ',
    ' crescent ': ' cres ',
    ' court ': ' ct ',
    ' way ': ' way ',
    ' grove ': ' gr ',
    ' place ': ' pl ',
    ' square ': ' sq ',
    ' terrace ': ' ter ',
    ' mews ': ' mews ',
    ' rise ': ' rise ',
    ' hill ': ' hill ',
  }
  Object.keys(replacements).forEach(key => {
    text = text.replace(new RegExp(key, 'g'), replacements[key])
  })
  return text.replace(/\s+/g, ' ').trim()
}

const parseAddressParts = raw => {
  const text = (raw || '').trim()
  const match = text.match(/^\s*([0-9]+[a-z]?)\s+([^,]+)/i)
  const number = match ? match[1].toLowerCase() : ''
  const street = match ? canonicalStreet(match[2]) : canonicalStreet(text.split(',')[0] || '')
  return { number, street, raw: text }
}

const sameHouse = (a, b) => Boolean(a.number && b.number && a.number === b.number && a.street === b.street)
const sameStreet = (a, b) => Boolean(a.street && b.street && a.street === b.street)

const accordionState = ref({
  address: true,
  windows: true,
  cadence: true,
  size: false,
  addons: false,
})

const isOpen = key => accordionState.value[key]
const toggleSection = key => { accordionState.value[key] = !accordionState.value[key] }

const sizeOptions = [
  { value: 1, label: 'Compact flat', meta: 'Up to 6 windows' },
  { value: 2, label: 'Standard', meta: '8-10 windows' },
  { value: 3, label: 'Corner / semi', meta: '12-16 windows' },
  { value: 4, label: 'Townhouse wrap', meta: '18-22 windows' },
  { value: 5, label: 'Large property', meta: '24+ windows' },
]

const windowRange = { min: 2, max: 24 }
const windowStops = Array.from({ length: ((windowRange.max - windowRange.min) / 2) + 1 }, (_, idx) => windowRange.min + idx * 2)
const windowMarks = computed(() => windowStops.map(value => ({
  value,
  position: ((value - windowRange.min) / (windowRange.max - windowRange.min)) * 100,
  isStart: value === windowRange.min,
  isEnd: value === windowRange.max,
})))

const frequencyOptions = [
  { value: 'once', label: 'One-off', meta: 'Perfect trial' },
  { value: 'six-week', label: '6-week', meta: '-3% loyalty' },
  { value: 'fortnightly', label: 'Fortnightly', meta: 'Most popular' },
  { value: 'weekly', label: 'Weekly', meta: '-12% autopilot' },
]

const frequencyMap = {
  once: { label: 'One-off clean', multiplier: 1, perMonth: 1, nextSlot: 'Thu · 09:00-11:00' },
  'six-week': { label: 'Every 6 weeks', multiplier: 0.97, perMonth: 0.66, nextSlot: 'Mon · 13:00-15:00' },
  fortnightly: { label: 'Every 2 weeks', multiplier: 0.93, perMonth: 2, nextSlot: 'Wed · 10:00-12:00' },
  weekly: { label: 'Weekly plan', multiplier: 0.88, perMonth: 4, nextSlot: 'Fri · 08:00-10:00' },
}

const addOnOptions = [
  { key: 'frames', label: 'Frames & sills detail', description: 'Hand-detail all uPVC/wood frames plus sills', cost: 15, hours: 0.2 },
  { key: 'conservatory', label: 'Conservatory roof', description: 'Pure water rinse + pole brush roof panels', cost: 30, hours: 0.6 },
  { key: 'gutters', label: 'Gutter clear & flush', description: 'Vacuum debris + rinse 10m run', cost: 28, hours: 0.45 },
  { key: 'solar', label: 'Solar panel rinse', description: 'Pure-water wash for up to 8 panels', cost: 24, hours: 0.4 },
]

const baseRate = { 1: 24, 2: 30, 3: 38, 4: 48, 5: 62 }
const baseHours = { 1: 0.9, 2: 1.1, 3: 1.5, 4: 2, 5: 2.6 }

const size = ref(2)
const windowCount = ref(8)
const frequency = ref('fortnightly')
const selectedAddOns = ref([])
const addressInput = ref(null)
const addressDisplay = ref('')
const addressMeta = ref('We’ll confirm exact travel once booked.')
const addressCoords = ref(null)
const addressError = ref('')
const addressParts = ref(null)
let autocomplete = null
let placesLoaded = false
let streetViewService = null
let streetGeocoder = null

const streetViewImages = ref([])
const streetViewStatus = ref('Add a service address to preview Street View captures.')
const streetViewError = ref('')
const streetViewLoading = ref(false)
const showMapView = ref(false)

const frontImage = computed(() => {
  const images = streetViewImages.value
  if (!images.length) return null
  return images.find(image => image.key === 'front') || images[0]
})

const sideImages = computed(() => {
  const images = streetViewImages.value
  if (!images.length) return []
  const frontKey = frontImage.value?.key
  return images.filter(image => image.key !== frontKey)
})

const googleMapsLink = computed(() => {
  if (!addressCoords.value) return ''
  const { lat, lng } = addressCoords.value
  return `https://www.google.com/maps/@${lat},${lng},18z`
})

const staticMapUrl = computed(() => {
  if (!addressCoords.value || !props.googleKey) return ''
  const { lat, lng } = addressCoords.value
  const params = new URLSearchParams({
    center: `${lat},${lng}`,
    zoom: '18',
    size: '640x320',
    maptype: 'roadmap',
    markers: `color:0x0fa087|${lat},${lng}`,
    key: props.googleKey,
  })
  return `https://maps.googleapis.com/maps/api/staticmap?${params.toString()}`
})

const panoCache = new Map()

const panoUrlById = (panoId, heading, pitch = DEFAULT_PITCH, fov = 80) => {
  const params = new URLSearchParams({
    size: '640x320',
    pano: panoId,
    heading: Math.round(normalizeHeading(heading)),
    pitch: Math.round(pitch),
    fov: String(fov),
    key: props.googleKey,
    source: 'outdoor',
  })
  return `https://maps.googleapis.com/maps/api/streetview?${params.toString()}`
}

const nearestPano = (latLng, radius = SEARCH_RADIUS) => new Promise((resolve, reject) => {
  streetViewService.getPanorama(
    { location: latLng, radius, preference: window.google.maps.StreetViewPreference.NEAREST },
    (data, status) => {
      if (status === window.google.maps.StreetViewStatus.OK && data) resolve(data)
      else reject(new Error('No Street View found near that address.'))
    },
  )
})

const panoById = panoId => new Promise((resolve, reject) => {
  streetViewService.getPanorama({ pano: panoId }, (data, status) => {
    if (status === window.google.maps.StreetViewStatus.OK && data) resolve(data)
    else reject(new Error('Street View pano lookup failed.'))
  })
})

const reverseGeocode = async latLng => {
  try {
    const result = await streetGeocoder.geocode({ location: latLng })
    return result?.results?.[0]?.formatted_address || ''
  } catch (error) {
    console.warn('Reverse geocode failed', error)
    return ''
  }
}

const panoAddress = async pano => {
  const panoId = pano?.location?.pano
  if (!panoId) return ''
  if (panoCache.has(panoId)) return panoCache.get(panoId)
  const fallback = pano.location?.description || ''
  const addr = fallback && fallback.length > 5 ? fallback : await reverseGeocode(pano.location.latLng)
  panoCache.set(panoId, addr)
  return addr
}

const dedupePanos = panos => {
  const seen = new Set()
  return panos.filter(pano => {
    const id = pano?.location?.pano
    if (!id || seen.has(id)) return false
    seen.add(id)
    return true
  })
}

const exploreAround = async (centre, targetLL) => {
  const queue = [centre]
  const out = []
  const seen = new Set()

  while (queue.length && out.length < BFS_MAX_NODES) {
    const pano = queue.shift()
    const panoId = pano?.location?.pano
    if (!panoId || seen.has(panoId)) continue
    seen.add(panoId)
    out.push(pano)
    const depth = pano.__depth || 0
    if (depth >= BFS_MAX_DEPTH) continue

    const desiredHeading = bearing(pano.location.latLng, targetLL)
    const sortedLinks = (pano.links || []).slice().sort((a, b) => {
      const da = Math.abs(signedDelta(a.heading || 0, desiredHeading))
      const db = Math.abs(signedDelta(b.heading || 0, desiredHeading))
      return da - db
    })

    for (const link of sortedLinks) {
      if (!link.pano) continue
      try {
        const next = await panoById(link.pano)
        next.__depth = depth + 1
        if (haversineMeters(next.location.latLng, targetLL) <= SIDE_MAX_DIST_M * 2.6) {
          queue.push(next)
        }
      } catch (error) {
        console.warn('Link pano failed', error)
      }
    }
  }

  return out
}

const radialSamples = async targetLL => {
  const panos = []
  for (const bearingDeg of SAMPLE_BEARINGS) {
    const samplePoint = offsetLatLng(targetLL, SAMPLE_DIST_M, bearingDeg)
    try {
      const pano = await nearestPano(samplePoint, 60)
      if (!panos.some(existing => existing.location?.pano === pano.location?.pano)) {
        panos.push(pano)
      }
    } catch (error) {
      /* ignore */
    }
  }
  return panos
}

const getLinkedPanos = async pano => {
  const results = []
  for (const link of pano.links || []) {
    if (!link.pano) continue
    try {
      const linked = await panoById(link.pano)
      results.push(linked)
    } catch (error) {
      /* ignore */
    }
  }
  return results
}

const pickImmediateSidesByTargetGeometry = (neighbors, targetLL, baseFrontLL) => {
  const theta0 = bearing(baseFrontLL, targetLL)
  const scored = neighbors.map(p => {
    const camLL = p.location.latLng
    const thetaI = bearing(camLL, targetLL)
    const delta = signedDelta(thetaI, theta0)
    const dist = haversineMeters(camLL, targetLL)
    return { pano: p, heading: thetaI, delta, dist }
  })
  const left = scored
    .filter(entry => entry.delta > 0)
    .sort((a, b) => Math.abs(a.delta) - Math.abs(b.delta) || a.dist - b.dist)[0]
  const right = scored
    .filter(entry => entry.delta < 0)
    .sort((a, b) => Math.abs(a.delta) - Math.abs(b.delta) || a.dist - b.dist)[0]
  return {
    left: left ? { pano: left.pano, heading: left.heading } : null,
    right: right ? { pano: right.pano, heading: right.heading } : null,
  }
}

const pickSideCandidatesByTargetGeometry = (candidates, targetLL, baseFrontLL) => {
  const theta0 = bearing(baseFrontLL, targetLL)
  const scored = candidates
    .map(pano => {
      const camLL = pano.location.latLng
      const thetaI = bearing(camLL, targetLL)
      const delta = signedDelta(thetaI, theta0)
      const dist = haversineMeters(camLL, targetLL)
      return { pano, heading: thetaI, delta, dist }
    })
    .filter(entry => entry.dist <= SIDE_MAX_DIST_M)

  const sortSide = ideal => (a, b) => {
    const aScore = Math.abs(Math.abs(a.delta) - ideal) * 2 + a.dist / 25
    const bScore = Math.abs(Math.abs(b.delta) - ideal) * 2 + b.dist / 25
    return aScore - bScore
  }

  const left = scored
    .filter(entry => entry.delta > ANGLE_MIN && entry.delta < ANGLE_MAX)
    .sort(sortSide(90))[0]
  const right = scored
    .filter(entry => entry.delta < -ANGLE_MIN && entry.delta > -ANGLE_MAX)
    .sort(sortSide(90))[0]
  return {
    left: left ? { pano: left.pano, heading: left.heading } : null,
    right: right ? { pano: right.pano, heading: right.heading } : null,
  }
}

const chooseFrontBasePano = async (unique, centre, selectedParts) => {
  const centreAddress = await panoAddress(centre)
  const centreParts = parseAddressParts(centreAddress)
  if (sameHouse(centreParts, selectedParts)) {
    return { pano: centre, matched: 'full', camAddr: centreAddress }
  }
  for (const pano of unique) {
    if (pano.location?.pano === centre.location?.pano) continue
    const addr = await panoAddress(pano)
    const parts = parseAddressParts(addr)
    if (sameHouse(parts, selectedParts)) {
      return { pano, matched: 'full', camAddr: addr }
    }
  }
  for (const pano of unique) {
    if (pano.location?.pano === centre.location?.pano) continue
    const addr = await panoAddress(pano)
    const parts = parseAddressParts(addr)
    if (sameStreet(parts, selectedParts)) {
      return { pano, matched: 'street', camAddr: addr }
    }
  }
  return { pano: centre, matched: 'nearest', camAddr: centreAddress }
}

const buildStreetViewSet = async (frontPano, targetLL, unique) => {
  const frontHeading = bearing(frontPano.location.latLng, targetLL)
  const neighbors = await getLinkedPanos(frontPano)
  let picks = pickImmediateSidesByTargetGeometry(neighbors, targetLL, frontPano.location.latLng)
  if (!picks.left || !picks.right) {
    const pool = unique.filter(pano => pano.location?.pano !== frontPano.location?.pano)
    const fallback = pickSideCandidatesByTargetGeometry(pool, targetLL, frontPano.location.latLng)
    picks = {
      left: picks.left || fallback.left,
      right: picks.right || fallback.right,
    }
  }
  const leftPano = picks.left?.pano || frontPano
  const rightPano = picks.right?.pano || frontPano
  const leftHeading = picks.left?.heading ?? frontHeading
  const rightHeading = picks.right?.heading ?? frontHeading

  const [frontAddr, leftAddr, rightAddr] = await Promise.all([
    panoAddress(frontPano),
    panoAddress(leftPano),
    panoAddress(rightPano),
  ])

  return [
    {
      key: 'left',
      label: 'Left',
      pano: leftPano,
      heading: leftHeading,
      pitch: 0,
      fov: 80,
      meta: leftAddr || `${leftPano.location.latLng.lat().toFixed(DEC_SIDES)}, ${leftPano.location.latLng.lng().toFixed(DEC_SIDES)}`,
    },
    {
      key: 'front',
      label: 'Front',
      pano: frontPano,
      heading: frontHeading,
      pitch: DEFAULT_PITCH,
      fov: 100,
      meta: frontAddr || (addressDisplay.value || ''),
    },
    {
      key: 'right',
      label: 'Right',
      pano: rightPano,
      heading: rightHeading,
      pitch: 0,
      fov: 80,
      meta: rightAddr || `${rightPano.location.latLng.lat().toFixed(DEC_SIDES)}, ${rightPano.location.latLng.lng().toFixed(DEC_SIDES)}`,
    },
  ]
}

const generateStreetViewImages = async () => {
  if (!streetViewService || !addressCoords.value) return
  streetViewLoading.value = true
  streetViewError.value = ''
  streetViewStatus.value = 'Resolving…'
  streetViewImages.value = []
  panoCache.clear()

  try {
    const targetLL = new window.google.maps.LatLng(addressCoords.value.lat, addressCoords.value.lng)
    const centre = await nearestPano(targetLL)
    const [viaLinks, radial] = await Promise.all([exploreAround(centre, targetLL), radialSamples(targetLL)])
    const unique = dedupePanos([centre, ...viaLinks, ...radial])
    const selectedParts = addressParts.value || parseAddressParts(addressDisplay.value)
    const chosen = await chooseFrontBasePano(unique, centre, selectedParts)
    const images = await buildStreetViewSet(chosen.pano, targetLL, unique)
    streetViewImages.value = images.map(item => ({
      key: item.key,
      label: item.label,
      url: panoUrlById(item.pano.location.pano, item.heading, item.pitch, item.fov || 80),
      meta: item.meta,
    }))
    streetViewStatus.value = chosen.matched === 'full'
      ? 'Address matched (full)'
      : chosen.matched === 'street'
        ? 'Street matched'
        : 'Nearest pano used'
  } catch (error) {
    console.warn('Street View generation failed', error)
    streetViewImages.value = []
    streetViewError.value = error.message || 'Unable to load Street View for this address.'
    streetViewStatus.value = streetViewError.value
  } finally {
    streetViewLoading.value = false
  }
}

const addOnTotals = computed(() => selectedAddOns.value.reduce((acc, key) => {
  const addon = addOnOptions.find(a => a.key === key)
  if (!addon) return acc
  return { cost: acc.cost + addon.cost, hours: acc.hours + addon.hours }
}, { cost: 0, hours: 0 }))

const perWindowRate = 3
const perWindowHours = 0.05
const sizeWindowBaseline = { 1: 6, 2: 8, 3: 14, 4: 20, 5: 24 }

const windowLabel = computed(() => `${windowCount.value} ${windowCount.value === 1 ? 'window' : 'windows'}`)
const windowBaseline = computed(() => sizeWindowBaseline[size.value] || 8)
const windowDelta = computed(() => windowCount.value - windowBaseline.value)
const windowUpcharge = computed(() => Math.round(windowDelta.value * perWindowRate))
const windowHourContribution = computed(() => Number((windowDelta.value * perWindowHours).toFixed(2)))

const subtotal = computed(() => Math.max(18, baseRate[size.value] + addOnTotals.value.cost + windowUpcharge.value))
const hours = computed(() => baseHours[size.value] + addOnTotals.value.hours + windowHourContribution.value)
const hoursDisplay = computed(() => {
  const value = hours.value
  return Number.isInteger(value) ? String(value) : value.toFixed(1).replace(/\.0$/, '')
})
const plan = computed(() => frequencyMap[frequency.value])
const totalPerVisit = computed(() => Math.round(subtotal.value * plan.value.multiplier))
const monthlyTotal = computed(() => Math.round(totalPerVisit.value * plan.value.perMonth))
const savings = computed(() => Math.max(0, subtotal.value - totalPerVisit.value))
const sizeLabel = computed(() => sizeOptions.find(o => o.value === size.value)?.label || '')
const addOnCopy = computed(() => {
  const count = selectedAddOns.value.length
  if (count === 0) return 'no add-ons'
  if (count === 1) return '1 add-on'
  return `${count} add-ons`
})
const formatDuration = totalHours => {
  const totalMinutes = Math.max(1, Math.round(totalHours * 60))
  const hrs = Math.floor(totalMinutes / 60)
  const mins = totalMinutes % 60
  if (!hrs) return `${mins} min${mins === 1 ? '' : 's'}`
  const hrLabel = `${hrs} hr${hrs === 1 ? '' : 's'}`
  const minLabel = mins ? ` ${mins} min${mins === 1 ? '' : 's'}` : ''
  return hrLabel + minLabel
}

const timeEstimate = computed(() => `${formatDuration(hours.value)} onsite`)

const currencyFormatter = new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP', maximumFractionDigits: 0 })
const formatCurrency = value => currencyFormatter.format(value)

const clearAddress = () => {
  addressDisplay.value = ''
  addressCoords.value = null
  addressError.value = ''
  addressMeta.value = 'We’ll confirm exact travel once booked.'
  addressParts.value = null
}

const initAutocomplete = async () => {
  if (!props.googleKey || !addressInput.value || autocomplete) return
  try {
    await loadGoogleMaps(props.googleKey, ['places'])
    placesLoaded = true
    streetViewService = new window.google.maps.StreetViewService()
    streetGeocoder = new window.google.maps.Geocoder()
    const options = { fields: ['formatted_address', 'geometry', 'name', 'address_components'], componentRestrictions: { country: ['gb', 'ie'] } }
    autocomplete = new window.google.maps.places.Autocomplete(addressInput.value, options)
    autocomplete.addListener('place_changed', () => {
      const place = autocomplete.getPlace()
      if (!place || !place.geometry || !place.geometry.location) {
        addressError.value = 'Could not find that address. Try adding the postcode.'
        return
      }
      addressError.value = ''
      const formatted = place.formatted_address || place.name || ''
      addressDisplay.value = formatted
      addressParts.value = parseAddressParts(formatted)
      addressCoords.value = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng(),
      }
      const postcodeComponent = (place.address_components || []).find(comp => comp.types.includes('postal_code'))
      const postcode = postcodeComponent ? postcodeComponent.long_name : ''
      addressMeta.value = [formatted, postcode].filter(Boolean).join(' · ')
    })
  } catch (error) {
    console.warn('Failed to load Google Places', error)
    addressError.value = 'Address lookup unavailable right now.'
  }
}

onMounted(() => {
  initAutocomplete()
})

onBeforeUnmount(() => {
  if (autocomplete && placesLoaded && window.google) {
    window.google.maps.event.clearInstanceListeners(autocomplete)
  }
  autocomplete = null
})

watch(addressCoords, coords => {
  if (coords && streetViewService && hasHouseNumber.value) {
    showMapView.value = false
    generateStreetViewImages()
  } else {
    streetViewImages.value = []
    streetViewStatus.value = hasHouseNumber.value
      ? 'Add a service address to preview Street View captures.'
      : 'Add the house name/number to enable Street View.'
    streetViewError.value = ''
    showMapView.value = true
  }
})

const toggleMapView = () => {
  if (!streetViewImages.value.length || !hasHouseNumber.value) {
    showMapView.value = true
    return
  }
  showMapView.value = !showMapView.value
}

const reportImageIssue = () => {
  if (!googleMapsLink.value) return
  window.open(googleMapsLink.value, '_blank', 'noopener')
}
</script>

<style scoped>
.quote-hero {
  display: flex;
  flex-direction: column;
  gap: clamp(1.5rem, 4vw, 3rem);
  padding: clamp(1.5rem, 4vw, 3rem);
  border-radius: 32px;
  border: 1px solid rgba(79,225,193,.35);
  background: linear-gradient(120deg, rgba(79,225,193,.25), rgba(79,225,193,.05)), #f6fffb;
  color: #032b25;
  box-shadow: 0 40px 90px rgba(6,48,44,.18);
  scroll-margin-top: calc(var(--nav-h) + 40px);
}

.hero-copy { display: flex; flex-direction: column; gap: 1rem; }
.eyebrow { text-transform: uppercase; letter-spacing: .35em; font-size: .7rem; color: rgba(5,82,70,.7); margin: 0; }
.lead { margin: 0; color: #2f3c38; font-size: 1.05rem; max-width: 540px; }
.hero-copy h2 { margin: 0; font-size: clamp(2rem, 4vw, 3rem); color: #032b25; }
.hero-points { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: .65rem; }
.hero-points li { display: inline-flex; align-items: center; gap: .5rem; font-weight: 600; color: #054b3f; }
.hero-points i { color: #0fb89b; }
.hero-links { display: flex; flex-wrap: wrap; gap: .75rem; }
.btn { border-radius: 999px; font-weight: 600; padding: .85rem 1.4rem; border: 1px solid transparent; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: transform .2s ease; }
.btn.primary { background: #4fe1c1; color: #052b21; box-shadow: 0 14px 30px rgba(79,225,193,.3); }
.btn.ghost { border-color: rgba(5,82,70,.25); color: #055b4a; background: rgba(79,225,193,.12); }
.btn.mini { padding: .5rem .9rem; font-size: .8rem; }
.btn:hover { transform: translateY(-2px); }

.generator-card { background: #ffffff; border-radius: 24px; border: 1px solid rgba(4,64,55,.08); padding: 1.1rem; box-shadow: 0 20px 40px rgba(5,70,60,.07); }
.generator-layout { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(260px, 0.9fr); gap: clamp(1rem, 3vw, 2rem); align-items: flex-start; }
.generator-left { display: flex; flex-direction: column; gap: 1rem; }
.generator-head { display: flex; justify-content: space-between; align-items: center; }
.generator-head h3 { margin: 6px 0 0; color: #042b24; }
.live-pill { border-radius: 999px; padding: .35rem .9rem; border: 1px solid rgba(5,84,70,.18); font-size: .8rem; display: inline-flex; align-items: center; gap: .25rem; color: #0fb89b; background: rgba(15,184,155,.12); }
.generator-controls { display: flex; flex-direction: column; gap: .9rem; }
.accordion { display: flex; flex-direction: column; gap: .8rem; }
.accordion-item { border: 1px solid rgba(6,70,60,.12); border-radius: 20px; background: #f5fffb; overflow: hidden; }
.accordion-item.open { box-shadow: 0 10px 30px rgba(3,43,36,.08); border-color: rgba(15,184,155,.35); }
.accordion-toggle { width: 100%; border: none; background: transparent; padding: .95rem 1.1rem; display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: .95rem; color: #073a31; cursor: pointer; }
.accordion-item.open .accordion-toggle { background: rgba(15,184,155,.08); }
.accordion-body { padding: 0 1.1rem 1.2rem; border-top: 1px solid rgba(6,70,60,.08); }
.accordion-body .field { margin-top: 1rem; }
.street-view { border: 1px solid rgba(6,70,60,.12); border-radius: 20px; padding: 1rem 1.2rem; background: #f9fffd; display: flex; flex-direction: column; gap: 1rem; }
.street-view--placeholder { align-items: center; text-align: center; }
.street-view-head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
.street-view-head .eyebrow { margin: 0; }
.street-view-head .muted.small { margin: 2px 0 0; font-size: .8rem; }
.street-view-layout { display: grid; grid-template-columns: 2fr 1fr; gap: .9rem; align-items: stretch; }
.street-view-front, .street-view-sides figure, .street-view-map img { margin: 0; border-radius: 16px; overflow: hidden; border: 1px solid rgba(6,70,60,.08); background: #fff; box-shadow: 0 8px 20px rgba(5,70,60,.07); }
.street-view-front img, .street-view-sides img, .street-view-map img { width: 100%; height: 100%; display: block; object-fit: cover; }
.street-view-front { min-height: 260px; }
.street-view-sides { display: flex; flex-direction: column; gap: .9rem; }
.street-view-sides figure { flex: 1; min-height: 120px; }
.street-view-map { display: flex; align-items: center; justify-content: center; min-height: 240px; }
.street-view-actions { display: flex; gap: .75rem; flex-wrap: wrap; }

@media (max-width: 640px) {
  .street-view-layout { grid-template-columns: 1fr; }
  .street-view-sides { flex-direction: row; }
  .street-view-sides figure { min-height: 140px; }
}
.field { display: flex; flex-direction: column; gap: .5rem; }
.field.emphasized { padding: .2rem 0 .4rem; border-bottom: 1px solid rgba(15,23,42,.08); }
.field-label { font-weight: 600; font-size: .9rem; color: #0f3e36; }
.slider-label { display: flex; justify-content: space-between; align-items: center; gap: .75rem; }
.slider-label strong { font-size: 1rem; color: #032b25; }
.pill-row { display: flex; flex-wrap: wrap; gap: .55rem; }
.pill-row.compact .pill { flex: 1 1 calc(33% - .55rem); min-width: 135px; }
.range-track { position: relative; padding: 0 9px; }
.window-range { width: 100%; display: block; margin: 0; box-sizing: border-box; accent-color: #0fb89b; }
.window-range::-webkit-slider-thumb { appearance: none; width: 18px; height: 18px; border-radius: 50%; background: #0fb89b; box-shadow: 0 0 0 4px rgba(15,184,155,.35); cursor: pointer; }
.window-range::-moz-range-thumb { width: 18px; height: 18px; border-radius: 50%; background: #0fb89b; box-shadow: 0 0 0 4px rgba(15,184,155,.35); cursor: pointer; }
.range-labels { position: relative; height: 18px; margin-top: .35rem; font-size: .7rem; color: #6b7f78; width: calc(100% - 16px); margin-left: 8px; box-sizing: border-box; }
.range-labels span { position: absolute; transform: translateX(-50%); top: 0; white-space: nowrap; }
.range-labels span.is-start { transform: translateX(0); }
.range-labels span.is-end { transform: translateX(-100%); }
.address-input { display: flex; align-items: center; gap: .5rem; border: 1px solid rgba(6,70,60,.12); border-radius: 16px; padding: .65rem .85rem; background: #fdfdfb; }
.address-input i { color: #0fb89b; }
.address-field { flex: 1; border: none; background: transparent; font-size: .95rem; outline: none; color: #032b25; }
.clear-btn { border: none; background: transparent; color: #6b7f78; cursor: pointer; padding: 0; }
.clear-btn:hover { color: #0fb89b; }
.address-error { margin: 0; color: #b42318; font-size: .8rem; }
.address-meta { margin: 0; color: #4b6a62; font-size: .8rem; }
.pill { border-radius: 16px; border: 1px solid rgba(6,70,60,.12); padding: .75rem 1rem; background: #f3fffb; color: #032b25; flex: 1 1 140px; text-align: left; cursor: pointer; }
.pill small { display: block; font-weight: 400; color: #546b63; }
.pill.active { border-color: #0fb89b; background: rgba(15,184,155,.15); box-shadow: 0 6px 18px rgba(15,184,155,.2); }
.addon-grid { display: flex; flex-direction: column; gap: .65rem; width: 100%; }
.addon { border: 1px solid rgba(6,70,60,.12); border-radius: 18px; padding: .85rem .95rem; display: flex; gap: .8rem; align-items: flex-start; background: #ffffff; color: #042b24; width: 100%; }
.addon input { accent-color: #0fb89b; margin: .1rem 0 0; }
.addon-content { display: flex; justify-content: space-between; align-items: center; gap: 1rem; width: 100%; flex-wrap: wrap; }
.addon-info { flex: 1 1 auto; min-width: 220px; }
.addon-info p { margin: 0; font-weight: 600; }
.addon-info small { display: block; color: #6b7f78; margin-top: .2rem; }
.price { font-weight: 700; color: #032b25; white-space: nowrap; }

.estimate-panel { border-radius: 20px; border: 1px solid rgba(15,184,155,.25); padding: 1.25rem; background: linear-gradient(135deg, rgba(79,225,193,.35), rgba(255,255,255,.9)); }
.estimate-panel h4 { margin: 6px 0 8px; font-size: 1.8rem; color: #042b24; }
.muted { color: #3e5a53; margin: 0 0 1rem; }
.summary-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem 1rem; margin: 0 0 1rem; }
.summary-list dt { font-size: .68rem; letter-spacing: .18em; text-transform: uppercase; color: #3a5951; }
.summary-list dd { margin: 0; font-weight: 600; color: #032b25; }
.summary-list dd.positive { color: #0fb89b; }
.line-items { list-style: none; margin: 0 0 1rem; padding: 1rem 0; border-top: 1px solid rgba(6,70,60,.1); border-bottom: 1px solid rgba(6,70,60,.1); display: flex; flex-direction: column; gap: .5rem; }
.line-items li { display: flex; justify-content: space-between; color: #0d3c33; font-size: .95rem; }
.btn.full { width: 100%; justify-content: center; }
.footnote { font-size: .75rem; color: #4d6a63; margin: .35rem 0 0; }

@media (max-width: 1024px) {
  .generator-layout { grid-template-columns: 1fr; }
}
</style>
