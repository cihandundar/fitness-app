import { defineNuxtRouteMiddleware } from 'nuxt/app'

export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore()

  // Kullanıcı giriş yapmamışsa login'e yönlendir
  if (!authStore.isLoggedIn) {
    return navigateTo('/login')
  }

  // Trainer rolü kontrolü
  if (authStore.user?.role !== 'trainer' && !authStore.isAdmin) {
    return navigateTo('/dashboard')
  }
})
