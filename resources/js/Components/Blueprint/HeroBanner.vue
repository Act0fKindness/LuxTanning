<template>
<section
  class="hero-banner"
  :class="[
    { 'hero-banner--full': fullBleed },
    hero.heroClass || null,
  ]"
>
    <div class="hero-glow" aria-hidden="true"></div>
    <div class="hero-inner" :class="{ 'hero-inner--full': fullBleed }">
      <div class="hero-grid">
        <div class="hero-copy" v-if="showCopy">
        <p v-if="hero.badge || hero.eyebrow" class="hero-eyebrow">
          <span v-if="hero.badge" class="pill">{{ hero.badge }}</span>
          <span>{{ hero.eyebrow }}</span>
        </p>
        <h1>{{ hero.headline }}</h1>
        <p v-if="hero.subhead" class="hero-subhead">{{ hero.subhead }}</p>
        <p
          v-for="(paragraph, index) in paragraphs"
          :key="index"
          class="hero-body"
        >
          {{ paragraph }}
        </p>
        <div class="hero-actions" v-if="primaryActions.length">
          <component
            v-for="action in primaryActions"
            :key="action.label"
            :is="action.href ? 'a' : 'button'"
            class="btn"
            :class="['btn-' + (action.variant || 'primary')]"
            :href="action.href"
            :type="action.href ? null : 'button'"
          >
            <i v-if="action.icon" :class="['bi', action.icon, 'me-2']"></i>
            {{ action.label }}
          </component>
        </div>
        <div class="hero-microcopy" v-if="microCopy.length">
          <span v-for="(item, index) in microCopy" :key="item" class="micro-item">
            {{ item }}<span v-if="index < microCopy.length - 1"> · </span>
          </span>
        </div>
        <div class="hero-utility" v-if="utilityActions.length">
          <span class="utility-label">Need something quick?</span>
          <div class="utility-links">
            <a
              v-for="action in utilityActions"
              :key="action.label"
              class="utility-link"
              :href="action.href"
            >
              <i v-if="action.icon" :class="['bi', action.icon]"></i>
              {{ action.label }}
            </a>
          </div>
        </div>
      </div>
        <div class="hero-panel" :class="{ 'hero-panel--map': showMap }">
          <template v-if="showMap">
            <div class="hero-map-shell">
              <div class="hero-map" ref="mapEl"></div>
            </div>
          </template>
          <template v-else>
            <div class="panel-card" v-for="highlight in highlights" :key="highlight.title">
              <p class="panel-label">{{ highlight.title }}</p>
              <p class="panel-copy">{{ highlight.description }}</p>
            </div>
            <div v-if="stat" class="panel-stat">
              <p class="stat-label">{{ stat.label }}</p>
              <p class="stat-value">{{ stat.value }}</p>
              <p class="stat-meta">{{ stat.meta }}</p>
            </div>
          </template>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { Loader } from '@googlemaps/js-api-loader'

const props = defineProps({
  hero: { type: Object, required: true },
  fallbackActions: { type: Array, default: () => [] },
  fullBleed: { type: Boolean, default: false },
  googleKey: { type: String, default: '' },
  mapData: { type: [Array, Object], default: null },
})

const fullBleed = computed(() => props.fullBleed)
const paragraphs = computed(() => {
  if (!props.hero.body) return []
  return Array.isArray(props.hero.body) ? props.hero.body : [props.hero.body]
})

const showCopy = computed(() => props.hero.hideCopy !== true)
const microCopy = computed(() => props.hero.microCopy || [])
const highlights = computed(() => props.hero.highlights || [])
const stat = computed(() => props.hero.stat || null)
const mapJobs = computed(() => {
  if (Array.isArray(props.mapData)) return props.mapData
  if (props.mapData && Array.isArray(props.mapData.jobs)) return props.mapData.jobs
  return []
})
const validMapJobs = computed(() => mapJobs.value.filter(job => Number.isFinite(Number(job.lat)) && Number.isFinite(Number(job.lng))))
const showMap = computed(() => Boolean(props.googleKey && validMapJobs.value.length))
const mapEl = ref(null)
let mapLoader = null
let mapInstance = null
let mapMarkers = []

const primaryActions = computed(() => {
  const actions = []
  if (props.hero.primaryAction) actions.push({ ...props.hero.primaryAction, variant: props.hero.primaryAction.variant || 'primary' })
  if (props.hero.secondaryAction) actions.push({ ...props.hero.secondaryAction, variant: props.hero.secondaryAction.variant || 'ghost' })
  return actions.length ? actions : props.fallbackActions
})

const utilityActions = computed(() => {
  if (props.hero.utilityActions) return props.hero.utilityActions
  if (!props.hero.primaryAction && !props.hero.secondaryAction) return []
  return props.fallbackActions
})

const initMap = async () => {
  if (!showMap.value || !mapEl.value) return
  if (!mapLoader) {
    mapLoader = new Loader({ apiKey: props.googleKey, version: 'weekly' })
  }

  if (!mapInstance) {
    try {
      const { Map } = await mapLoader.importLibrary('maps')
      mapInstance = new Map(mapEl.value, {
        center: defaultCenter(),
        zoom: 12,
        disableDefaultUI: false,
      })
    } catch (error) {
      console.warn('[hero-map] load failed', error)
      return
    }
  }

  renderMarkers()
}

const defaultCenter = () => {
  const job = validMapJobs.value[0]
  if (!job) return { lat: 51.5072, lng: -0.1276 }
  return { lat: Number(job.lat), lng: Number(job.lng) }
}

const renderMarkers = () => {
  if (!mapInstance || !window.google) return
  mapMarkers.forEach(marker => marker.setMap(null))
  mapMarkers = []
  const { maps } = window.google
  validMapJobs.value.forEach(job => {
    const marker = new maps.Marker({
      position: { lat: Number(job.lat), lng: Number(job.lng) },
      map: mapInstance,
      title: job.customer || 'Job',
    })
    mapMarkers.push(marker)
  })

  if (mapMarkers.length) {
    const pos = mapMarkers[0].getPosition()
    if (pos) {
      mapInstance.setCenter(pos)
    }
  }
}

const destroyMap = () => {
  mapMarkers.forEach(marker => marker.setMap(null))
  mapMarkers = []
  mapInstance = null
  mapLoader = null
}

watch(showMap, value => {
  if (value) {
    initMap()
  } else {
    destroyMap()
  }
})

watch(validMapJobs, () => {
  if (showMap.value) {
    if (!mapInstance) {
      initMap()
    } else {
      renderMarkers()
    }
  }
})

onMounted(() => {
  if (showMap.value) {
    initMap()
  }
})

onBeforeUnmount(() => {
  destroyMap()
})
</script>

<style scoped>
.hero-banner {
  position: relative;
  border-radius: 32px;
  padding: 48px;
  background: radial-gradient(circle at 20% 20%, rgba(104, 237, 203, 0.25), transparent 55%),
    radial-gradient(circle at 80% 0%, rgba(107, 165, 255, 0.3), transparent 45%),
    linear-gradient(135deg, #030711, #0d152d 55%, #0b0f1a);
  color: #f7fbff;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.08);
  max-width: 1100px;
  margin: 0 auto;
}

.hero-banner--full {
  border-radius: 0;
  padding: 48px 0;
  max-width: none;
}

.hero-glow {
  position: absolute;
  inset: 12px;
  border-radius: 28px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  pointer-events: none;
}

.hero-banner--full .hero-glow {
  inset: 0;
  border-radius: 0;
}

.hero-inner {
  position: relative;
  z-index: 1;
}

.hero-inner--full {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 clamp(16px, 4vw, 48px);
}

.hero-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 32px;
}

.hero-copy h1 {
  font-size: clamp(36px, 5vw, 52px);
  margin-bottom: 12px;
  letter-spacing: -0.01em;
}

.hero-eyebrow {
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.25em;
  color: rgba(255, 255, 255, 0.72);
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.hero-eyebrow .pill {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  background: rgba(79, 225, 193, 0.14);
  color: #6dfedc;
  border-radius: 999px;
  letter-spacing: 0.08em;
}

.hero-subhead {
  font-size: 18px;
  line-height: 1.6;
  color: rgba(247, 251, 255, 0.85);
  margin-bottom: 14px;
}

.hero-body {
  color: rgba(255, 255, 255, 0.78);
  line-height: 1.7;
  margin-bottom: 10px;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin: 24px 0 12px;
}

.btn {
  border-radius: 999px;
  padding: 12px 22px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  font-size: 15px;
  border: none;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(120deg, #66ffd6, #35cfff);
  color: #061b16;
  box-shadow: 0 12px 25px rgba(102, 255, 214, 0.35);
}

.btn-ghost {
  background: rgba(255, 255, 255, 0.08);
  color: #f7fbff;
  border: 1px solid rgba(255, 255, 255, 0.25);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.16);
  color: #fff;
}

.hero-microcopy {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.65);
}

.hero-utility {
  margin-top: 18px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.utility-label {
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: rgba(255, 255, 255, 0.45);
}

.utility-links {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.utility-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.08);
  color: #f7fbff;
  text-decoration: none;
  font-size: 14px;
}

.hero-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.hero-panel--map { height: 100%; grid-column: 1 / -1; }

.hero-map-shell {
  width: 100%;
  border-radius: 24px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(3, 7, 17, 0.45);
  min-height: 320px;
}

.hero-map { width: 100%; height: 340px; }

.panel-card {
  background: rgba(6, 9, 25, 0.65);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 20px;
  backdrop-filter: blur(12px);
  box-shadow: 0 12px 30px rgba(3, 5, 15, 0.45);
}

.panel-label {
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.25em;
  color: rgba(109, 254, 220, 0.9);
  margin-bottom: 10px;
}

.panel-copy {
  margin: 0;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.8);
}

.panel-stat {
  border-radius: 20px;
  padding: 22px;
  background: linear-gradient(135deg, rgba(109, 254, 220, 0.15), rgba(65, 104, 255, 0.2));
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.stat-label {
  text-transform: uppercase;
  letter-spacing: 0.3em;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.6);
}

.stat-value {
  font-size: 36px;
  margin: 8px 0;
  font-weight: 700;
}

.stat-meta {
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
}

@media (max-width: 640px) {
  .hero-banner {
    padding: 32px 24px;
  }
  .hero-actions {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
.hero-banner--dispatch { padding: 10px; }
