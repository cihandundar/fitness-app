import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  modules: [
    '@pinia/nuxt'
  ],
  css: ['~/assets/css/main.css'],
  vite: {
    plugins: [
      tailwindcss(),
    ],
    clearScreen: false,
    server: {
      proxy: {
        '/storage': {
          target: 'http://localhost:8000',
          changeOrigin: true
        }
      }
    }
  },
  typescript: {
    strict: false,
    typeCheck: false
  }
})