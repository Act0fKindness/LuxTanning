export function calculateWindowQuote({
  windows = 24,
  storeys = 2,
  frequency = 'six_week',
  frames = false,
  sills = false,
  gutters = false,
} = {}) {
  const windowPrice = 2.5
  const extrasPrice = { frames: 4, sills: 3, gutters: 25 }
  const storeyMods = { 1: 0, 2: 0.1, 3: 0.2 }
  const freqMods = { one_off: 0.2, four_week: -0.05, six_week: 0, eight_week: 0.05 }
  const firstFactor = 1.3
  const vat = 0.2
  const minCallout = 15
  const round = 0.5
  const depositCfg = { percent: 0.3, min: 1000, max: 5000 }
  const timePerWindow = 2
  const gutterTime = 20

  const baseWindows = windows * windowPrice
  const extrasTotal = (frames ? extrasPrice.frames : 0) + (sills ? extrasPrice.sills : 0) + (gutters ? extrasPrice.gutters : 0)

  const base = baseWindows + extrasTotal
  const storeyAdj = base * (storeyMods[storeys] ?? 0)
  const freqAdj = base * (freqMods[frequency] ?? 0)
  const firstAdj = base * (firstFactor - 1)

  let exVat = base + storeyAdj + freqAdj + firstAdj
  const preClampExVat = exVat
  exVat = Math.max(exVat, minCallout)
  const roundedExVat = Math.round(exVat / round) * round
  const total = roundedExVat * (1 + vat)
  const totalPence = Math.round(total * 100)

  let depositPence = 0
  if (depositCfg.percent > 0) {
    const calc = Math.round(totalPence * depositCfg.percent)
    depositPence = Math.min(Math.max(calc, depositCfg.min), depositCfg.max)
  }

  const minutes = Math.round(windows * timePerWindow + (gutters ? gutterTime : 0))

  return {
    total_pence: totalPence,
    deposit_pence: depositPence,
    estimate_minutes: minutes,
    breakdown: {
      base_windows: baseWindows,
      extras_total: extrasTotal,
      storey_adjustment: storeyAdj,
      frequency_adjustment: freqAdj,
      first_clean_adjustment: firstAdj,
      base_pre_clamp: preClampExVat,
      min_callout_applied: exVat > preClampExVat,
      ex_vat: roundedExVat,
      vat_rate: vat,
    },
  }
}

export const currencyFormatter = new Intl.NumberFormat('en-GB', {
  style: 'currency',
  currency: 'GBP',
})

export const formatCurrency = amount => currencyFormatter.format(amount)
