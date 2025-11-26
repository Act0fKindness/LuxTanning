<template>
  <section class="pricing-section">
    <header class="section-head">
      <p class="eyebrow">{{ section.badge || 'Pricing' }}</p>
      <h2>{{ section.title }}</h2>
      <p v-if="section.description" class="muted">{{ format(section.description) }}</p>
    </header>

    <div class="plans-grid">
      <article
        v-for="plan in plans"
        :key="plan.name"
        class="plan-card"
        :class="{ popular: plan.popular }"
      >
        <p class="plan-badge" v-if="plan.badge || plan.popular">
          {{ plan.badge || 'Most popular' }}
        </p>
        <div class="plan-heading">
          <h3>{{ plan.name }}</h3>
          <p class="plan-tagline" v-if="plan.tagline">{{ format(plan.tagline) }}</p>
        </div>
        <p class="plan-price">
          <span class="value">{{ plan.price }}</span>
          <span class="period">{{ plan.period }}</span>
        </p>
        <p class="plan-description">{{ format(plan.description) }}</p>
        <ul class="plan-features">
          <li v-for="feature in plan.features" :key="feature">
            <i class="bi bi-check2"></i>
            <span>{{ format(feature) }}</span>
          </li>
        </ul>
        <component
          v-if="plan.cta"
          :is="plan.cta.href ? 'a' : 'button'"
          class="btn"
          :class="plan.popular ? 'btn-primary' : 'btn-ghost'"
          :href="plan.cta.href || null"
          type="button"
        >
          {{ plan.cta.label }}
        </component>
      </article>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { formatText } from '../../utils/contextFormatter'

const props = defineProps({
  section: { type: Object, required: true },
  context: { type: Object, default: () => ({}) },
})

const format = text => formatText(text, props.context)
const plans = computed(() => props.section?.props?.plans || [])
</script>

<style scoped>
.pricing-section { padding: 6px; }
.section-head { text-align: center; margin-bottom: 24px; }
.eyebrow { text-transform: uppercase; letter-spacing: 0.3em; font-size: 12px; color: #475467; margin-bottom: 8px; }
.section-head h2 { margin: 0 0 8px; font-size: 28px; }
.muted { margin: 0 auto; max-width: 640px; color: #475467; }

.plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; }
.plan-card { border: 1px solid rgba(15,23,42,.1); border-radius: 24px; padding: 24px; background: #f8fafc; display: flex; flex-direction: column; gap: 16px; position: relative; }
.plan-card.popular { background: linear-gradient(135deg, #ecfdf3, #e0f2fe); border-color: rgba(79, 225, 193, 0.4); box-shadow: 0 20px 45px rgba(15, 23, 42, 0.15); }
.plan-badge { position: absolute; top: 16px; right: 16px; margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.2em; color: #047857; }
.plan-heading h3 { margin: 0; font-size: 20px; }
.plan-tagline { margin: 4px 0 0; color: #475467; font-size: 13px; text-transform: uppercase; letter-spacing: 0.2em; }
.plan-price { margin: 0; font-weight: 700; font-size: 30px; display: flex; align-items: baseline; gap: 6px; }
.plan-price .period { font-size: 14px; color: #475467; font-weight: 500; }
.plan-description { margin: 0; color: #425466; min-height: 40px; }
.plan-features { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; flex-grow: 1; }
.plan-features li { display: flex; align-items: flex-start; gap: 8px; color: #1d2939; }
.plan-features i { color: #0d9488; margin-top: 2px; }
.btn { border-radius: 999px; padding: 12px 18px; text-align: center; font-weight: 600; text-decoration: none; border: none; cursor: pointer; }
.btn-primary { background: #0f766e; color: #fff; }
.btn-ghost { background: #fff; color: #0f172a; border: 1px solid rgba(15,23,42,.1); }
</style>
