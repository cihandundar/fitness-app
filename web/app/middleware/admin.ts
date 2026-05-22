export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore()

  // Eğer kullanıcı giriş yapmamışsa veya rolü admin/super_admin değilse dashboard'a yönlendir
  if (!authStore.user || (authStore.user.role !== 'admin' && authStore.user.role !== 'super_admin')) {
    return navigateTo('/dashboard')
  }
})
