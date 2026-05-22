<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <AppSidebar />
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 lg:pl-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between">
        <h1 class="text-xl font-semibold text-white">İlerleme</h1>
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
             <span class="text-white font-medium text-sm">SA</span>
        </div>
      </header>
      
      <div class="p-6 lg:p-8">
        <div class="flex justify-between items-center mb-8">
           <h2 class="text-3xl font-bold text-white">Gelişimini İzle</h2>
           <button @click="isModalOpen = true" class="px-5 py-2.5 bg-violet-500 text-white rounded-xl font-medium hover:bg-violet-600 transition-all flex items-center">
             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
             Yeni Kayıt
           </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-slate-500 text-sm mb-1">Başlangıç</p>
            <p class="text-2xl font-bold text-white">{{ firstWeight }} kg</p>
          </div>
          <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-slate-500 text-sm mb-1">Güncel</p>
            <p class="text-2xl font-bold text-violet-400">{{ currentWeight }} kg</p>
          </div>
          <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-slate-500 text-sm mb-1">Toplam Değişim</p>
            <p class="text-2xl font-bold text-green-400">{{ (currentWeight - firstWeight).toFixed(1) }} kg</p>
          </div>
          <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-slate-500 text-sm mb-1">Kayıt Sayısı</p>
            <p class="text-2xl font-bold text-white">{{ logs.length }}</p>
          </div>
        </div>

        <!-- Progress Table -->
        <div class="bg-slate-900/50 border border-slate-800/50 rounded-2xl overflow-hidden">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-slate-900/80 border-b border-slate-800">
                <th class="px-6 py-4 text-slate-400 font-medium">Tarih</th>
                <th class="px-6 py-4 text-slate-400 font-medium">Kilo</th>
                <th class="px-6 py-4 text-slate-400 font-medium">Yağ %</th>
                <th class="px-6 py-4 text-slate-400 font-medium text-right">İşlem</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-800/30 transition-all">
                <td class="px-6 py-4 text-slate-300">{{ new Date(log.created_at).toLocaleDateString('tr-TR') }}</td>
                <td class="px-6 py-4 text-white font-bold">{{ log.weight }} kg</td>
                <td class="px-6 py-4 text-slate-400">{{ log.body_fat_percentage || '-' }}%</td>
                <td class="px-6 py-4 text-right">
                  <button @click="deleteLog(log.id)" class="text-slate-600 hover:text-red-500 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Log Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden p-6">
          <h3 class="text-xl font-bold text-white mb-6">Yeni Gelişim Kaydı</h3>
          <form @submit.prevent="saveLog" class="space-y-4">
            <div>
              <label class="block text-sm text-slate-400 mb-1">Kilo (kg)</label>
              <input v-model="form.weight" type="number" step="0.1" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white outline-none focus:border-violet-500">
            </div>
            <div>
              <label class="block text-sm text-slate-400 mb-1">Vücut Yağ Oranı (%)</label>
              <input v-model="form.body_fat_percentage" type="number" step="0.1" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white outline-none focus:border-violet-500">
            </div>
            <div class="pt-4 flex gap-3">
              <button type="button" @click="isModalOpen = false" class="flex-1 py-3 bg-slate-800 text-slate-400 rounded-xl font-medium">İptal</button>
              <button type="submit" class="flex-1 py-3 bg-violet-500 text-white rounded-xl font-bold">Kaydet</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
  layout: false
})

const api = useApi()
const logs = ref([])
const isModalOpen = ref(false)
const form = ref({ weight: 0, body_fat_percentage: null })

const fetchLogs = async () => {
  try {
    const res = await api.get('/progress-logs')
    logs.value = res.data.data
  } catch (e) {
    console.error('Kayıtlar yüklenemedi')
  }
}

const firstWeight = computed(() => logs.value[logs.value.length - 1]?.weight || 0)
const currentWeight = computed(() => logs.value[0]?.weight || 0)

const saveLog = async () => {
  try {
    await api.post('/progress-logs', form.value)
    await fetchLogs()
    isModalOpen.value = false
    form.value = { weight: 0, body_fat_percentage: null }
  } catch (e) {
    alert('Hata oluştu')
  }
}

const deleteLog = async (id: number) => {
  if (confirm('Silmek istediğine emin misin?')) {
    try {
      await api.delete(`/progress-logs/${id}`)
      await fetchLogs()
    } catch (e) {
      alert('Silinemedi')
    }
  }
}

onMounted(fetchLogs)
</script>
