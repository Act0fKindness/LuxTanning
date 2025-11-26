<template>
  <div class="platform-jobs-panel">
    <header class="panel-header">
      <div>
        <div class="title">Live Jobs</div>
        <div class="subtitle">Platform-wide activity</div>
      </div>
      <button class="btn btn-ghost btn-xs" type="button" @click="toggleMap" v-if="mapsKey">
        <i class="bi" :class="mapExpanded ? 'bi-map' : 'bi-map-fill'"></i>
        <span class="ms-1">{{ mapExpanded ? 'Hide map' : 'Show map' }}</span>
      </button>
    </header>

    <div class="filter-stack">
      <div class="form-group position-relative">
        <label class="form-label small text-muted">Search</label>
        <input type="search" class="form-control form-control-sm" v-model.trim="textFilter" placeholder="Company, address, postcode" />
      </div>
      <div class="form-group position-relative" v-if="mapsKey">
        <label class="form-label small text-muted">Location</label>
        <div class="location-input">
          <input type="search" class="form-control form-control-sm" ref="gmapsInput" :value="locationLabel" placeholder="Filter by town or postcode" @focus="setupAutocomplete" readonly />
          <button type="button" class="clear-btn" v-if="locationFilter" @click="clearLocation" aria-label="Clear location"><i class="bi bi-x"></i></button>
        </div>
        <div class="radius-selector" v-if="locationFilter">
          <label class="small text-muted">Radius</label>
          <select class="form-select form-select-sm" v-model.number="radiusKm">
            <option :value="5">5 km</option>
            <option :value="10">10 km</option>
            <option :value="25">25 km</option>
            <option :value="50">50 km</option>
            <option :value="100">100 km</option>
          </select>
        </div>
      </div>
      <label class="form-check small">
        <input class="form-check-input" type="checkbox" v-model="onlyActiveNow" />
        <span class="form-check-label">Show only jobs in progress</span>
      </label>
    </div>

    <transition name="fade">
      <div class="map-wrapper" v-show="mapExpanded && mapsKey">
        <div ref="map" class="map-canvas"></div>
        <div class="map-footnote" v-if="!hasMappableJobs">
          <small class="text-muted">Jobs need lat/lng to appear on the map.</small>
        </div>
      </div>
    </transition>

    <div v-if="isListMode ? listSections.length : displayGroups.length">
      <div v-if="isListMode" class="list-wrapper">
        <section class="list-section" v-for="section in listSections" :key="section.key">
          <header class="list-section-header">
            <h5 class="section-title mb-0">{{ section.label }}</h5>
            <div class="text-muted small">{{ section.count }} job{{ section.count === 1 ? '' : 's' }}</div>
          </header>
          <section class="list-group" v-for="group in section.groups" :key="`${section.key}-${group.tenant}`">
            <header class="list-group-header">
              <div class="tenant-name">{{ group.tenant }}</div>
              <div class="text-muted small">{{ group.jobs.length }} job{{ group.jobs.length === 1 ? '' : 's' }}</div>
            </header>
            <div class="list-jobs">
              <article
                v-for="job in group.jobs"
                :key="job.id"
                class="job-card"
                :class="{ 'active-now': job.isActiveNow }"
              >
                <div class="job-card-main">
                  <div class="job-card-info">
                    <div class="status-row">
                      <span class="status-pill">{{ job.statusLabel }}</span>
                      <span class="completion-chip" :class="job.isCompleted ? 'completed' : 'pending'">{{ job.completionLabel }}</span>
                    </div>
                    <div class="address">{{ job.addressLabel }}</div>
                    <div class="meta text-muted small">
                      <span v-if="job.tenant_name">{{ job.tenant_name }}</span>
                      <span v-if="job.priceLabel" class="ms-2">{{ job.priceLabel }}</span>
                      <span v-if="job.address?.postcode" class="ms-2">{{ job.address.postcode }}</span>
                    </div>
                  </div>
                  <div class="job-card-time">
                    <span class="time">{{ job.windowLabel }}</span>
                    <span class="date" v-if="job.dateLabel">{{ job.dateLabel }}</span>
                  </div>
                </div>
                <div class="job-progress" v-if="job.progressPosition">
                  <div class="job-progress-track">
                    <div class="job-progress-line" :style="{ left: job.progressPosition }"></div>
                  </div>
                </div>
              </article>
            </div>
          </section>
        </section>
      </div>
      <div v-else class="timeline-wrapper">
        <div class="timeline-range-labels">
          <span>{{ formatMinutes(timelineRange.start) }}</span>
          <span>{{ formatMinutes(timelineRange.end) }}</span>
        </div>
        <div class="timeline-global-track">
          <div class="timeline-now" :style="currentLineStyle"></div>
        </div>
        <div class="job-groups">
          <section class="job-group" v-for="group in displayGroups" :key="group.tenant">
            <header class="group-header">
              <div>
                <div class="tenant-name">{{ group.tenant }}</div>
                <div class="text-muted small">{{ group.jobs.length }} job{{ group.jobs.length === 1 ? '' : 's' }}</div>
              </div>
            </header>
            <div class="group-timeline">
              <div class="timeline-track">
                <div class="timeline-now" :style="currentLineStyle"></div>
                <article
                  v-for="job in group.jobs"
                  :key="job.id"
                  class="timeline-job"
                  :class="{ 'active-now': job.isActiveNow }"
                  :style="job.timelineStyle"
                >
                  <div class="time">{{ job.windowLabel }}</div>
                  <div class="address">{{ job.addressLabel }}</div>
                  <div class="meta">{{ job.dateLabel ? job.dateLabel + ' • ' : '' }}{{ job.statusLabel }} • {{ job.completionLabel }}</div>
                </article>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
    <div class="empty-state" v-else>
      <i class="bi bi-calendar-x"></i>
      <p>No jobs match the current filters.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlatformJobsPanel',
  props: {
    jobs: { type: Array, default: () => [] },
    mapsKey: { type: String, default: '' },
    layout: { type: String, default: 'timeline' }
  },
  data() {
    return {
      textFilter: '',
      onlyActiveNow: false,
      locationFilter: null,
      radiusKm: 25,
      map: null,
      mapExpanded: true,
      markers: [],
      markerPulses: [],
      autocomplete: null,
      hasMappableJobs: false,
      currentMinutes: this.nowMinutes(),
      mapInitialised: false,
      geocoder: null,
      geocodeCache: {},
      openInfoWindow: null
    }
  },
  computed: {
    isListMode() {
      return this.layout === 'list'
    },
    todayKey() {
      const now = new Date()
      const month = String(now.getMonth() + 1).padStart(2, '0')
      const day = String(now.getDate()).padStart(2, '0')
      return `${now.getFullYear()}-${month}-${day}`
    },
    recentCompletedCutoffKey() {
      const now = new Date()
      now.setDate(now.getDate() - 3)
      const month = String(now.getMonth() + 1).padStart(2, '0')
      const day = String(now.getDate()).padStart(2, '0')
      return `${now.getFullYear()}-${month}-${day}`
    },
    locationLabel() {
      if (!this.locationFilter) return ''
      return this.locationFilter.description || `${this.locationFilter.lat.toFixed(4)}, ${this.locationFilter.lng.toFixed(4)}`
    },
    processedJobs() {
      const base = Array.isArray(this.jobs) ? this.jobs : []
      return base.map(job => {
        const startMinutes = this.normaliseMinutes(job.start_minutes, job.eta_window)
        const endMinutes = this.normaliseMinutes(job.end_minutes, job.eta_window, true, startMinutes)
        const rawAddress = job.address || {}
        const lat = rawAddress.lat !== undefined && rawAddress.lat !== null && rawAddress.lat !== '' ? Number(rawAddress.lat) : null
        const lng = rawAddress.lng !== undefined && rawAddress.lng !== null && rawAddress.lng !== '' ? Number(rawAddress.lng) : null
        const addressKey = [rawAddress.line1, rawAddress.postcode, job.id].filter(Boolean).join('|')
        const cached = addressKey ? (this.geocodeCache[addressKey] || null) : null
        const addrLat = lat ?? cached?.lat ?? null
        const addrLng = lng ?? cached?.lng ?? null
        const address = { ...rawAddress, lat: addrLat, lng: addrLng, _key: addressKey }
        const addressLabel = [address.line1, address.city, address.postcode].filter(Boolean).join(', ')
        const statusRaw = String(job.status || 'scheduled')
        const statusLabel = statusRaw.replace(/_/g, ' ')
        const pricePence = typeof job.price_pence === 'number' ? job.price_pence : Number(job.price_pence || 0)
        const priceLabel = pricePence ? `£${(pricePence / 100).toFixed(2)}` : null
        const dateLabel = job.date ? this.formatDateLabel(job.date) : null
        const isCompleted = statusRaw === 'completed'
        return {
          ...job,
          startMinutes,
          endMinutes,
          address,
          addressLabel: addressLabel || 'Address TBC',
          windowLabel: job.eta_window || 'Time TBC',
          statusLabel: statusLabel.charAt(0).toUpperCase() + statusLabel.slice(1),
          priceLabel,
          dateLabel,
          isCompleted,
          completionLabel: isCompleted ? 'Completed' : 'Not complete',
          hasCoords: typeof addrLat === 'number' && !Number.isNaN(addrLat) && typeof addrLng === 'number' && !Number.isNaN(addrLng)
        }
      })
    },
    filteredJobs() {
      let list = this.processedJobs

      if (this.textFilter) {
        const needle = this.textFilter.trim().toLowerCase()
        list = list.filter(job => {
          const haystack = [job.tenant_name, job.addressLabel, job.windowLabel, job.statusLabel, job.priceLabel]
            .filter(Boolean)
            .join(' ')
            .toLowerCase()
          return haystack.includes(needle)
        })
      }

      if (this.onlyActiveNow) {
        const now = this.currentMinutes
        list = list.filter(job => this.isWithinWindow(job.startMinutes, job.endMinutes, now))
      }

      if (this.locationFilter) {
        list = list
          .map(job => ({
            ...job,
            distanceKm: job.hasCoords ? this.distanceKm(job.address.lat, job.address.lng, this.locationFilter.lat, this.locationFilter.lng) : null
          }))
          .filter(job => job.distanceKm !== null && job.distanceKm <= this.radiusKm)
      }

      return list.sort((a, b) => (a.startMinutes ?? 1440) - (b.startMinutes ?? 1440))
    },
    timelineRange() {
      const source = this.filteredJobs.length ? this.filteredJobs : this.processedJobs
      const defaultStart = 8 * 60
      const defaultEnd = 18 * 60
      const starts = source.map(j => j.startMinutes).filter(v => typeof v === 'number')
      const ends = source.map(j => j.endMinutes).filter(v => typeof v === 'number')
      const min = starts.length ? Math.min(...starts) : defaultStart
      const max = ends.length ? Math.max(...ends) : (starts.length ? Math.max(...starts.map(s => s + 60)) : defaultEnd)
      const span = Math.max(max - min, 120)
      return { start: min, end: min + span, span }
    },
    displayGroups() {
      const groups = new Map()
      const range = this.timelineRange
      const now = this.currentMinutes

      this.filteredJobs.forEach(job => {
        const key = job.tenant_name || 'Unassigned company'
        if (!groups.has(key)) groups.set(key, [])
        const isActiveNow = this.isWithinWindow(job.startMinutes, job.endMinutes, now)
        const timelineStyle = this.timelineStyle(job, range)
        const progressPosition = this.progressPosition(job.startMinutes, job.endMinutes)
        groups.get(key).push({ ...job, isActiveNow, timelineStyle, progressPosition })
      })

      return Array.from(groups.entries())
        .map(([tenant, jobs]) => ({
          tenant,
          jobs: jobs.sort((a, b) => (a.startMinutes ?? 1440) - (b.startMinutes ?? 1440))
        }))
        .sort((a, b) => {
          const aMinutes = a.jobs[0]?.startMinutes ?? 1440
          const bMinutes = b.jobs[0]?.startMinutes ?? 1440
          return aMinutes - bMinutes
        })
    },
    listSections() {
      const todayMap = new Map()
      const upcomingMap = new Map()
      const recentCompletedMap = new Map()
      const now = this.currentMinutes
      const range = this.timelineRange

      const addToMap = (map, tenant, job) => {
        if (!map.has(tenant)) {
          map.set(tenant, [])
        }
        map.get(tenant).push(job)
      }

      this.filteredJobs.forEach(job => {
        const tenantKey = job.tenant_name || 'Unassigned company'
        const isActiveNow = this.isWithinWindow(job.startMinutes, job.endMinutes, now)
        const timelineStyle = this.timelineStyle(job, range)
        const progressPosition = this.progressPosition(job.startMinutes, job.endMinutes)
        const enriched = { ...job, isActiveNow, timelineStyle, progressPosition }
        const jobDate = job.date || ''

        if (jobDate > this.todayKey) {
          addToMap(upcomingMap, tenantKey, enriched)
        } else if (jobDate === this.todayKey) {
          addToMap(todayMap, tenantKey, enriched)
        } else if (job.isCompleted && jobDate && jobDate >= this.recentCompletedCutoffKey) {
          addToMap(recentCompletedMap, tenantKey, enriched)
        }
      })

      const mapToGroups = (map, direction = 'asc') => {
        const groups = Array.from(map.entries())
          .map(([tenant, jobs]) => ({
            tenant,
            jobs: jobs.slice().sort((a, b) => (a.startMinutes ?? 1440) - (b.startMinutes ?? 1440))
          }))

        const compareDates = (aDate, bDate) => {
          if (aDate === bDate) return 0
          if (!aDate) return direction === 'asc' ? 1 : -1
          if (!bDate) return direction === 'asc' ? -1 : 1
          const result = aDate.localeCompare(bDate)
          return direction === 'asc' ? result : -result
        }

        return groups.sort((a, b) => {
          const aDate = a.jobs[0]?.date || ''
          const bDate = b.jobs[0]?.date || ''
          const dateCompare = compareDates(aDate, bDate)
          if (dateCompare !== 0) return dateCompare
          const aMinutes = a.jobs[0]?.startMinutes ?? 1440
          const bMinutes = b.jobs[0]?.startMinutes ?? 1440
          return aMinutes - bMinutes
        })
      }

      const sections = []

      const todayGroups = mapToGroups(todayMap)
      if (todayGroups.length) {
        const todayCount = todayGroups.reduce((total, group) => total + group.jobs.length, 0)
        sections.push({ key: 'today', label: 'Today', groups: todayGroups, count: todayCount })
      }

      const upcomingGroups = mapToGroups(upcomingMap, 'asc')
      if (upcomingGroups.length) {
        const upcomingCount = upcomingGroups.reduce((total, group) => total + group.jobs.length, 0)
        sections.push({ key: 'upcoming', label: 'Upcoming', groups: upcomingGroups, count: upcomingCount })
      }

      const completedGroups = mapToGroups(recentCompletedMap, 'desc')
      if (completedGroups.length) {
        const completedCount = completedGroups.reduce((total, group) => total + group.jobs.length, 0)
        sections.push({ key: 'recentCompleted', label: 'Completed (last 3 days)', groups: completedGroups, count: completedCount })
      }

      return sections
    },
    currentLineStyle() {
      if (this.isListMode) return { left: '0%' }
      const range = this.timelineRange
      const position = this.positionForMinutes(this.currentMinutes, range)
      return { left: position }
    }
  },
  watch: {
    filteredJobs() {
      this.queueMarkerRefresh()
    },
    radiusKm() {
      this.queueMarkerRefresh()
    },
    mapsKey: {
      immediate: true,
      handler(val) {
        if (val && !this.mapInitialised) {
          this.initMap()
        }
      }
    }
  },
  mounted() {
    this._ticker = setInterval(() => { this.currentMinutes = this.nowMinutes() }, 60000)
    if (this.mapsKey) {
      this.initMap()
    }
  },
  beforeUnmount() {
    if (this._ticker) clearInterval(this._ticker)
    this.clearMarkerPulses()
  },
  methods: {
    toggleMap() {
      this.mapExpanded = !this.mapExpanded
      if (this.mapExpanded) {
        this.$nextTick(() => this.queueMarkerRefresh())
      }
    },
    nowMinutes() {
      const d = new Date()
      return d.getHours() * 60 + d.getMinutes()
    },
    formatDateLabel(value) {
      try {
        const d = new Date(value)
        if (Number.isNaN(d.getTime())) return String(value)
        const opts = { weekday: 'short', day: 'numeric', month: 'short' }
        const now = new Date()
        if (d.getFullYear() !== now.getFullYear()) opts.year = 'numeric'
        return d.toLocaleDateString('en-GB', opts)
      } catch (e) {
        return String(value)
      }
    },
    normaliseMinutes(value, window, end = false, fallbackStart = null) {
      if (typeof value === 'number' && !Number.isNaN(value)) return value
      if (!window || !window.includes('-')) {
        if (end && fallbackStart !== null) return fallbackStart + 60
        return end ? null : null
      }
      const [rawStart, rawEnd] = window.split('-').map(v => v.trim())
      const target = end ? rawEnd : rawStart
      if (!target) {
        if (end && fallbackStart !== null) return fallbackStart + 60
        return null
      }
      const [hrs, mins] = target.split(':').map(v => parseInt(v, 10))
      if (Number.isNaN(hrs) || Number.isNaN(mins)) {
        if (end && fallbackStart !== null) return fallbackStart + 60
        return null
      }
      return hrs * 60 + mins
    },
    isWithinWindow(start, end, now) {
      if (typeof start !== 'number') return false
      const windowEnd = typeof end === 'number' ? end : start + 60
      return now >= start && now <= windowEnd
    },
    timelineStyle(job, range) {
      const start = typeof job.startMinutes === 'number' ? job.startMinutes : range.start
      const end = typeof job.endMinutes === 'number' ? job.endMinutes : start + 60
      const safeEnd = Math.max(end, start + 15)
      const leftRatio = (start - range.start) / range.span
      const widthRatio = (safeEnd - start) / range.span
      const left = Math.max(0, Math.min(100, leftRatio * 100))
      const width = Math.max(4, Math.min(100, widthRatio * 100))
      return { left: `${left}%`, width: `${width}%` }
    },
    positionForMinutes(value, range) {
      if (typeof value !== 'number') return '0%'
      const ratio = (value - range.start) / range.span
      const clamped = Math.max(0, Math.min(1, ratio))
      return `${clamped * 100}%`
    },
    formatMinutes(value) {
      if (typeof value !== 'number') return '--:--'
      const hrs = Math.floor(value / 60)
      const mins = value % 60
      return `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`
    },
    distanceKm(lat1, lng1, lat2, lng2) {
      const toRad = deg => deg * (Math.PI / 180)
      const R = 6371
      const dLat = toRad(lat2 - lat1)
      const dLng = toRad(lng2 - lng1)
      const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
      return R * c
    },
    progressPosition(start, end) {
      if (typeof start !== 'number') return null
      const windowEnd = typeof end === 'number' ? end : start + 60
      const span = Math.max(windowEnd - start, 1)
      const pos = ((this.currentMinutes - start) / span) * 100
      const clamped = Math.max(0, Math.min(100, pos))
      return `${clamped}%`
    },
    clearLocation() {
      this.locationFilter = null
      if (this.$refs.gmapsInput) {
        this.$refs.gmapsInput.value = ''
      }
      this.queueMarkerRefresh()
    },
    setupAutocomplete() {
      if (this.autocomplete || !this.mapsKey) return
      if (!window.google || !window.google.maps) return
      const options = { fields: ['name', 'geometry', 'formatted_address'], componentRestrictions: { country: ['gb', 'ie'] } }
      this.autocomplete = new window.google.maps.places.Autocomplete(this.$refs.gmapsInput, options)
      this.autocomplete.addListener('place_changed', () => {
        const place = this.autocomplete.getPlace()
        if (!place || !place.geometry || !place.geometry.location) return
        const lat = place.geometry.location.lat()
        const lng = place.geometry.location.lng()
        this.locationFilter = {
          lat,
          lng,
          description: place.formatted_address || place.name || ''
        }
        this.radiusKm = 25
        this.queueMarkerRefresh(true)
      })
    },
    async initMap() {
      if (!this.mapsKey) return
      await this.ensureGoogle()
      if (!window.google || !this.$refs.map) return
      this.map = new window.google.maps.Map(this.$refs.map, {
        center: { lat: 54.5, lng: -3.5 },
        zoom: 5,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false
      })
      this.geocoder = new window.google.maps.Geocoder()
      this.mapInitialised = true
      this.hasMappableJobs = this.processedJobs.some(job => job.hasCoords)
      this.queueMarkerRefresh(true)
      this.setupAutocomplete()
    },
    ensureGoogle() {
      if (typeof window === 'undefined') return Promise.resolve()
      if (window.google && window.google.maps) return Promise.resolve()
      const key = this.mapsKey
      if (!key) return Promise.resolve()
      const existing = document.getElementById('google-maps-script')
      if (existing) {
        return new Promise(resolve => {
          if (window.google && window.google.maps) return resolve()
          existing.addEventListener('load', () => resolve(), { once: true })
        })
      }
      return new Promise(resolve => {
        const script = document.createElement('script')
        script.id = 'google-maps-script'
        script.src = `https://maps.googleapis.com/maps/api/js?key=${key}&libraries=places`
        script.async = true
        script.defer = true
        script.onload = () => resolve()
        document.head.appendChild(script)
      })
    },
    queueMarkerRefresh(fitBounds = false) {
      if (!this.map) return
      clearTimeout(this._markerTimer)
      this._markerTimer = setTimeout(() => {
        this.refreshMarkers(fitBounds)
      }, 150)
    },
    refreshMarkers(fitBounds = false) {
      if (!this.map) return
      if (this.openInfoWindow) {
        this.openInfoWindow.close()
        this.openInfoWindow = null
      }
      this.markers.forEach(marker => marker.setMap(null))
      this.markers = []
      this.clearMarkerPulses()
      const withCoords = []
      const needsCoords = []
      this.filteredJobs.forEach(job => {
        if (job.hasCoords) withCoords.push(job)
        else needsCoords.push(job)
      })

      if (needsCoords.length && this.geocoder) {
        needsCoords.slice(0, 5).forEach((job, idx) => {
          window.setTimeout(() => this.geocodeJob(job), idx * 200)
        })
      }

      this.hasMappableJobs = withCoords.length > 0
      if (!withCoords.length) return
      const bounds = new window.google.maps.LatLngBounds()
      withCoords.forEach(job => {
        const position = { lat: job.address.lat, lng: job.address.lng }
        const marker = new window.google.maps.Marker({
          map: this.map,
          position,
          title: `${job.tenant_name || 'Company'} • ${job.windowLabel}`,
          icon: this.buildMarkerIcon(job.isActiveNow ? 1.05 : 0.95)
        })
        const info = new window.google.maps.InfoWindow({
          content: this.buildInfoWindow(job),
          maxWidth: 320
        })
        google.maps.event.addListener(info, 'domready', () => this.styleInfoWindow(job.id))
        marker.addListener('click', () => {
          if (this.openInfoWindow && this.openInfoWindow !== info) {
            this.openInfoWindow.close()
          }
          info.open({ anchor: marker, map: this.map })
          this.openInfoWindow = info
        })
        info.addListener('closeclick', () => {
          if (this.openInfoWindow === info) {
            this.openInfoWindow = null
          }
        })
        marker.addListener('mouseover', () => this.handleMarkerHover(marker, job))
        marker.addListener('mouseout', () => this.handleMarkerHoverOut(marker, job))
        this.markers.push(marker)
        bounds.extend(marker.getPosition())
        if (job.isActiveNow) {
          this.startMarkerPulse(marker)
        }
      })
      if (withCoords.length === 1 && (!this.locationFilter || fitBounds)) {
        const target = withCoords[0].address
        this.map.setCenter({ lat: target.lat, lng: target.lng })
        this.map.setZoom(14)
      } else {
        this.map.fitBounds(bounds, { top: 60, right: 60, bottom: 60, left: 60 })
      }
    },
    geocodeJob(job) {
      if (!this.geocoder || !job?.address?._key) return
      const key = job.address._key
      if (this.geocodeCache[key]) return
      const query = job.addressLabel || job.address?.line1 || job.address?.postcode || ''
      if (!query) return
      this.geocoder.geocode({ address: query }, (results, status) => {
        if (status === 'OK' && results && results[0] && results[0].geometry && results[0].geometry.location) {
          const lat = results[0].geometry.location.lat()
          const lng = results[0].geometry.location.lng()
          this.geocodeCache = { ...this.geocodeCache, [key]: { lat, lng } }
          this.queueMarkerRefresh()
        } else if (status === 'OVER_QUERY_LIMIT') {
          window.setTimeout(() => this.geocodeJob(job), 1000)
        } else {
          this.geocodeCache = { ...this.geocodeCache, [key]: { lat: null, lng: null } }
        }
      })
    },
    buildMarkerIcon(scale = 1) {
      const base = 36
      const size = base * scale
      return {
        url: 'https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-drop-pin.png?v=1762253393',
        scaledSize: new window.google.maps.Size(size, size * 1.1),
        anchor: new window.google.maps.Point(size / 2, size * 1.1)
      }
    },
    buildInfoWindow(job) {
      const price = job.priceLabel ? `<div style="font-size:22px;font-weight:700;color:#0b0c0f;">${job.priceLabel}</div>` : ''
      const meta = `${job.dateLabel ? job.dateLabel + ' • ' : ''}${job.windowLabel}`
      return `
        <div class="glint-infowindow" data-job-id="${job.id}" style="max-width:320px;padding:22px 22px;border-radius:0 0 20px 20px;background:#fff;box-shadow:0 22px 48px rgba(15,23,42,0.2);font-family:'Inter','SF Pro Display','Segoe UI',sans-serif;color:#111;">
          ${price}
          <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#6c757d;margin-top:${price ? 6 : 0}px;">${meta}</div>
          <div style="font-size:16px;font-weight:600;margin-top:12px;">${job.tenant_name || 'Company'}</div>
          <div style="font-size:15px;color:#3f4a59;margin-top:6px;">${job.addressLabel}</div>
        </div>
      `
    },
    startMarkerPulse(marker) {
      marker.__hovering = false
      let growing = true
      const pulse = setInterval(() => {
        if (!marker.getIcon || marker.__hovering) return
        const icon = marker.getIcon()
        const nextScale = growing ? 1.25 : 1.05
        marker.setIcon(this.buildMarkerIcon(nextScale))
        growing = !growing
      }, 700)
      this.markerPulses.push(pulse)
    },
    clearMarkerPulses() {
      this.markerPulses.forEach(intervalId => clearInterval(intervalId))
      this.markerPulses = []
    },
    handleMarkerHover(marker, job) {
      marker.__hovering = true
      marker.setIcon(this.buildMarkerIcon(1.35))
    },
    handleMarkerHoverOut(marker, job) {
      marker.__hovering = false
      marker.setIcon(this.buildMarkerIcon(job.isActiveNow ? 1.05 : 0.95))
    },
    styleInfoWindow(jobId) {
      const apply = () => {
        const root = document.querySelector(`.glint-infowindow[data-job-id="${jobId}"]`)
        if (!root) return
        const iwContainer = root.closest('.gm-style-iw')
        const iwParent = iwContainer ? iwContainer.parentElement : null
        const scroller = iwContainer ? iwContainer.querySelector('.gm-style-iw-d') : null

        if (iwParent) {
          iwParent.style.overflow = 'visible'
          iwParent.style.maxHeight = 'none'
          iwParent.style.height = 'auto'
        }
        if (iwContainer) {
          iwContainer.style.padding = '0'
          iwContainer.style.maxWidth = '320px'
          iwContainer.style.maxHeight = 'none'
          iwContainer.style.borderRadius = '0 20px 20px 0'
          iwContainer.style.boxShadow = 'none'
          iwContainer.style.minHeight = '0'
          iwContainer.style.minWidth = '0'
          iwContainer.style.height = 'auto'
        }
        if (scroller) {
          scroller.style.overflow = 'visible'
          scroller.style.maxHeight = 'none'
          scroller.style.height = 'auto'
          scroller.style.padding = '0'
        }
        root.style.maxHeight = 'none'
        root.style.height = 'auto'
      }

      apply()
      setTimeout(apply, 0)
      const header = iwContainer ? iwContainer.previousSibling : null
      const closeBtn = header ? header.querySelector('button.gm-ui-hover-effect') : null
      if (header) {
        header.style.display = 'flex'
        header.style.alignItems = 'center'
        header.style.justifyContent = 'flex-end'
        header.style.padding = '6px'
      }
      if (closeBtn) {
        closeBtn.style.width = '28px'
        closeBtn.style.height = '28px'
        closeBtn.style.background = '#0b0c0f'
        closeBtn.style.borderRadius = '50%'
        closeBtn.style.margin = '0'
        closeBtn.style.opacity = '0.85'
        closeBtn.style.display = 'flex'
        closeBtn.style.alignItems = 'center'
        closeBtn.style.justifyContent = 'center'
        const span = closeBtn.querySelector('span')
        if (span) {
          span.style.maskImage = 'none'
          span.style.background = 'transparent'
          span.innerHTML = '&times;'
          span.style.color = '#fff'
          span.style.fontSize = '18px'
          span.style.lineHeight = '18px'
        }
      }
    }
  }
}
</script>

<style scoped>
.platform-jobs-panel{ display:flex; flex-direction:column; gap:12px; }
.panel-header{ display:flex; align-items:center; justify-content:space-between; }
.panel-header .title{ font-weight:600; font-size:1rem; }
.panel-header .subtitle{ font-size:0.75rem; color:#6c757d; }
.btn.btn-ghost.btn-xs{ padding:4px 8px; font-size:0.75rem; line-height:1; border:1px solid transparent; color:#1b4332; }
.btn.btn-ghost.btn-xs:hover{ border-color:#1b4332; }
.filter-stack{ display:flex; flex-direction:column; gap:8px; }
.filter-stack .form-group{ display:flex; flex-direction:column; gap:4px; }
.form-control-sm{ font-size:0.8rem; border-radius:8px; }
.location-input{ position:relative; }
.location-input input{ padding-right:28px; }
.clear-btn{ position:absolute; top:50%; right:6px; transform:translateY(-50%); background:none; border:none; padding:0; color:#6c757d; }
.clear-btn:hover{ color:#000; }
.radius-selector{ margin-top:6px; display:flex; align-items:center; gap:6px; }
.radius-selector .form-select-sm{ width:auto; border-radius:8px; font-size:0.75rem; padding:2px 8px; }
.form-check-input{ border-radius:4px; margin-right:6px; }
.map-wrapper{ border:1px solid #e0e3eb; border-radius:12px; overflow:hidden; position:relative; }
.map-canvas{ width:100%; height:320px; }
.map-footnote{ padding:6px 10px; background:#f8f9fa; border-top:1px solid #e0e3eb; }
.list-wrapper{ display:flex; flex-direction:column; gap:18px; }
.list-section{ display:flex; flex-direction:column; gap:12px; }
.list-section-header{ display:flex; align-items:center; justify-content:space-between; padding:0 4px; }
.section-title{ font-size:0.95rem; font-weight:600; }
.list-group{ border:1px solid #e0e3eb; border-radius:12px; padding:16px; background:#fff; box-shadow:0 6px 18px rgba(32,55,90,0.08); }
.list-group-header{ display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.list-jobs{ display:flex; flex-direction:column; gap:12px; }
.job-card{ border:1px solid #eef0f5; border-radius:12px; padding:16px 18px; background:#fdfefe; transition:border-color .2s ease, background .2s ease, box-shadow .2s ease; }
.job-card.active-now{ border-color:#1b4332; background:#e6f4ea; box-shadow:0 10px 24px rgba(27,67,50,0.12); }
.job-card-main{ display:flex; align-items:flex-start; justify-content:space-between; gap:20px; }
.job-card-info{ flex:1 1 auto; display:flex; flex-direction:column; gap:8px; }
.status-row{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.status-pill{ display:inline-flex; align-items:center; justify-content:center; padding:4px 12px; border-radius:999px; background:#e6f4ea; color:#0b6240; font-size:0.72rem; font-weight:600; text-transform:capitalize; width:fit-content; }
.job-card.active-now .status-pill{ background:#1b4332; color:#fff; }
.completion-chip{ display:inline-flex; align-items:center; justify-content:center; padding:3px 10px; border-radius:999px; font-size:0.7rem; font-weight:600; letter-spacing:.01em; text-transform:uppercase; }
.completion-chip.completed{ background:#d1f2e0; color:#0b6240; border:1px solid #9edbb9; }
.completion-chip.pending{ background:#fce8e6; color:#b23c17; border:1px solid #f5b1a6; }
.job-card-info .address{ font-size:0.92rem; color:#1f2933; margin:0; }
.job-card-info .meta span{ display:inline-flex; align-items:center; }
.job-card-time{ flex:0 0 auto; display:flex; flex-direction:column; align-items:flex-end; min-width:140px; gap:4px; }
.job-card-time .time{ font-weight:700; font-size:1.45rem; line-height:1; color:#0b1735; }
.job-card-time .date{ font-size:0.8rem; color:#6c757d; }
.job-progress{ margin-top:14px; }
.job-progress-track{ position:relative; height:6px; background:#eef0f5; border-radius:999px; }
.job-card.active-now .job-progress-track{ background:#cbe4d3; }
.job-progress-line{ position:absolute; top:-4px; width:2px; height:16px; background:#1b4332; border-radius:999px; transition:left .3s ease; }
.job-card.active-now .job-progress-line{ background:#0b6240; }
.timeline-wrapper{ display:flex; flex-direction:column; gap:12px; }
.timeline-range-labels{ display:flex; justify-content:space-between; font-size:0.75rem; color:#6c757d; }
.timeline-global-track{ position:relative; height:2px; background:linear-gradient(90deg,#e9ecef,#dee2e6); border-radius:999px; }
.timeline-now{ position:absolute; top:-6px; width:2px; height:14px; background:#1b4332; border-radius:999px; transition:left .2s ease; }
.job-groups{ display:flex; flex-direction:column; gap:12px; }
.job-group{ border:1px solid #e0e3eb; border-radius:12px; padding:10px; background:#fff; box-shadow:0 6px 18px rgba(32,55,90,0.08); }
.group-header{ display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
.tenant-name{ font-weight:600; font-size:0.95rem; }
.group-timeline{ position:relative; }
.timeline-track{ position:relative; padding:12px 0; }
.timeline-track::before{ content:''; position:absolute; top:18px; left:0; right:0; height:2px; background:#f1f3f5; border-radius:999px; }
.timeline-track .timeline-now{ top:6px; height:26px; }
.timeline-job{ position:relative; display:block; padding:8px 10px; background:#f8f9fa; border-radius:8px; border:1px solid transparent; min-width:96px; max-width:100%; }
.timeline-job .time{ font-weight:600; font-size:0.8rem; }
.timeline-job .address{ font-size:0.75rem; color:#495057; margin-top:2px; }
.timeline-job .meta{ font-size:0.7rem; color:#6c757d; margin-top:4px; }
.timeline-job.active-now{ background:#e6f4ea; border-color:#1b4332; }
.timeline-job:not(:last-child){ margin-bottom:0; }
.empty-state{ display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; gap:6px; padding:16px; color:#6c757d; font-size:0.85rem; border:1px dashed #ced4da; border-radius:12px; }
.fade-enter-active,.fade-leave-active{ transition:opacity .2s ease; }
.fade-enter-from,.fade-leave-to{ opacity:0; }
</style>
