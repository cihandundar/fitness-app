import { defineNuxtRouteMiddleware } from 'nuxt/app'

export default defineNuxtRouteMiddleware(async (to, from) => {
  const authStore = useAuthStore()

  // Kullanıcı giriş yapmamışsa auth middleware'e bırak
  if (!authStore.isLoggedIn) {
    return
  }

  // Admin kullanıcılar için üyelik kontrolü yapma
  if (authStore.isAdmin) {
    return
  }

  const api = useApi()

  try {
    const response = await api.get('/my-membership')
    const membership = response.data?.data || response.data

    // Üyelik yok veya aktif değilse dashboard'a yönlendir
    if (!membership || membership.status !== 'active') {
      return navigateTo('/dashboard')
    }
  } catch (error) {
    // API hatası durumunda da dashboard'a yönlendir
    return navigateTo('/dashboard')
  }
})
