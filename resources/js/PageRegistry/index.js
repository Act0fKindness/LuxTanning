export { pageRegistry, definePage } from './registry'

import { registerPublicPages } from './pages/public'
import { registerAuthPages } from './pages/auth'
import { registerSharedPages } from './pages/shared'
import { registerCustomerPages } from './pages/customer'
import { registerCleanerPages } from './pages/cleaner'
import { registerManagerPages } from './pages/manager'
import { registerOwnerPages } from './pages/owner'
import { registerAccountantPages } from './pages/accountant'
import { registerSupportPages } from './pages/support'
import { registerGlintPages } from './pages/glint'

registerPublicPages()
registerAuthPages()
registerSharedPages()
registerCustomerPages()
registerCleanerPages()
registerManagerPages()
registerOwnerPages()
registerAccountantPages()
registerSupportPages()
registerGlintPages()
