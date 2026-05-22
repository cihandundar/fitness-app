<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <AppSidebar :is-open="sidebarOpen" @close="closeSidebar" />
    
    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto min-w-0 bg-slate-950 lg:pl-72">
        <!-- Mobile Header -->
        <header class="lg:hidden sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50">
          <div class="flex items-center justify-between px-4 sm:px-6 h-16">
            <button
              @click="openSidebar"
              class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
            </button>

            <h1 class="text-xl font-semibold text-white">Dashboard</h1>

            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
              <span class="text-white font-medium text-sm">{{ userInitials }}</span>
            </div>
          </div>
        </header>

        <!-- Desktop Header -->
        <header class="hidden lg:flex sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 items-center px-6 lg:px-8 justify-between w-full">
            <h1 class="text-xl font-semibold text-white">Dashboard</h1>
            <div class="flex items-center space-x-4">
               <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                <span class="text-white font-medium text-sm">{{ userInitials }}</span>
              </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="p-4 sm:p-6 lg:p-8">
          <!-- Welcome Section -->
          <div class="mb-6 sm:mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">
              Merhaba, {{ authStore.user?.name?.split(' ')[0] }}! 👋
            </h2>
            <p class="text-slate-400">Bugün antrenman yapmaya hazır mısın?</p>
          </div>

          <!-- Quick Stats -->
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6 sm:mb-8">
            <div class="p-3 sm:p-4 lg:p-6 rounded-xl sm:rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-violet-500/30 transition-all">
              <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-lg sm:rounded-xl bg-violet-500/20 flex items-center justify-center">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                </div>
                <span class="text-xs sm:text-sm text-green-400 font-medium">+2</span>
              </div>
              <p class="text-xs sm:text-sm text-slate-400">Haftalık Antrenman</p>
              <p class="text-lg sm:text-xl lg:text-2xl font-bold text-white">4 Seans</p>
            </div>
            
            <div class="p-3 sm:p-4 lg:p-6 rounded-xl sm:rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-violet-500/30 transition-all">
              <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-lg sm:rounded-xl bg-emerald-500/20 flex items-center justify-center">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                  </svg>
                </div>
              </div>
              <p class="text-xs sm:text-sm text-slate-400">Yakılan Kalori</p>
              <p class="text-lg sm:text-xl lg:text-2xl font-bold text-white">1,240 kcal</p>
            </div>

            <div class="p-3 sm:p-4 lg:p-6 rounded-xl sm:rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-violet-500/30 transition-all">
              <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-lg sm:rounded-xl bg-orange-500/20 flex items-center justify-center">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <p class="text-xs sm:text-sm text-slate-400">Toplam Süre</p>
              <p class="text-lg sm:text-xl lg:text-2xl font-bold text-white">180 dk</p>
            </div>

            <div class="p-3 sm:p-4 lg:p-6 rounded-xl sm:rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-violet-500/30 transition-all">
              <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-lg sm:rounded-xl bg-blue-500/20 flex items-center justify-center">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                </div>
              </div>
              <p class="text-xs sm:text-sm text-slate-400">Aktif Üyelik</p>
              <p class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Premium</p>
            </div>
          </div>

          <!-- Charts & Quick Actions -->
           <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
              <div class="xl:col-span-2 p-6 rounded-3xl bg-slate-900/50 border border-slate-800/50">
                 <h3 class="text-lg font-semibold text-white mb-6">Aktivite Grafiği</h3>
                 <div class="h-64 flex items-end justify-between px-2 gap-2">
                    <div v-for="h in [40, 70, 45, 90, 65, 30, 10]" :key="h" class="flex-1 bg-violet-500/20 rounded-t-lg hover:bg-violet-500/40 transition-all cursor-pointer relative group" :style="{ height: h + '%' }">
                       <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                         {{ h*10 }} kcal
                       </div>
                    </div>
                 </div>
                 <div class="flex justify-between mt-4 text-xs text-slate-500">
                    <span>Pzt</span><span>Sal</span><span>Çar</span><span>Per</span><span>Cum</span><span>Cmt</span><span>Paz</span>
                 </div>
              </div>
              <div class="p-6 rounded-3xl bg-slate-900/50 border border-slate-800/50">
                 <h3 class="text-lg font-semibold text-white mb-6">Hızlı İşlemler</h3>
                 <div class="space-y-3">
                    <NuxtLink to="/progress" class="flex items-center p-3 rounded-2xl bg-slate-800/50 hover:bg-slate-800 transition-all group">
                       <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                          <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                       </div>
                       <span class="text-white font-medium">İlerleme Kaydet</span>
                    </NuxtLink>
                    <NuxtLink to="/programs" class="flex items-center p-3 rounded-2xl bg-slate-800/50 hover:bg-slate-800 transition-all group">
                       <div class="w-10 h-10 rounded-xl bg-violet-500/20 flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                          <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                       </div>
                       <span class="text-white font-medium">Programlarımı Gör</span>
                    </NuxtLink>
                 </div>

                 <h3 class="text-lg font-semibold text-white mt-8 mb-6">Son Antrenmanlar</h3>
                 <div class="space-y-4">
                    <div v-for="workout in recentWorkouts" :key="workout.id" class="flex items-center justify-between p-3 rounded-2xl bg-slate-800/30">
                       <div>
                          <p class="text-white text-sm font-bold">{{ workout.workout?.title || 'Serbest Antrenman' }}</p>
                          <p class="text-[10px] text-slate-500 font-bold uppercase">{{ formatDate(workout.completed_at) }}</p>
                       </div>
                       <span class="text-emerald-400 font-mono text-xs">Tamamlandı</span>
                    </div>
                    <div v-if="recentWorkouts.length === 0" class="text-center py-4">
                       <p class="text-slate-600 text-xs italic">Henüz antrenman kaydı yok.</p>
                    </div>
                 </div>
              </div>
           </div>
        </div>
      </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

definePageMeta({
  middleware: 'auth',
  layout: false,
})

const authStore = useAuthStore()
const api = useApi()
const sidebarOpen = ref(false)
const recentWorkouts = ref([])

const userInitials = computed(() => {
  if (!authStore.user?.name) return 'U'
  return authStore.user.name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2)
})

const fetchRecentWorkouts = async () => {
  try {
    const res = await api.get('/workout-tracking/history')
    recentWorkouts.value = res.data.data.data.slice(0, 5)
  } catch (e) {
    console.error('Antrenmanlar yüklenemedi')
  }
}

const formatDate = (d: string) => {
  if (!d) return ''
  return new Date(d).toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' })
}

const openSidebar = () => sidebarOpen.value = true
const closeSidebar = () => sidebarOpen.value = false

onMounted(fetchRecentWorkouts)
</script>
