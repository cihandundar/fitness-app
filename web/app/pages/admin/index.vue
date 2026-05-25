<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Admin Dashboard</h1>
        <div class="flex items-center space-x-3">
           <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 text-[10px] font-bold border border-red-500/20 uppercase tracking-widest">Sistem Durumu: Aktif</span>
        </div>
      </header>

      <div class="p-6 lg:p-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
          <div v-for="stat in stats" :key="stat.label" class="p-6 rounded-3xl bg-slate-900 border border-slate-800 hover:border-red-500/30 transition-all group overflow-hidden relative">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-slate-800/50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2 relative z-10">{{ stat.label }}</p>
            <div class="flex items-end justify-between relative z-10">
               <p class="text-3xl font-bold text-white">{{ stat.value }}</p>
               <div :class="stat.color" class="p-2 rounded-xl bg-opacity-10">
                  <component :is="stat.icon" class="w-6 h-6" />
               </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
           <!-- Recent Activity -->
           <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800">
              <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                 <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                 Hızlı Erişim
              </h2>
              <div class="grid grid-cols-2 gap-4">
                 <NuxtLink to="/admin/users" class="p-4 rounded-2xl bg-slate-800 hover:bg-slate-700 transition-all text-center">
                    <p class="text-white font-bold mb-1">Kullanıcılar</p>
                    <p class="text-xs text-slate-500">Rol & Durum Yönetimi</p>
                 </NuxtLink>
                 <NuxtLink to="/admin/programs" class="p-4 rounded-2xl bg-slate-800 hover:bg-slate-700 transition-all text-center">
                    <p class="text-white font-bold mb-1">Programlar</p>
                    <p class="text-xs text-slate-500">İçerik Oluşturma</p>
                 </NuxtLink>
                 <NuxtLink to="/admin/exercises" class="p-4 rounded-2xl bg-slate-800 hover:bg-slate-700 transition-all text-center">
                    <p class="text-white font-bold mb-1">Egzersizler</p>
                    <p class="text-xs text-slate-500">Kütüphane Yönetimi</p>
                 </NuxtLink>
                 <NuxtLink to="/admin/muscle-groups" class="p-4 rounded-2xl bg-slate-800 hover:bg-slate-700 transition-all text-center">
                    <p class="text-white font-bold mb-1">Kas Grupları</p>
                    <p class="text-xs text-slate-500">Target Alanları</p>
                 </NuxtLink>
                 <NuxtLink to="/admin/equipment" class="p-4 rounded-2xl bg-slate-800 hover:bg-slate-700 transition-all text-center">
                    <p class="text-white font-bold mb-1">Ekipmanlar</p>
                    <p class="text-xs text-slate-500">Ekipman Türleri</p>
                 </NuxtLink>
                 <NuxtLink to="/admin/branches" class="p-4 rounded-2xl bg-slate-800 hover:bg-slate-700 transition-all text-center">
                    <p class="text-white font-bold mb-1">Branşlar</p>
                    <p class="text-xs text-slate-500">Spor Branşları</p>
                 </NuxtLink>
                 <NuxtLink to="/admin/memberships" class="p-4 rounded-2xl bg-slate-800 hover:bg-slate-700 transition-all text-center">
                    <p class="text-white font-bold mb-1">Üyelikler</p>
                    <p class="text-xs text-slate-500">Paket Yönetimi</p>
                 </NuxtLink>
                 <NuxtLink to="/admin/payments" class="p-4 rounded-2xl bg-slate-800 hover:bg-slate-700 transition-all text-center">
                    <p class="text-white font-bold mb-1">Ödemeler</p>
                    <p class="text-xs text-slate-500">Tahsilat Takibi</p>
                 </NuxtLink>
              </div>
           </div>

           <!-- Welcome Section -->
           <div class="p-8 rounded-3xl bg-gradient-to-br from-red-500/20 to-orange-600/20 border border-red-500/20 flex flex-col justify-center relative overflow-hidden">
              <div class="absolute top-0 right-0 p-8 opacity-10">
                 <svg class="w-32 h-32 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
              </div>
              <h3 class="text-2xl font-bold text-white mb-2">Hoş Geldin Admin!</h3>
              <p class="text-slate-300 leading-relaxed max-w-md">Spor salonunuzun tüm operasyonlarını buradan yönetebilirsiniz. Kullanıcı rollerini değiştirin, yeni antrenman programları yayınlayın ve ödemeleri anlık olarak takip edin.</p>
           </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { 
  UsersIcon, 
  AcademicCapIcon, 
  FireIcon, 
  CurrencyDollarIcon 
} from '@heroicons/vue/24/outline'

definePageMeta({
  middleware: 'admin',
  layout: false
})

const api = useApi()
const stats = ref([
  { label: 'Toplam Üye', value: '...', icon: UsersIcon, color: 'text-blue-400', key: 'users' },
  { label: 'Aktif Program', value: '...', icon: AcademicCapIcon, color: 'text-purple-400', key: 'programs' },
  { label: 'Egzersizler', value: '...', icon: FireIcon, color: 'text-red-400', key: 'exercises' },
  { label: 'Ödemeler', value: '...', icon: CurrencyDollarIcon, color: 'text-emerald-400', key: 'payments' }
])

const fetchStats = async () => {
  try {
    const [u, pr, ex, py] = await Promise.all([
      api.get('/users'),
      api.get('/programs'),
      api.get('/exercises'),
      api.get('/payments')
    ])

    stats.value[0].value = u.data.data?.length || u.data?.length || 0
    stats.value[1].value = pr.data.data?.length || pr.data?.length || 0
    stats.value[2].value = ex.data.meta?.total ?? ex.data.data?.length ?? ex.data?.length ?? 0
    stats.value[3].value = py.data.data?.length || py.data?.length || 0
  } catch (e) {
    console.error('İstatistikler yüklenemedi', e)
  }
}

onMounted(fetchStats)
</script>
