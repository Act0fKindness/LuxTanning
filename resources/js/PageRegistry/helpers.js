const withAnchor = (section, anchor) => anchor ? { ...section, anchor } : section

export const summary = (id, title, cards, description, options = {}) => withAnchor({
  id,
  component: 'SummaryCards',
  title,
  description,
  props: { cards },
}, options.anchor)

export const insights = (id, title, items, description, options = {}) => withAnchor({
  id,
  component: 'InsightList',
  title,
  description,
  props: { items },
}, options.anchor)

export const dataTable = (id, title, columns, rows, description, footer, options = {}) => {
  const { rowsKey, anchor } = options
  const props = { columns, rows, footer }
  if (rowsKey) props.rowsKey = rowsKey
  return withAnchor({
    id,
    component: 'DataTable',
    title,
    description,
    props,
  }, anchor)
}

export const timeline = (id, title, events, description, options = {}) => {
  const { eventsKey, anchor } = options
  const props = { events }
  if (eventsKey) props.eventsKey = eventsKey
  return withAnchor({
    id,
    component: 'Timeline',
    title,
    description,
    props,
  }, anchor)
}

export const kanban = (id, title, columns, description, options = {}) => withAnchor({
  id,
  component: 'KanbanBoard',
  title,
  description,
  props: { columns },
}, options.anchor)

export const actionGrid = (id, title, actions, description, options = {}) => withAnchor({
  id,
  component: 'ActionGrid',
  title,
  description,
  props: { actions },
}, options.anchor)

export const statusList = (id, title, items, description, options = {}) => {
  const { itemsKey, anchor } = options
  const props = { items }
  if (itemsKey) props.itemsKey = itemsKey
  return withAnchor({
    id,
    component: 'StatusList',
    title,
    description,
    props,
  }, anchor)
}

export const mapPanel = (id, title, markers, description, zoom, options = {}) => {
  const { markersKey, anchor } = options
  const props = { markers, zoom }
  if (markersKey) props.markersKey = markersKey
  return withAnchor({
    id,
    component: 'MapPanel',
    title,
    description,
    props,
  }, anchor)
}

export const splitPanels = (id, title, panels, description, options = {}) => withAnchor({
  id,
  component: 'SplitPanels',
  title,
  description,
  props: { panels },
}, options.anchor)

export const checklist = (id, title, items, description, options = {}) => withAnchor({
  id,
  component: 'Checklist',
  title,
  description,
  props: { items },
}, options.anchor)

export const quoteGenerator = (id, title, description, options = {}) => withAnchor({
  id,
  component: 'QuoteGenerator',
  title,
  description,
  badge: options.badge || 'Instant pricing',
  fullWidth: true,
  props: options.props || {},
}, options.anchor)

export const brandCustomizer = (id, title, description, options = {}) => withAnchor({
  id,
  component: 'BrandCustomizer',
  title,
  description,
  badge: options.badge || 'Customise',
  fullWidth: options.fullWidth !== false,
  props: options.props || {},
}, options.anchor)

export const pricingPlans = (id, title, description, plans, options = {}) => withAnchor({
  id,
  component: 'PricingPlans',
  title,
  description,
  fullWidth: options.fullWidth !== false,
  props: { plans },
}, options.anchor)

export const detailCard = (id, title, description, props = {}, options = {}) => withAnchor({
  id,
  component: 'DetailCard',
  title,
  description,
  props,
}, options.anchor)

export const tileGrid = (id, title, description, tiles, options = {}) => withAnchor({
  id,
  component: 'TileGrid',
  title,
  description,
  props: { tiles },
}, options.anchor)

export const codeSnippet = (id, title, description, code, options = {}) => withAnchor({
  id,
  component: 'CodeSnippet',
  title,
  description,
  props: { code, language: options.language || 'html' },
}, options.anchor)
