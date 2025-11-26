import axios from 'axios'
import { resolveCsrfToken } from './csrf'

function csrfHeaders(pageProps = {}) {
  return {
    'X-CSRF-TOKEN': resolveCsrfToken(pageProps),
    'X-Requested-With': 'XMLHttpRequest'
  }
}

export async function updateCleanerJobStatus(jobId, action, pageProps = {}) {
  const { data } = await axios.post(`/cleaner/jobs/${jobId}/status`, { action }, {
    headers: csrfHeaders(pageProps)
  })
  return data?.job
}

export async function sendCleanerLocation(jobId, payload, pageProps = {}) {
  const { data } = await axios.post(`/cleaner/jobs/${jobId}/location`, payload, {
    headers: csrfHeaders(pageProps)
  })
  return data?.job
}

export function statusActionLabel(status) {
  switch (status) {
    case 'scheduled':
    case 'en_route':
    case 'arrived':
      return 'Start'
    case 'started':
      return 'Finish'
    case 'completed':
      return 'Completed'
    case 'cancelled':
      return 'Cancelled'
    default:
      return 'Update'
  }
}
