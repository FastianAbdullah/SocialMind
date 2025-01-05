import { ref } from 'vue'

export function useDynamicCss(isLoading,initialCssFiles = []) {
  const cssFiles = ref(initialCssFiles)

  const removeDynamicCss = () => {
    return new Promise((resolve, reject) => {
      try {
        const links = document.querySelectorAll('link[data-dynamic="true"]')
        links.forEach((link) => {
          document.head.removeChild(link)
        })
        console.log(`${links.length} CSS file(s) removed.`)
        resolve()
      } catch (error) {
        console.log('Error Occurred While loading the CSS file', error)
        reject()
      }
    })
  }

  const loadCss = (href) => {
    return new Promise((resolve, reject) => {
      const existingLink = document.querySelector(`link[href="${href}"]`)
      if (existingLink) {
        console.log(`${href} already loaded.`)
        return resolve()
      }

      const link = document.createElement('link')
      link.rel = 'stylesheet'
      link.href = href
      link.dataset.dynamic = 'true'

      link.onload = () => {
        console.log(`${href} loaded successfully.`)
        resolve()
      }

      link.onerror = () => {
        console.error(`Failed to load CSS: ${href}`)
        reject(new Error(`Failed to load CSS: ${href}`))
      }

      document.head.appendChild(link)
    })
  }

  const initializeCss = async () => {
    try {
      for (const file of cssFiles.value) {
        await loadCss(file)
      }
      isLoading.value = false
    } catch (error) {
      console.log('Error Occurred While Initializing CSS files', error)
      isLoading.value = false
    }
  }

  // Add method to update CSS files
  const updateCssFiles = (newFiles) => {
    cssFiles.value = newFiles
  }

  return {
    removeDynamicCss,
    initializeCss,
  }
}