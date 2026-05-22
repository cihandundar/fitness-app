<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <!-- Sidebar -->
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
          <NuxtLink to="/trainer" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="ml-3">Genel Bakış</span>
          </NuxtLink>
          <NuxtLink to="/trainer/clients" class="flex items-center px-3 py-2.5 rounded-xl bg-purple-500/10 text-purple-400 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="ml-3">Öğrencilerim</span>
          </NuxtLink>
        </nav>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 p-6 lg:p-8">
      <header class="mb-8 flex items-center justify-between">
        <div class="flex items-center">
          <NuxtLink to="/trainer/clients" class="mr-4 p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </NuxtLink>
          <div>
            <h1 class="text-3xl font-bold text-white">{{ client?.name }} - Gelişim Analizi</h1>
            <p class="text-slate-400">Öğrencinizin kilo ve antrenman geçmişini izleyin.</p>
          </div>
        </div>
      </header>

      <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-purple-500"></div>
      </div>

      <div v-else class="space-y-8">
        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50">
            <p class="text-slate-400 text-sm mb-1">Güncel Kilo</p>
            <p class="text-3xl font-bold text-white">{{ currentWeight }} kg</p>
          </div>
          <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50">
            <p class="text-slate-400 text-sm mb-1">Hedef Kilo</p>
            <p class="text-3xl font-bold text-slate-500">--</p>
          </div>
          <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50">
            <p class="text-slate-400 text-sm mb-1">Değişim</p>
            <p class="text-3xl font-bold" :class="weightDiff < 0 ? 'text-green-400' : 'text-red-400'">{{ weightDiff }} kg</p>
          </div>
          <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50">
            <p class="text-slate-400 text-sm mb-1">Kayıt Sayısı</p>
            <p class="text-3xl font-bold text-purple-400">{{ logs.length }}</p>
          </div>
        </div>

        <!-- Weight Chart (Simple SVG) -->
        <div class="p-6 rounded-3xl bg-slate-900/50 border border-slate-800/50">
          <h2 class="text-xl font-bold text-white mb-6">Kilo Değişim Grafiği</h2>
          <div class="h-64 w-full relative group">
            <svg v-if="logs.length > 1" class="w-full h-full" viewBox="0 0 1000 200" preserveAspectRatio="none">
              <!-- Y-Axis Grid Lines -->
              <line v-for="i in 4" :key="i" x1="0" :y1="i*50" x2="1000" :y2="i*50" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
              
              <!-- Connection Line -->
              <polyline
                fill="none"
                stroke="url(#gradient)"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
                :points="chartPoints"
                class="drop-shadow-[0_0_8px_rgba(168,85,247,0.4)]"
              />
              
              <!-- Area Fill -->
              <polygon
                :points="`0,200 ${chartPoints} 1000,200`"
                fill="url(#area-gradient)"
                opacity="0.2"
              />

              <defs>
                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                  <stop offset="0%" stop-color="#a855f7" />
                  <stop offset="100%" stop-color="#6366f1" />
                </linearGradient>
                <linearGradient id="area-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="#a855f7" />
                  <stop offset="100%" stop-color="transparent" />
                </linearGradient>
              </defs>
            </svg>
            <div v-else class="flex items-center justify-center h-full text-slate-500 italic">
              Grafik oluşturmak için en az 2 kayıt gereklidir.
            </div>
          </div>
          <div class="flex justify-between mt-4 px-2">
            <span class="text-xs text-slate-500">{{ firstDate }}</span>
            <span class="text-xs text-slate-500">{{ lastDate }}</span>
          </div>
        </div>

        <!-- Progress Logs Table -->
        <div class="bg-slate-900/50 border border-slate-800/50 rounded-2xl overflow-hidden">
          <div class="p-6 border-b border-slate-800/50">
            <h2 class="text-xl font-bold text-white">Gelişim Kayıtları</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-slate-800/50 bg-slate-900/80">
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm">Tarih</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm">Kilo</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm">Vücut Yağ Oranı</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm">Notlar</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/50">
                <tr v-for="log in sortedLogs" :key="log.id" class="hover:bg-slate-800/30 transition-colors">
                  <td class="px-6 py-4 text-slate-300">
                    {{ new Date(log.created_at).toLocaleDateString('tr-TR') }}
                  </td>
                  <td class="px-6 py-4 text-white font-bold">{{ log.weight }} kg</td>
                  <td class="px-6 py-4 text-slate-400">{{ log.body_fat_percentage ? log.body_fat_percentage + '%' : '-' }}</td>
                  <td class="px-6 py-4 text-slate-500 text-sm truncate max-w-xs">{{ log.notes || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const api = useApi()

const client = ref(null)
const logs = ref([])
const loading = ref(true)

const fetchData = async () => {
  try {
    const clientId = route.params.id
    const res = await api.get(`/trainer/client-progress/${clientId}`)
    client.value = res.data.data.client
    logs.value = res.data.data.logs
  } catch (e) {
    console.error('Veriler yüklenemedi')
  } finally {
    loading.value = false
  }
}

const sortedLogs = computed(() => {
  return [...logs.value].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
})

const currentWeight = computed(() => {
  return sortedLogs.value[0]?.weight || 0
})

const weightDiff = computed(() => {
  if (logs.value.length < 2) return 0
  const first = logs.value[0].weight
  const last = logs.value[logs.value.length - 1].weight
  return (currentWeight.value - first).toFixed(1)
})

const firstDate = computed(() => logs.value[0] ? new Date(logs.value[0].created_at).toLocaleDateString() : '')
const lastDate = computed(() => logs.value[logs.value.length - 1] ? new Date(logs.value[logs.value.length - 1].created_at).toLocaleDateString() : '')

// SVG Chart Point Logic
const chartPoints = computed(() => {
  if (logs.value.length < 2) return ''
  
  const weights = logs.value.map(l => parseFloat(l.weight))
  const minW = Math.min(...weights) - 2
  const maxW = Math.max(...weights) + 2
  const range = maxW - minW
  
  return logs.value.map((log, index) => {
    const x = (index / (logs.value.length - 1)) * 1000
    const y = 200 - ((log.weight - minW) / range) * 200
    return `${x},${y}`
  }).join(' ')
})

onMounted(fetchData)
</script>
