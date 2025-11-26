<template>
  <div class="brand-customizer">
    <div class="brand-lab">
      <div class="lab-form">
      <p class="eyebrow">{{ format(section.badge || 'Customise') }}</p>
      <h2>{{ format(section.title || 'Brand-ready portal in seconds') }}</h2>
      <p class="lead">{{ format(section.description || 'Drop in your website, pick a colour and we output the exact embed + booking hub that matches your brand guidelines.') }}</p>

      <label class="field">
        <span>Business name</span>
        <input type="text" v-model="businessName" placeholder="e.g. Clearview Window Co." />
      </label>

      <label class="field">
        <span>Website / booking domain</span>
        <div class="url-input">
          <span>https://</span>
          <input type="text" v-model="domainInput" @input="handleDomainInput" placeholder="yourdomain.co.uk" />
        </div>
      </label>

      <div class="profile-card">
        <div class="profile-header">
          <p class="meta-label">Company profile</p>
          <p class="profile-copy">These details appear on invoices, transactional emails and the booking portal.</p>
        </div>
        <div class="profile-grid">
          <label class="field">
            <span>Legal name</span>
            <input type="text" v-model="legalName" placeholder="e.g. AOK World Cleaning Ltd" />
          </label>
          <label class="field">
            <span>Company registration number</span>
            <input type="text" v-model="registrationNumber" placeholder="e.g. 12874632" />
          </label>
          <label class="field">
            <span>VAT / tax ID</span>
            <input type="text" v-model="vatNumber" placeholder="e.g. GB 123 4567 89" />
          </label>
          <label class="field">
            <span>Primary timezone</span>
            <input type="text" v-model="timezone" placeholder="e.g. Europe/London" />
          </label>
          <label class="field">
            <span>Currency</span>
            <input type="text" v-model="currency" placeholder="e.g. GBP" />
          </label>
          <label class="field">
            <span>Support email</span>
            <input type="email" v-model="supportEmail" placeholder="support@yourcompany.co" />
          </label>
          <label class="field">
            <span>Support phone</span>
            <input type="text" v-model="supportPhone" placeholder="+44 20 1234 5678" />
          </label>
          <label class="field full">
            <span>Support hours</span>
            <input type="text" v-model="supportHours" placeholder="Mon–Fri · 08:00–18:00 UK" />
            <small class="field-hint">Shown on booking pages + transactional emails.</small>
          </label>
          <label class="field full">
            <span>Address line 1</span>
            <input type="text" v-model="addressLine1" placeholder="20 Electric Boulevard" />
          </label>
          <label class="field full">
            <span>Address line 2</span>
            <input type="text" v-model="addressLine2" placeholder="Suite or building (optional)" />
          </label>
          <label class="field">
            <span>City</span>
            <input type="text" v-model="city" placeholder="London" />
          </label>
          <label class="field">
            <span>Region / county</span>
            <input type="text" v-model="region" placeholder="Greater London" />
          </label>
          <label class="field">
            <span>Postal code</span>
            <input type="text" v-model="postalCode" placeholder="SW11 8BJ" />
          </label>
          <label class="field">
            <span>Country</span>
            <input type="text" v-model="country" placeholder="GB" />
          </label>
        </div>
        <label class="toggle-field">
          <input type="checkbox" v-model="syncSupportDetails" />
          <div>
            <span>Sync these details to invoices & emails</span>
            <p>When enabled, support contact info appears automatically in customer communications.</p>
          </div>
        </label>
      </div>

      <div class="color-panel" v-if="showColors">
        <div class="tone-grid">
          <label class="fine-tune">
            <span>Primary</span>
            <input type="color" v-model="primaryColor" aria-label="Primary colour" />
          </label>
          <label class="fine-tune">
            <span>Secondary</span>
            <input type="color" v-model="secondaryColor" aria-label="Secondary colour" />
          </label>
          <label class="fine-tune">
            <span>Accent</span>
            <input type="color" v-model="selectedColor" aria-label="Accent colour" />
          </label>
        </div>
      </div>

      <div class="workspace-shells">
        <div class="shell-header">
          <p class="meta-label">Workspace shells</p>
          <p class="shell-copy">Set sidebar colors for owner, staff and customer portals.</p>
        </div>
        <div class="workspace-shell-grid">
          <label v-for="shell in workspaceShellOptions" :key="shell.key" class="workspace-shell">
            <div class="shell-preview" :style="shellPreviewStyle(shell.key)">
              <span class="preview-pill"></span>
              <span class="preview-link short"></span>
              <span class="preview-link"></span>
            </div>
            <div class="shell-meta">
              <span class="shell-label">{{ shell.label }}</span>
              <span class="shell-hint">{{ shell.hint }}</span>
            </div>
            <input type="color" v-model="workspaceColors[shell.key]" :aria-label="shell.label" />
          </label>
        </div>
      </div>

      <div class="integration-grid">
        <label v-for="integration in integrationOptions" :key="integration.key" class="integration">
          <input type="checkbox" v-model="selectedIntegrations" :value="integration.key" />
          <div>
            <strong>{{ integration.label }}</strong>
            <p>{{ integration.description }}</p>
          </div>
        </label>
      </div>

      <div class="upload-grid">
        <div class="upload-card">
          <label class="upload-label">Logo</label>
          <div class="upload-preview" v-if="logoPreview">
            <img :src="logoPreview" alt="Logo preview" />
          </div>
          <input ref="logoInput" type="file" accept="image/*" @change="onLogoChange" />
          <small>SVG/PNG, up to 3MB.</small>
          <p class="error" v-if="brandingForm.errors.logo">{{ brandingForm.errors.logo }}</p>
        </div>
        <div class="upload-card">
          <label class="upload-label">Favicon / Icon</label>
          <div class="upload-preview icon" v-if="iconPreview">
            <img :src="iconPreview" alt="Icon preview" />
          </div>
          <input ref="iconInput" type="file" accept="image/*" @change="onIconChange" />
          <small>Square PNG, up to 1MB.</small>
          <p class="error" v-if="brandingForm.errors.icon">{{ brandingForm.errors.icon }}</p>
        </div>
      </div>

      <label class="field">
        <span>Back to site link</span>
        <input type="url" v-model="backLink" placeholder="https://yourdomain.co.uk" />
      </label>

      <div class="brand-actions">
        <div class="status-messages">
          <p v-if="flashSuccess" class="status success"><i class="bi bi-check2-circle"></i> {{ flashSuccess }}</p>
          <p v-else-if="formError" class="status error"><i class="bi bi-exclamation-triangle"></i> {{ formError }}</p>
        </div>
        <div class="action-buttons">
          <button type="button" class="ghost" @click="resetBranding" :disabled="brandingForm.processing">Reset</button>
          <button type="button" @click="submitBranding" :disabled="brandingForm.processing">
            <span v-if="brandingForm.processing"><i class="bi bi-arrow-repeat spin"></i> Saving…</span>
            <span v-else>Save branding</span>
          </button>
        </div>
      </div>
    </div>

    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { formatText } from '../../utils/contextFormatter'
import { sidebarGradientFromColor, normalizeHex } from '../../utils/color'
import { resolveCsrfToken } from '../../utils/csrf'

const FALLBACK_LOGO = 'https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152'

const props = defineProps({
  section: { type: Object, required: true },
  context: { type: Object, default: () => ({}) },
})

const format = text => formatText(text, props.context)
const page = usePage()
const saveUrl = computed(() => props.section.props?.saveUrl || '/owner/branding/update')
const csrfToken = computed(() => resolveCsrfToken(page?.props))

const integrationOptions = [
  { key: 'quote', label: 'Quote widget', description: 'Live pricing + add-ons embedded on any page.' },
  { key: 'status', label: 'Status badge', description: 'Show uptime + response time inline.' },
  { key: 'support', label: 'Support chat', description: 'Branded concierge with policy snippets.' },
  { key: 'booking', label: 'Booking flow', description: 'Full checkout inside your domain.' },
]
const defaultIntegrations = integrationOptions.map(option => option.key)

const workspaceShellOptions = [
  { key: 'owner', label: 'Owner workspace', hint: 'Controls /owner sidebar colors.' },
  { key: 'staff', label: 'Staff workspace', hint: 'Managers, cleaners, accountants & support.' },
  { key: 'customer', label: 'Customer portal', hint: 'Customer self-serve hub.' },
]

const workspacePreviewDefaults = {
  owner: 'linear-gradient(180deg,#110732,#1f1462 60%,#2b1f7a)',
  staff: 'linear-gradient(180deg,#0e153a,#1d2565 65%,#24307a)',
  customer: 'linear-gradient(180deg,#022c22,#02463a 70%,#006154)',
}

const businessName = ref('')
const domainInput = ref('')
const backLink = ref('')
const legalName = ref('')
const registrationNumber = ref('')
const vatNumber = ref('')
const timezone = ref('Europe/London')
const currency = ref('GBP')
const supportEmail = ref('')
const supportPhone = ref('')
const supportHours = ref('')
const addressLine1 = ref('')
const addressLine2 = ref('')
const city = ref('')
const region = ref('')
const postalCode = ref('')
const country = ref('GB')
const syncSupportDetails = ref(true)
const primaryColor = ref('#0f172a')
const secondaryColor = ref('#1e293b')
const selectedColor = ref('#4fe1c1')
const selectedIntegrations = ref([...defaultIntegrations])
const logoPreview = ref('')
const iconPreview = ref('')
const logoInput = ref(null)
const iconInput = ref(null)
const logoFile = ref(null)
const iconFile = ref(null)
const appliedTheme = ref({ primary: primaryColor.value, secondary: secondaryColor.value, accent: selectedColor.value })
const objectUrls = ref([])
const workspaceColors = ref({ owner: '', staff: '', customer: '' })
const appliedWorkspaceColors = ref({ owner: '', staff: '', customer: '' })
const sidebarBaseline = ref(workspacePreviewDefaults.owner)
let isHydrating = false

const brandingProps = computed(() => page.props?.branding || {})
const tenantProps = computed(() => page.props?.tenant || {})
const flash = computed(() => page.props?.flash || {})
const showColors = computed(() => props.section.props?.showColorPanel !== false)

const flashSuccess = computed(() => flash.value.success || '')
const brandingForm = useForm({
  company_name: '',
  marketing_url: '',
  back_to_site_url: '',
  primary_color: '',
  secondary_color: '',
  accent_color: '',
  font: '',
  logo: null,
  icon: null,
  workspace_owner_sidebar: '',
  workspace_staff_sidebar: '',
  workspace_customer_sidebar: '',
  integrations: [...defaultIntegrations],
  legal_name: '',
  registration_number: '',
  vat_number: '',
  timezone: '',
  currency: '',
  support_email: '',
  support_phone: '',
  support_hours: '',
  address_line1: '',
  address_line2: '',
  city: '',
  region: '',
  postal_code: '',
  country: '',
  sync_support_details: true,
  _token: csrfToken.value,
})
const formError = computed(() => Object.values(brandingForm.errors)[0] || '')

watch(
  () => csrfToken.value,
  token => {
    brandingForm._token = token || ''
  },
  { immediate: true }
)

watch(
  () => brandingProps.value,
  branding => {
    hydrateFromBranding(branding)
  },
  { immediate: true }
)

watch([selectedColor, primaryColor, secondaryColor], () => {
  applyLiveTheme(primaryColor.value, secondaryColor.value, selectedColor.value)
})

watch(businessName, value => {
  updateLiveNames(value)
})

watch(logoPreview, value => {
  updateLiveLogos(value)
})

watch(iconPreview, value => {
  updateLiveIcons(value)
})

watch(() => workspaceColors.value.owner, value => {
  if (isHydrating) return
  previewWorkspaceSidebar('owner', value)
})

function hydrateFromBranding(branding) {
  isHydrating = true
  businessName.value = branding.company || tenantProps.value?.name || 'Your company'
  const marketingUrl = branding.marketing_url || tenantProps.value?.marketing_url || ''
  domainInput.value = sanitiseDomain(marketingUrl)
  backLink.value = branding.back_to_site_url || marketingUrl || ''
  const profile = branding.profile || tenantProps.value?.branding?.profile || tenantProps.value?.company_profile || {}
  legalName.value = profile.legal_name || ''
  registrationNumber.value = profile.registration_number || ''
  vatNumber.value = profile.vat_number || ''
  timezone.value = profile.timezone || 'Europe/London'
  currency.value = profile.currency || 'GBP'
  supportEmail.value = profile.support_email || ''
  supportPhone.value = profile.support_phone || ''
  supportHours.value = profile.support_hours || ''
  addressLine1.value = profile.address_line1 || ''
  addressLine2.value = profile.address_line2 || ''
  city.value = profile.city || ''
  region.value = profile.region || ''
  postalCode.value = profile.postal_code || ''
  country.value = profile.country || 'GB'
  syncSupportDetails.value = profile.sync_support_details !== undefined ? Boolean(profile.sync_support_details) : true
  primaryColor.value = branding.colors?.primary || '#0f172a'
  secondaryColor.value = branding.colors?.secondary || '#1e293b'
  selectedColor.value = branding.colors?.accent || '#4fe1c1'
  appliedTheme.value = {
    primary: primaryColor.value,
    secondary: secondaryColor.value,
    accent: selectedColor.value,
  }
  logoPreview.value = branding.logo || tenantProps.value?.branding?.logo || FALLBACK_LOGO
  iconPreview.value = branding.icon || branding.logo || tenantProps.value?.branding?.icon || FALLBACK_LOGO
  const shells = branding.workspaces || {}
  workspaceColors.value = {
    owner: shells.owner?.sidebar || '',
    staff: shells.staff?.sidebar || '',
    customer: shells.customer?.sidebar || '',
  }
  appliedWorkspaceColors.value = { ...workspaceColors.value }
  const brandingIntegrations = Array.isArray(branding.integrations) ? branding.integrations : defaultIntegrations
  selectedIntegrations.value = defaultIntegrations.filter(key => brandingIntegrations.includes(key))
  captureSidebarBaseline()
  isHydrating = false
}

function handleDomainInput() {
  domainInput.value = sanitiseDomain(domainInput.value).replace(/^https?:\/\//i, '')
}

function sanitiseDomain(value = '') {
  return value
    .toLowerCase()
    .replace(/^https?:\/\//, '')
    .replace(/\s+/g, '')
    .replace(/\/.*$/, '')
    .replace(/[^a-z0-9.-]/g, '')
}

function formatUrl(value) {
  if (!value) return ''
  if (/^https?:\/\//i.test(value)) {
    return value
  }
  return `https://${value}`
}

function shellPreviewStyle(key) {
  const candidate = workspaceColors.value[key] || (isHydrating ? appliedWorkspaceColors.value[key] : '')
  return {
    background: resolveSidebarValue(candidate) || workspacePreviewDefaults[key],
  }
}

function workspaceRoot() {
  if (typeof document === 'undefined') return null
  return document.querySelector('.workspace-root')
}

function applyLiveTheme(primary, secondary, accent) {
  if (!primary || !secondary || !accent) return
  const root = workspaceRoot() || document.documentElement
  root?.style.setProperty('--ws-brand-primary', primary)
  root?.style.setProperty('--ws-brand-secondary', secondary)
  root?.style.setProperty('--ws-brand-accent', accent)
}

function resolveSidebarValue(color) {
  const normalized = normalizeHex(color)
  if (!normalized) return ''
  return sidebarGradientFromColor(normalized) || normalized
}

function captureSidebarBaseline() {
  if (appliedWorkspaceColors.value.owner) return
  if (typeof window === 'undefined') return
  const root = workspaceRoot()
  if (!root) return
  const inline = root.style.getPropertyValue('--ws-sidebar')?.trim()
  if (inline) {
    sidebarBaseline.value = inline
    return
  }
  const computed = window.getComputedStyle(root)
  sidebarBaseline.value = computed.getPropertyValue('--ws-sidebar')?.trim() || workspacePreviewDefaults.owner
}

function previewWorkspaceSidebar(target, color) {
  const root = workspaceRoot()
  if (!root) return
  const activeSkin = root.dataset.workspaceSkin || 'owner'
  if (activeSkin !== target) return
  const next = resolveSidebarValue(color) || sidebarBaseline.value
  if (next) {
    root.style.setProperty('--ws-sidebar', next)
  }
}

function updateLiveLogos(src) {
  if (!src) return
  const nodes = document.querySelectorAll('[data-brand-logo]')
  nodes.forEach(node => {
    if (!node.dataset.originalSrc) {
      node.dataset.originalSrc = node.getAttribute('src') || ''
    }
    node.setAttribute('src', src)
  })
}

function updateLiveNames(value) {
  const nodes = document.querySelectorAll('[data-brand-name]')
  nodes.forEach(node => {
    if (!node.dataset.originalName) {
      node.dataset.originalName = node.textContent || ''
    }
    node.textContent = value
  })
}

function updateLiveIcons(src) {
  if (!src) return
  const nodes = document.querySelectorAll('link[data-brand-icon]')
  nodes.forEach(node => {
    if (!node.dataset.originalHref) {
      node.dataset.originalHref = node.getAttribute('href') || ''
    }
    node.setAttribute('href', src)
  })
}

function restoreLiveBranding() {
  document.querySelectorAll('[data-brand-logo]').forEach(node => {
    if (node.dataset.originalSrc) {
      node.setAttribute('src', node.dataset.originalSrc)
      delete node.dataset.originalSrc
    }
  })
  document.querySelectorAll('[data-brand-name]').forEach(node => {
    if (node.dataset.originalName) {
      node.textContent = node.dataset.originalName
      delete node.dataset.originalName
    }
  })
  document.querySelectorAll('link[data-brand-icon]').forEach(node => {
    if (node.dataset.originalHref) {
      node.setAttribute('href', node.dataset.originalHref)
      delete node.dataset.originalHref
    }
  })
  applyLiveTheme(appliedTheme.value.primary, appliedTheme.value.secondary, appliedTheme.value.accent)
  previewWorkspaceSidebar('owner', appliedWorkspaceColors.value.owner || '')
}

function onLogoChange(event) {
  const file = event.target.files?.[0]
  logoFile.value = file || null
  if (file) {
    const url = URL.createObjectURL(file)
    objectUrls.value.push(url)
    logoPreview.value = url
  } else {
    logoPreview.value = brandingProps.value.logo || FALLBACK_LOGO
  }
}

function onIconChange(event) {
  const file = event.target.files?.[0]
  iconFile.value = file || null
  if (file) {
    const url = URL.createObjectURL(file)
    objectUrls.value.push(url)
    iconPreview.value = url
  } else {
    iconPreview.value = brandingProps.value.icon || brandingProps.value.logo || FALLBACK_LOGO
  }
}

function resetBranding() {
  hydrateFromBranding(brandingProps.value)
  logoFile.value = null
  iconFile.value = null
  if (logoInput.value) logoInput.value.value = ''
  if (iconInput.value) iconInput.value.value = ''
  brandingForm.clearErrors()
}

function syncForm() {
  brandingForm.company_name = businessName.value?.trim() || ''
  brandingForm.marketing_url = formatUrl(domainInput.value)
  brandingForm.back_to_site_url = formatUrl(backLink.value || domainInput.value)
  brandingForm.primary_color = primaryColor.value
  brandingForm.secondary_color = secondaryColor.value
  brandingForm.accent_color = selectedColor.value
  brandingForm.logo = logoFile.value
  brandingForm.icon = iconFile.value
  brandingForm.workspace_owner_sidebar = workspaceColors.value.owner || ''
  brandingForm.workspace_staff_sidebar = workspaceColors.value.staff || ''
  brandingForm.workspace_customer_sidebar = workspaceColors.value.customer || ''
  brandingForm.integrations = [...selectedIntegrations.value]
  brandingForm.legal_name = legalName.value?.trim() || ''
  brandingForm.registration_number = registrationNumber.value?.trim() || ''
  brandingForm.vat_number = vatNumber.value?.trim() || ''
  brandingForm.timezone = timezone.value?.trim() || ''
  brandingForm.currency = currency.value?.trim() || ''
  brandingForm.support_email = supportEmail.value?.trim() || ''
  brandingForm.support_phone = supportPhone.value?.trim() || ''
  brandingForm.support_hours = supportHours.value?.trim() || ''
  brandingForm.address_line1 = addressLine1.value?.trim() || ''
  brandingForm.address_line2 = addressLine2.value?.trim() || ''
  brandingForm.city = city.value?.trim() || ''
  brandingForm.region = region.value?.trim() || ''
  brandingForm.postal_code = postalCode.value?.trim() || ''
  brandingForm.country = country.value?.trim() || ''
  brandingForm.sync_support_details = syncSupportDetails.value ? 1 : 0
  brandingForm._token = csrfToken.value
}

function submitBranding() {
  syncForm()
  brandingForm.post(saveUrl.value, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      logoFile.value = null
      iconFile.value = null
      if (logoInput.value) logoInput.value.value = ''
      if (iconInput.value) iconInput.value.value = ''
      appliedTheme.value = {
        primary: primaryColor.value,
        secondary: secondaryColor.value,
        accent: selectedColor.value,
      }
      appliedWorkspaceColors.value = { ...workspaceColors.value }
      const preview = resolveSidebarValue(workspaceColors.value.owner)
      if (preview) {
        sidebarBaseline.value = preview
      }
    },
  })
}

onMounted(() => {
  captureSidebarBaseline()
})

onBeforeUnmount(() => {
  restoreLiveBranding()
  objectUrls.value.forEach(url => URL.revokeObjectURL(url))
})
</script>

<style scoped>
.brand-customizer {
  width: 100%;
  padding: clamp(1.5rem, 5vw, 3rem);
  background: linear-gradient(180deg, #fdfefe, #f2f7f8);
  border-radius: 28px;
  border: 1px solid rgba(15,23,42,.05);
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.6);
}
.brand-lab {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: clamp(1.5rem, 4vw, 3rem);
}
.lab-form { display: flex; flex-direction: column; gap: 1.1rem; }
.eyebrow { text-transform: uppercase; letter-spacing: .3em; color: #0fb89b; font-size: .75rem; margin: 0; }
.lead { color: #4b5563; margin: 0; }
.field { display: flex; flex-direction: column; gap: .4rem; font-size: .9rem; color: #0f172a; }
.field input { border-radius: 14px; border: 1px solid rgba(15,23,42,.12); padding: .75rem 1rem; font-size: 1rem; }
.field input:focus { outline: none; border-color: #0fb89b; box-shadow: 0 0 0 3px rgba(15,184,155,.1); }
.url-input { display: flex; align-items: center; gap: .4rem; border-radius: 14px; border: 1px solid rgba(15,23,42,.12); padding: .6rem .9rem; background: #f8fafc; }
.url-input span { color: #6b7280; font-size: .9rem; }
.url-input input { flex: 1; border: none; background: transparent; padding: 0; }
.url-input input:focus { outline: none; }

.color-panel { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 1rem; background: rgba(255,255,255,.9); display: flex; flex-direction: column; gap: .75rem; }
.fine-tune { display: flex; align-items: center; gap: .6rem; font-size: .85rem; color: #4b5563; }
.fine-tune input { border: none; background: transparent; width: 50px; height: 32px; padding: 0; }
.tone-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: .75rem; margin-top: .25rem; }
.tone-grid .fine-tune { justify-content: space-between; padding: .35rem .65rem; border: 1px solid rgba(15,23,42,.08); border-radius: 12px; background: #fff; }
.tone-grid .fine-tune input { width: 36px; height: 28px; }

.meta-label { text-transform: uppercase; font-size: .7rem; letter-spacing: .3em; color: #6b7280; margin: 0; }
.shell-copy { margin: 0; color: #6b7280; font-size: .85rem; }

.workspace-shells { display: flex; flex-direction: column; gap: .75rem; border-radius: 16px; border: 1px solid rgba(15,23,42,.06); padding: 1rem; background: rgba(255,255,255,.85); }
.shell-header { display: flex; flex-direction: column; gap: .25rem; }
.shell-copy { margin: 0; color: #6b7280; font-size: .85rem; }
.workspace-shell-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: .75rem; }
.workspace-shell { display: flex; align-items: center; gap: .75rem; padding: .6rem; border-radius: 14px; border: 1px solid rgba(15,23,42,.08); background: #fff; }
.shell-preview { width: 60px; height: 60px; border-radius: 14px; display: flex; flex-direction: column; justify-content: center; gap: 6px; padding: 10px; box-shadow: inset 0 0 0 1px rgba(255,255,255,.25), 0 8px 20px rgba(15,23,42,.18); }
.shell-preview .preview-pill,
.shell-preview .preview-link { display: block; border-radius: 999px; background: rgba(255,255,255,.8); height: 8px; }
.shell-preview .preview-pill { width: 80%; }
.shell-preview .preview-link { height: 6px; opacity: .85; width: 90%; }
.shell-preview .preview-link.short { width: 70%; }
.shell-preview .preview-link:not(.short) { width: 90%; }
.shell-meta { flex: 1; display: flex; flex-direction: column; gap: 4px; }
.shell-label { font-weight: 600; color: #0f172a; }
.shell-hint { color: #6b7280; font-size: .8rem; }
.workspace-shell input[type="color"] { border: none; width: 46px; height: 32px; border-radius: 8px; background: #f1f5f9; padding: 0; cursor: pointer; }

.integration-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: .75rem; }
.integration { border: 1px solid rgba(15,23,42,.08); border-radius: 16px; padding: .85rem; display: grid; grid-template-columns: auto 1fr; gap: .85rem; align-items: flex-start; background: #fff; }
.integration input { margin: .2rem 0 0 0; accent-color: #0fb89b; }
.integration p { margin: .2rem 0 0; color: #6b7280; font-size: .85rem; }

.profile-card { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 1rem; background: rgba(255,255,255,.92); display: flex; flex-direction: column; gap: .9rem; }
.profile-header { display: flex; flex-direction: column; gap: .25rem; }
.profile-copy { margin: 0; color: #6b7280; font-size: .85rem; }
.profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: .75rem; }
.field.full { grid-column: 1 / -1; }
.field textarea { border-radius: 14px; border: 1px solid rgba(15,23,42,.12); padding: .75rem 1rem; font-size: 1rem; resize: vertical; min-height: 80px; }
.field-hint { color: #6b7280; font-size: .78rem; }
.toggle-field { display: flex; gap: .75rem; border: 1px solid rgba(15,23,42,.08); border-radius: 14px; padding: .65rem; align-items: flex-start; background: #fff; }
.toggle-field input { margin-top: .2rem; accent-color: #0fb89b; }
.toggle-field span { font-weight: 600; color: #0f172a; }
.toggle-field p { margin: .15rem 0 0; color: #6b7280; font-size: .85rem; }

.upload-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
.upload-card { border: 1px dashed rgba(15,23,42,.2); border-radius: 16px; padding: 1rem; background: rgba(249,250,251,.85); display: flex; flex-direction: column; gap: .6rem; }
.upload-label { text-transform: uppercase; letter-spacing: .2em; font-size: .7rem; color: #6b7280; }
.upload-preview { border-radius: 12px; border: 1px solid rgba(15,23,42,.08); background: #fff; padding: .75rem; display: flex; align-items: center; justify-content: center; min-height: 70px; }
.upload-preview img { max-width: 100%; max-height: 70px; object-fit: contain; }
.upload-preview.icon img { max-height: 48px; }
.upload-card input[type="file"] { font-size: .85rem; }
.upload-card small { color: #6b7280; font-size: .75rem; }
.error { color: #b91c1c; font-size: .8rem; margin: 0; }

.brand-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; border-top: 1px solid rgba(15,23,42,.08); padding-top: 1rem; margin-top: .5rem; }
.status-messages { flex: 1; }
.status { margin: 0; font-size: .85rem; display: flex; align-items: center; gap: .4rem; }
.status.success { color: #0f9d58; }
.status.error { color: #b91c1c; }
.action-buttons { display: flex; gap: .75rem; }
.action-buttons button { border-radius: 999px; border: none; padding: .55rem 1.4rem; font-weight: 600; cursor: pointer; }
.action-buttons .ghost { border: 1px solid rgba(15,23,42,.15); background: transparent; color: #0f172a; }
.action-buttons button:not(.ghost) { background: #0fb89b; color: #fff; }
.spin { animation: spin 1s linear infinite; }

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .color-panel { padding: .85rem; }
  .tone-grid { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
  .workspace-shells { padding: .85rem; }
  .workspace-shell-grid { grid-template-columns: 1fr; }
  .workspace-shell { flex-direction: column; align-items: flex-start; }
  .workspace-shell input[type="color"] { width: 100%; height: 38px; }
}
</style>
