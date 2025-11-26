export function normalizeHex(value) {
  if (typeof value !== 'string') return null
  let hex = value.trim()
  if (!hex) return null
  if (hex.startsWith('#')) {
    hex = hex.slice(1)
  }
  if (!(hex.length === 3 || hex.length === 6)) {
    return null
  }
  if (!/^[0-9a-fA-F]+$/.test(hex)) {
    return null
  }
  if (hex.length === 3) {
    hex = hex.split('').map(char => char + char).join('')
  }
  return `#${hex.toLowerCase()}`
}

function adjustChannel(channel, percent) {
  const ratio = Math.max(-1, Math.min(1, percent))
  if (ratio < 0) {
    return Math.round(channel * (1 + ratio))
  }
  return Math.round(channel + (255 - channel) * ratio)
}

export function adjustHex(value, percent) {
  const hex = normalizeHex(value)
  if (!hex) return null
  const ratio = percent / 100
  const numeric = parseInt(hex.slice(1), 16)
  const r = (numeric >> 16) & 255
  const g = (numeric >> 8) & 255
  const b = numeric & 255
  const nextR = adjustChannel(r, ratio)
  const nextG = adjustChannel(g, ratio)
  const nextB = adjustChannel(b, ratio)
  const toHex = component => component.toString(16).padStart(2, '0')
  return `#${toHex(nextR)}${toHex(nextG)}${toHex(nextB)}`
}

export function sidebarGradientFromColor(value) {
  const hex = normalizeHex(value)
  if (!hex) return null
  const top = adjustHex(hex, 18) || hex
  const bottom = adjustHex(hex, -20) || hex
  return `linear-gradient(180deg, ${top}, ${hex} 60%, ${bottom})`
}

export function hexToRgb(value) {
  const hex = normalizeHex(value)
  if (!hex) return null
  const chunk = hex.slice(1)
  const parts = chunk.match(/.{2}/g)
  if (!parts) return null
  const [r, g, b] = parts.map(part => parseInt(part, 16))
  if ([r, g, b].some(component => Number.isNaN(component))) {
    return null
  }
  return { r, g, b }
}

export function luminanceFromHex(value) {
  const rgb = hexToRgb(value)
  if (!rgb) return null
  const normalizeChannel = channel => {
    const scaled = channel / 255
    return scaled <= 0.03928 ? scaled / 12.92 : Math.pow((scaled + 0.055) / 1.055, 2.4)
  }
  const r = normalizeChannel(rgb.r)
  const g = normalizeChannel(rgb.g)
  const b = normalizeChannel(rgb.b)
  return 0.2126 * r + 0.7152 * g + 0.0722 * b
}

export function pickContrastingTextColor(value, light = 'rgba(255,255,255,.92)', dark = 'rgba(15,23,42,.9)', threshold = 0.6) {
  const luminance = luminanceFromHex(value)
  if (luminance === null) {
    return null
  }
  return luminance > threshold ? dark : light
}
