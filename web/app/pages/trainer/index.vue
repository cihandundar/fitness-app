<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <!-- Trainer Sidebar -->
    <aside class="hidden lg:flex w-72 bg-slate-900/95 backdrop-blur-xl border-r border-slate-800/50 flex-col sticky top-0 h-screen">
      <div class="flex flex-col h-full">
        <div class="flex items-center h-16 px-6 border-b border-slate-800/50 flex-shrink-0">
          <NuxtLink to="/trainer" class="flex items-center space-x-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <span class="text-lg font-bold bg-gradient-to-r from-purple-400 to-indigo-400 bg-clip-text text-transparent">Trainer Panel</span>
          </NuxtLink>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
          <div class="space-y-1">
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Koçluk</p>
            
            <NuxtLink to="/trainer" class="flex items-center px-3 py-2.5 rounded-xl bg-purple-500/10 text-purple-400 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              <span class="ml-3">Genel Bakış</span>
            </NuxtLink>

            <NuxtLink to="/trainer/clients" class="flex items-center px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              <span class="ml-3">Öğrencilerim</span>
            </NuxtLink>

            <NuxtLink to="/trainer/appointments" class="flex items-center px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <span class="ml-3">Randevular</span>
            </NuxtLink>
          </div>

          <div class="mt-auto pt-6 border-t border-slate-800/50">
            <NuxtLink to="/dashboard" class="flex items-center px-3 py-2.5 rounded-xl text-violet-400 hover:bg-violet-500/10 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
              <span class="ml-3">Kullanıcı Paneli</span>
            </NuxtLink>
          </div>
        </nav>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 p-6 lg:p-8">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-white">Hoş Geldin, Koç!</h1>
        <p class="text-slate-400">Bugünkü antrenmanların ve öğrenci durumlarına göz at.</p>
      </header>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50">
          <p class="text-slate-400 text-sm mb-1">Toplam Öğrenci</p>
          <p class="text-3xl font-bold text-white">{{ stats.totalClients || 0 }}</p>
        </div>
        <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50">
          <p class="text-slate-400 text-sm mb-1">Bugünkü Randevular</p>
          <p class="text-3xl font-bold text-purple-400">0</p>
        </div>
        <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50">
          <p class="text-slate-400 text-sm mb-1">Bekleyen İstekler</p>
          <p class="text-3xl font-bold text-amber-400">0</p>
        </div>
      </div>

      <!-- Recent Clients Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50">
          <h2 class="text-xl font-bold text-white mb-4">Öğrencilerimin Son Aktiviteleri</h2>
          <div class="space-y-4">
            <p class="text-slate-500 text-sm italic">Henüz bir aktivite yok.</p>
          </div>
        </div>
        <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50">
          <h2 class="text-xl font-bold text-white mb-4">Hızlı Duyuru Yayınla</h2>
          <textarea placeholder="Öğrencilerine bir mesaj gönder..." class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white h-24 mb-4 outline-none focus:border-purple-500 transition-all"></textarea>
          <button class="w-full py-2.5 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl font-medium transition-all">Duyuruyu Paylaş</button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth', // trainer middleware da eklenebilir
  layout: false
})

const api = useApi()
const stats = ref({
  totalClients: 0
})

const fetchStats = async () => {
  try {
    const res = await api.get('/trainer/clients')
    stats.value.totalClients = res.data.data.length
  } catch (e) {
    console.error('Stats yüklenemedi')
  }
}

onMounted(fetchStats)
</script>
