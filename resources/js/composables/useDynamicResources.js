import { ref } from 'vue'

export function useDynamicResources(isLoading,initialCssFiles = [],initialJsFiles = []) {
  const cssFiles = ref(initialCssFiles)
  const JsFiles = ref(initialJsFiles)

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
    const baseUrl = 'http://[::1]:5173';
    try {
      for (const file of cssFiles.value) {
        await loadCss(`${baseUrl}/${file}`)
      }
    } catch (error) {
      console.log('Error Occurred While Initializing CSS files', error)
    } finally {
      isLoading.value = false
    }
  }

  const removeDynamicJs = () => {
    return new Promise((resolve,reject) => {
      try {
        const scriptlinks = document.querySelectorAll('script[data-dynamic="true"]');
        scriptlinks.forEach((link) => {
          document.body.removeChild(link)
        })
        console.log(`${scriptlinks.length} Scripts Removed.`)
        resolve()
      } catch (error) {
        console.log('Error Occured While removing Script Files', error)
        reject()
      }
    })
  }

  const loadScript = (src) => {
    return new Promise((resolve, reject) => {
      const existingScript = document.querySelector(`script[src="${src}"]`);
      if (existingScript) {
        console.log(`${src} already loaded.`);
        return resolve();
      }
  
      const script = document.createElement('script');
      script.src = src;
      script.dataset.dynamic = 'true';  // Adding data-dynamic attribute
  
      script.onload = () => {
        console.log(`${src} loaded successfully.`);
        resolve();
      };
  
      script.onerror = () => {
        console.error(`Failed to load script: ${src}`);
        reject(new Error(`Failed to load script: ${src}`));
      };
  
      document.body.appendChild(script);
    });
  };

   const initializeScripts = async () => {
    const baseUrl = 'http://[::1]:5173';
    try {
      for (const script of JsFiles.value) {
        await loadScript(`${baseUrl}/${script}`);
      }
      console.log('All scripts loaded successfully');
    } catch (error) {
      console.error('Error loading scripts:', error);
    }
  };


  return {
    removeDynamicCss,
    initializeCss,
    removeDynamicJs,
    initializeScripts
  }
}