// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  modules: ['@nuxtjs/tailwindcss'],

  build: {
    transpile: ['apexcharts', 'vue3-apexcharts']
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
      reverb: {
        appKey: process.env.NUXT_PUBLIC_REVERB_APP_KEY || 'u2oi0cwsi7cmbmnkteku',
        host: process.env.NUXT_PUBLIC_REVERB_HOST || 'localhost',
        port: process.env.NUXT_PUBLIC_REVERB_PORT || '8080',
        scheme: process.env.NUXT_PUBLIC_REVERB_SCHEME || 'http'
      }
    }
  },

  app: {
    head: {
      title: 'FreshTracks - Time Tracking',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: '/freshtracks-icon-alt2.png' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' }
      ]
    }
  },

  css: ['~/assets/css/main.css'],

  tailwindcss: {
    injectPosition: 'first',
    viewer: false
  }
})
