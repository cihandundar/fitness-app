<template>
  <div>
    <!-- Eğer sayfa layout: false kullanıyorsa, header/footer gösterme -->
    <template v-if="!hasCustomLayout">
      <div class="min-h-screen flex flex-col bg-slate-950 text-slate-100">
        <AppHeader />
        <main class="flex-grow">
          <NuxtPage />
        </main>
        <AppFooter />
      </div>
    </template>

    <!-- Özel layout olan sayfalar için (dashboard vb.) -->
    <template v-else>
      <NuxtPage />
    </template>

    <!-- Toast Notification Component -->
    <UiToast />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const route = useRoute()

// Dashboard ve diğer özel layout sayfalarını kontrol et
const hasCustomLayout = computed(() => {
  return route.meta.layout === false
})

// Initialize auth store on app mount
const authStore = useAuthStore()
const isInitialized = ref(false)

onMounted(async () => {
  await authStore.init()
  isInitialized.value = true
})
</script>

<style>


/* Sayfa geçişleri için yumuşaklık */
.page-enter-active,
.page-leave-active {
  transition: all 0.3s;
}
.page-enter-from,
.page-leave-to {
  opacity: 0;
  filter: blur(1rem);
}
</style>
