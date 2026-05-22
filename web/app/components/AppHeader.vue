<template>
  <header class="bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center space-x-2 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/20 group-hover:shadow-violet-500/40 transition-all">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.57 14.86L22 13.43L20.57 12L17 15.57L8.43 7L12 3.43L10.57 2L9.14 3.43L7.71 2L5.57 4.14L4.14 2.71L2.71 4.14L4.14 5.57L2 7.71L3.43 9.14L2 10.57L3.43 12L7 8.43L15.57 17L12 20.57L13.43 22L14.86 20.57L16.29 22L18.43 19.86L19.86 21.29L21.29 19.86L19.86 18.43L22 16.29L20.57 14.86Z"/>
            </svg>
          </div>
          <span class="text-xl font-bold bg-gradient-to-r from-violet-400 to-purple-400 bg-clip-text text-transparent">FitApp</span>
        </NuxtLink>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-1">
          <template v-if="authStore.isLoggedIn">
            <NuxtLink to="/dashboard" class="px-4 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all font-medium">
              Dashboard
            </NuxtLink>
            <NuxtLink to="/programs" class="px-4 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all font-medium">
              Programlar
            </NuxtLink>
            <NuxtLink to="/exercises" class="px-4 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all font-medium">
              Egzersizler
            </NuxtLink>
            <NuxtLink to="/progress" class="px-4 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all font-medium">
              İlerleme
            </NuxtLink>
          </template>
        </nav>

        <!-- Auth Buttons / User Menu -->
        <div class="flex items-center space-x-3">
          <template v-if="authStore.isLoggedIn">
            <div class="flex items-center space-x-3">
              <div class="text-right hidden sm:block">
                <p class="text-sm font-medium text-slate-200">{{ authStore.user?.name }}</p>
                <p class="text-xs text-slate-500 capitalize">{{ authStore.user?.role }}</p>
              </div>
              <div class="relative">
                <button @click="showUserMenu = !showUserMenu" class="flex items-center space-x-2 focus:outline-none">
                  <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
                    <span class="text-white font-medium text-sm">{{ initials }}</span>
                  </div>
                </button>
                <!-- Dropdown Menu -->
                <div v-if="showUserMenu" class="absolute right-0 mt-2 w-48 bg-slate-900 rounded-2xl shadow-2xl shadow-black/50 border border-slate-800 py-2 z-50 backdrop-blur-xl">
                  <NuxtLink to="/profile" class="block px-4 py-2.5 text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">
                    Profil
                  </NuxtLink>
                  <NuxtLink to="/settings" class="block px-4 py-2.5 text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">
                    Ayarlar
                  </NuxtLink>
                  <hr class="my-2 border-slate-800">
                  <button @click="handleLogout" class="w-full text-left px-4 py-2.5 text-red-400 hover:text-red-300 hover:bg-slate-800/50 transition-all">
                    Çıkış Yap
                  </button>
                </div>
              </div>
            </div>
          </template>
          <template v-else>
            <NuxtLink to="/login" class="px-4 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all font-medium">
              Giriş
            </NuxtLink>
            <NuxtLink to="/register" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:shadow-violet-500/40 transition-all">
              Kayıt Ol
            </NuxtLink>
          </template>
        </div>

        <!-- Mobile Menu Button -->
        <button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="showMobileMenu" class="md:hidden bg-slate-900/95 backdrop-blur-xl border-t border-slate-800/50">
      <div class="px-4 py-3 space-y-1">
        <template v-if="authStore.isLoggedIn">
          <NuxtLink to="/dashboard" class="block px-4 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">Dashboard</NuxtLink>
          <NuxtLink to="/programs" class="block px-4 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">Programlar</NuxtLink>
          <NuxtLink to="/exercises" class="block px-4 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">Egzersizler</NuxtLink>
          <NuxtLink to="/progress" class="block px-4 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">İlerleme</NuxtLink>
          <NuxtLink to="/profile" class="block px-4 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">Profil</NuxtLink>
          <button @click="handleLogout" class="w-full text-left px-4 py-2.5 rounded-xl text-red-400 hover:text-red-300 hover:bg-slate-800/50 transition-all">Çıkış Yap</button>
        </template>
        <template v-else>
          <NuxtLink to="/login" class="block px-4 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">Giriş</NuxtLink>
          <NuxtLink to="/register" class="block px-4 py-2.5 rounded-xl text-violet-400 hover:text-violet-300 hover:bg-slate-800/50 transition-all">Kayıt Ol</NuxtLink>
        </template>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const authStore = useAuthStore()
const showUserMenu = ref(false)
const showMobileMenu = ref(false)

const initials = computed(() => {
  if (!authStore.user?.name) return 'U'
  return authStore.user.name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2)
})

const handleLogout = async () => {
  await authStore.logout()
  await navigateTo('/login')
  showUserMenu.value = false
  showMobileMenu.value = false
}
</script>
