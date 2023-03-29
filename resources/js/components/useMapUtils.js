import {
  ref,
} from 'vue'

export const useMapUtils = () => {
  const observer = ref(null)

  const startObservingMapVisibility = (mapRef) => {
    endObservingMapVisibility()
    observer.value = new IntersectionObserver((changes) => {
      const foundIntersection = changes.some(change => {
        if (typeof change.isVisible === 'undefined') {
          change.isVisible = true
        }

        return change.isIntersecting && change.isVisible
      })

      if (foundIntersection && mapRef.leafletObject) {
        mapRef.leafletObject.invalidateSize()
      }
    }, {
      delay: 100,
      threshold: [0, 1.0],
      trackVisibility: true,
    })
    observer.value.observe(mapRef.$el)
  }
  const endObservingMapVisibility = () => {
    if (observer.value) {
      observer.value.disconnect()
      observer.value = null
    }
  }

  return {
    startObservingMapVisibility,
    endObservingMapVisibility,
  }
}
