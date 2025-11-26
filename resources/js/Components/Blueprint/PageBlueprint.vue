<template>
  <div class="page-blueprint">
    <div
      class="hero-full-bleed"
      :class="{ 'hero-full-bleed--full': Boolean(page.hero?.fullBleed) }"
    >
      <HeroBanner
        v-if="page.hero"
        :hero="page.hero"
        :fallback-actions="page.actions || []"
        :full-bleed="Boolean(page.hero?.fullBleed)"
        :google-key="googleKey"
        :map-data="heroMapData"
      />
      <section v-else class="page-hero">
        <div>
          <p v-if="page.badge" class="badge">{{ page.badge }}</p>
          <h1>{{ page.title }}</h1>
          <p class="subtitle" v-if="page.description">{{ format(page.description) }}</p>
        </div>
        <div class="hero-actions" v-if="page.actions?.length">
          <component
            v-for="action in page.actions"
            :key="action.label"
            :is="action.href ? 'a' : 'button'"
            :href="action.href"
            class="btn"
            :class="['btn-' + (action.variant || 'secondary')]"
            :type="action.href ? null : 'button'"
          >
            <i v-if="action.icon" :class="['bi', action.icon, 'me-2']"></i>
            {{ action.label }}
          </component>
        </div>
      </section>
    </div>

    <div class="page-content">
      <section v-if="page.quickLinks?.length" class="quick-links">
        <article v-for="link in page.quickLinks" :key="link.href" class="quick-link">
          <div>
            <p class="label">{{ link.label }}</p>
            <p class="desc" v-if="link.description">{{ format(link.description) }}</p>
          </div>
          <span class="icon" aria-hidden="true">→</span>
        </article>
      </section>

      <section
        v-for="section in page.sections"
        :key="section.id"
        :class="['blueprint-section', { 'blueprint-section--breakout': section.fullWidth }]"
        :id="section.anchor || section.id"
      >
        <component
          :is="resolve(section.component)"
          :section="section"
          :context="context"
          :google-key="googleKey"
        />
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { formatText, getContextValue } from '../../utils/contextFormatter'
import SummaryCardsSection from './SummaryCardsSection.vue'
import InsightListSection from './InsightListSection.vue'
import DataTableSection from './DataTableSection.vue'
import TimelineSection from './TimelineSection.vue'
import KanbanBoardSection from './KanbanBoardSection.vue'
import MapPanelSection from './MapPanelSection.vue'
import ActionGridSection from './ActionGridSection.vue'
import StatusListSection from './StatusListSection.vue'
import SplitPanelsSection from './SplitPanelsSection.vue'
import ChecklistSection from './ChecklistSection.vue'
import CleanerMapSection from './CleanerMapSection.vue'
import CleanerNextJobSection from './CleanerNextJobSection.vue'
import CleanerNavigationSection from './CleanerNavigationSection.vue'
import QuoteGeneratorSection from './QuoteGeneratorSection.vue'
import BrandCustomizerSection from './BrandCustomizerSection.vue'
import HeroBanner from './HeroBanner.vue'
import PricingPlansSection from './PricingPlansSection.vue'
import DetailCardSection from './DetailCardSection.vue'
import TileGridSection from './TileGridSection.vue'
import CodeSnippetSection from './CodeSnippetSection.vue'
import OwnerDispatchBoardSection from './OwnerDispatchBoardSection.vue'

const props = defineProps({
  page: { type: Object, required: true },
  context: { type: Object, default: () => ({}) },
})

const googleKey = computed(() => usePage().props?.google?.maps_key || '')
const heroMapData = computed(() => {
  const mapKey = props.page.hero?.mapPanel?.dataKey
  if (!mapKey) return null
  return getContextValue(props.context, mapKey, null)
})

const sectionMap = {
  SummaryCards: SummaryCardsSection,
  InsightList: InsightListSection,
  DataTable: DataTableSection,
  Timeline: TimelineSection,
  KanbanBoard: KanbanBoardSection,
  MapPanel: MapPanelSection,
  ActionGrid: ActionGridSection,
  StatusList: StatusListSection,
  SplitPanels: SplitPanelsSection,
  Checklist: ChecklistSection,
  CleanerMap: CleanerMapSection,
  CleanerNextJob: CleanerNextJobSection,
  CleanerNavigation: CleanerNavigationSection,
  QuoteGenerator: QuoteGeneratorSection,
  BrandCustomizer: BrandCustomizerSection,
  PricingPlans: PricingPlansSection,
  DetailCard: DetailCardSection,
  TileGrid: TileGridSection,
  CodeSnippet: CodeSnippetSection,
  OwnerDispatchBoard: OwnerDispatchBoardSection,
}

const resolve = component => sectionMap[component] || InsightListSection
const format = text => formatText(text, props.context)
</script>

<style scoped>
.page-blueprint { display: flex; flex-direction: column; gap: 32px; }
.hero-full-bleed { width: 100%; padding: 0 clamp(16px, 4vw, 32px); }
.hero-full-bleed--full { padding: 0; }
.page-content { width: 100%; max-width: 1100px; margin: 0 auto; padding: 0 clamp(16px, 4vw, 32px); display: flex; flex-direction: column; gap: 24px; }
.page-hero { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; padding: 24px; background: #0c1324; color: #fff; border-radius: 20px; box-shadow: inset 0 0 0 1px rgba(255,255,255,.08); max-width: 1100px; margin: 30px auto 0; }
.page-hero h1 { font-size: clamp(24px, 4vw, 34px); margin-bottom: 8px; }
.page-hero .subtitle { opacity: .85; max-width: 640px; }
.badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(79,225,193,.15); color: #4fe1c1; border-radius: 999px; padding: 4px 12px; text-transform: uppercase; font-size: 12px; letter-spacing: .1em; }
.hero-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.btn { border: none; border-radius: 999px; font-weight: 600; padding: 10px 18px; cursor: pointer; display: inline-flex; align-items: center; font-size: 14px; }
.btn-primary { background: #4fe1c1; color: #062f25; }
.btn-secondary { background: rgba(255,255,255,.12); color: #fff; }
.btn-ghost { background: transparent; border: 1px solid rgba(255,255,255,.3); color: #fff; }
.btn-danger { background: #e94c4c; color: #fff; }

.quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
.quick-link { padding: 16px 18px; border-radius: 16px; border: 1px solid rgba(15,23,42,.08); background: #fff; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px rgba(15,23,42,.08); }
.quick-link .label { font-weight: 600; margin: 0; }
.quick-link .desc { margin: 4px 0 0; color: #4b5563; font-size: 14px; }
.quick-link .icon { font-size: 20px; color: #0f172a; }

.blueprint-section { border-radius: 20px; border: 1px solid rgba(15,23,42,.08); background: #fff; padding: 24px; box-shadow: 0 12px 30px rgba(15,23,42,.05); scroll-margin-top: calc(var(--nav-h) + 32px); }
.blueprint-section--breakout { padding: 0; border: none; background: transparent; box-shadow: none; }
</style>
