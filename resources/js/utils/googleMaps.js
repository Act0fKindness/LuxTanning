import { Loader } from '@googlemaps/js-api-loader'

let cachedKey = null
let loaderPromise = null

export function loadGoogleMaps(apiKey, libraries = []) {
  if (!apiKey) {
    return Promise.reject(new Error('Missing Google Maps API key'))
  }

  if (!loaderPromise || cachedKey !== apiKey) {
    const loader = new Loader({
      apiKey,
      version: 'weekly',
      libraries
    })
    cachedKey = apiKey
    loaderPromise = loader.load().catch(error => {
      cachedKey = null
      loaderPromise = null
      throw error
    })
  }

  return loaderPromise
}
