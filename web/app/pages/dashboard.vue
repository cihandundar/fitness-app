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
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-16">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-2 border-violet-500 mx-auto"></div>
            <p class="mt-4 text-slate-400">Yükleniyor...</p>
          </div>

          <!-- No Active Membership Warning -->
          <div v-else-if="!loading && !isAdmin && (!membership || membership.status !== 'active')" class="text-center py-16">
            <div class="inline-flex w-20 h-20 rounded-full bg-amber-500/20 flex items-center justify-center mb-6">
              <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-3">Üyelik Onayı Bekleniyor</h2>
            <p class="text-slate-400 mb-6 max-w-md mx-auto">
              {{ membership?.status === 'pending' ? 'Üyeliğiniz admin tarafından inceleniyor. Onaylandığında dashboard\'a erişim sağlayabileceksiniz.' : 'Aktif bir üyeliğiniz bulunmuyor.' }}
            </p>
            <div class="flex justify-center gap-3">
              <NuxtLink to="/" class="inline-flex items-center px-6 py-3 rounded-xl bg-slate-800 text-white font-medium hover:bg-slate-700 transition-colors">
                Anasayfaya Dön
              </NuxtLink>
              <NuxtLink to="/memberships" class="inline-flex items-center px-6 py-3 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white font-medium hover:shadow-lg transition-all">
                Üyelik Seç
              </NuxtLink>
            </div>
          </div>

          <!-- Active Member Dashboard Content -->
          <template v-else>
          <!-- Welcome Section -->
          <div class="mb-6 sm:mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">
              Merhaba, {{ authStore.user?.name?.split(' ')[0] }}! 👋
            </h2>
            <p class="text-slate-400">Bugün antrenman yapmaya hazır mısın?</p>
          </div>

          <!-- Quick Stats -->
          <div v-if="isRegularUser" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6 sm:mb-8">
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

            <div class="p-3 sm:p-4 lg:p-6 rounded-xl sm:rounded-2xl bg-slate-900/50 border transition-all" :class="isExpiringSoon ? 'border-amber-500/30' : 'border-slate-800/50 hover:border-violet-500/30'">
              <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-lg sm:rounded-xl flex items-center justify-center" :class="isExpiringSoon ? 'bg-amber-500/20' : 'bg-blue-500/20'">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" :class="isExpiringSoon ? 'text-amber-400' : 'text-blue-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <span v-if="isExpiringSoon" class="text-xs sm:text-sm text-amber-400 font-medium">Son!</span>
              </div>
              <p class="text-xs sm:text-sm text-slate-400">Kalan Süre</p>
              <p class="text-lg sm:text-xl lg:text-2xl font-bold" :class="isExpiringSoon ? 'text-amber-400' : 'text-white'">{{ remainingDays }} Gün</p>
              <p class="text-xs text-slate-500 mt-1">{{ membership?.plan?.name || 'Premium' }}</p>
            </div>
          </div>

          <!-- Admin/Trainer Stats -->
          <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-violet-500/30 transition-all">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-xl bg-violet-500/20 flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-2xl font-bold text-white">{{ authStore.user?.role === 'trainer' ? 'Öğrencilerim' : 'Yönetim Paneli' }}</p>
                  <p class="text-sm text-slate-400">{{ authStore.user?.role === 'trainer' ? 'Danışanlarınızı yönetin' : 'Sistemi yönetin' }}</p>
                </div>
              </div>
              <NuxtLink :to="authStore.user?.role === 'trainer' ? '/trainer/clients' : '/admin/users'" class="block w-full text-center py-3 bg-violet-500/20 text-violet-400 rounded-xl hover:bg-violet-500/30 transition-all">
                {{ authStore.user?.role === 'trainer' ? 'Öğrencilerimi Gör' : 'Kullanıcıları Yönet' }}
              </NuxtLink>
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-purple-500/30 transition-all">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                </div>
                <div>
                  <p class="text-2xl font-bold text-white">Programlar</p>
                  <p class="text-sm text-slate-400">Fitness programları</p>
                </div>
              </div>
              <NuxtLink to="/programs" class="block w-full text-center py-3 bg-purple-500/20 text-purple-400 rounded-xl hover:bg-purple-500/30 transition-all">
                Programları Yönet
              </NuxtLink>
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-emerald-500/30 transition-all">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-2xl font-bold text-white">Raporlar</p>
                  <p class="text-sm text-slate-400">İstatistikler ve analiz</p>
                </div>
              </div>
              <NuxtLink to="/progress" class="block w-full text-center py-3 bg-emerald-500/20 text-emerald-400 rounded-xl hover:bg-emerald-500/30 transition-all">
                İlerleme Takibi
              </NuxtLink>
            </div>
          </div>

          <!-- Charts & Quick Actions -->
           <div v-if="isRegularUser" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
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
          </template>
        </div>
      </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'

definePageMeta({
  middleware: 'auth',
  layout: false,
  title: 'Dashboard - FitApp'
})

const router = useRouter()
const authStore = useAuthStore()
const api = useApi()
const sidebarOpen = ref(false)
const recentWorkouts = ref([])
const loading = ref(true)
const membership = ref<any>(null)

const userInitials = computed(() => {
  if (!authStore.user?.name) return 'U'
  return authStore.user.name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2)
})

const isAdmin = computed(() => {
  return authStore.user?.role === 'admin' || authStore.user?.role === 'super_admin'
})

const isRegularUser = computed(() => {
  return authStore.user?.role === 'user'
})

const fetchRecentWorkouts = async () => {
  loading.value = true
  try {
    // Admin kullanıcıysa üyelik kontrolü yapma
    const isAdminUser = authStore.user?.role === 'admin' || authStore.user?.role === 'super_admin'

    if (!isAdminUser) {
      // Önce üyeliği kontrol et
      const membershipRes = await api.get('/my-membership')
      membership.value = membershipRes.data?.data || membershipRes.data

      console.log('Dashboard - Membership:', membership.value)

      // Üyelik yok veya pending/rejected ise uyarı göster
      if (!membership.value || membership.value.status === 'pending' || membership.value.status === 'rejected') {
        return
      }
    }

    // Admin veya Active üyelik var - antrenmanları yükle
    const res = await api.get('/workout-tracking/history')
    recentWorkouts.value = res.data.data.data.slice(0, 5)
  } catch (e) {
    console.error('Antrenmanlar yüklenemedi', e)
  } finally {
    loading.value = false
  }
}

const remainingDays = computed(() => {
  if (!membership.value?.end_date) return 0
  const endDate = new Date(membership.value.end_date)
  const now = new Date()
  const diff = endDate.getTime() - now.getTime()
  return Math.max(0, Math.floor(diff / (1000 * 60 * 60 * 24)))
})

const isExpiringSoon = computed(() => {
  return remainingDays.value > 0 && remainingDays.value <= 7
})

const formatDate = (d: string) => {
  if (!d) return ''
  return new Date(d).toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' })
}

const openSidebar = () => sidebarOpen.value = true
const closeSidebar = () => sidebarOpen.value = false

onMounted(fetchRecentWorkouts)

</script>
