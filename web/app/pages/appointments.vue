<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <AppSidebar />
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 lg:pl-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between">
        <h1 class="text-xl font-semibold text-white">Randevularım</h1>
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
             <span class="text-white font-medium text-sm">SA</span>
        </div>
      </header>
      
      <div class="p-6 lg:p-8">
        <div class="flex justify-between items-center mb-8">
           <h2 class="text-3xl font-bold text-white">Seans Randevuları</h2>
           <button @click="isModalOpen = true" class="px-5 py-2.5 bg-violet-500 text-white rounded-xl font-medium hover:bg-violet-600 transition-all flex items-center">
             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
             Yeni Randevu Al
           </button>
        </div>

        <div class="space-y-4">
           <div v-for="app in appointments" :key="app.id" class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex items-center">
                 <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center mr-4">
                   <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                 </div>
                 <div>
                   <p class="text-white font-bold">{{ app.trainer?.name }}</p>
                   <p class="text-sm text-slate-500">Kişisel Eğitmen (PT)</p>
                 </div>
              </div>
              <div class="flex flex-col md:items-end">
                 <p class="text-white">{{ formatDate(app.start_time) }}</p>
                 <p class="text-sm text-slate-500">{{ formatTime(app.start_time) }} - {{ formatTime(app.end_time) }}</p>
              </div>
              <div>
                 <span :class="statusClass(app.status)" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                   {{ app.status }}
                 </span>
              </div>
           </div>
           
           <div v-if="appointments.length === 0" class="py-20 text-center text-slate-500 italic">
             Henüz bir randevun bulunmuyor.
           </div>
        </div>
      </div>

      <!-- New Appointment Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden p-6">
          <h3 class="text-xl font-bold text-white mb-6">Randevu Talebi</h3>
          <form @submit.prevent="saveAppointment" class="space-y-4">
            <div>
              <label class="block text-sm text-slate-400 mb-1">Eğitmen Seçin</label>
              <select v-model="form.trainer_id" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white">
                <option v-for="trainer in trainers" :key="trainer.id" :value="trainer.id">{{ trainer.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-slate-400 mb-1">Tarih ve Saat</label>
              <input v-model="form.start_time" type="datetime-local" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white">
            </div>
            <p class="text-[10px] text-slate-500 italic">Not: Randevu süresi standart 60 dakikadır.</p>
            <div class="pt-4 flex gap-3">
              <button type="button" @click="isModalOpen = false" class="flex-1 py-3 bg-slate-800 text-slate-400 rounded-xl font-medium">İptal</button>
              <button type="submit" class="flex-1 py-3 bg-violet-500 text-white rounded-xl font-bold">Talep Gönder</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: ['auth', 'active-member'],
  layout: false
})

const api = useApi()
const appointments = ref([])
const trainers = ref([])
const isModalOpen = ref(false)

const form = ref({
  trainer_id: null,
  start_time: '',
})

const fetchData = async () => {
  try {
    const [appRes, trainerRes] = await Promise.all([
      api.get('/appointments'),
      api.get('/users') // Admin endpoint'i ama user listesi için trainer filtreli gerekebilir, şimdilik hepsi
    ])
    appointments.value = appRes.data.data
    trainers.value = trainerRes.data.data.filter(u => u.role === 'trainer')
  } catch (e) {
    console.error('Veriler yüklenemedi')
  }
}

const formatDate = (d: string) => new Date(d).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long' })
const formatTime = (d: string) => new Date(d).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })

const statusClass = (status: string) => {
  switch (status) {
    case 'confirmed': return 'bg-green-500/10 text-green-400 border border-green-500/20'
    case 'pending': return 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
    case 'cancelled': return 'bg-red-500/10 text-red-400 border border-red-500/20'
    default: return 'bg-slate-500/10 text-slate-400'
  }
}

const saveAppointment = async () => {
  try {
    const startTime = new Date(form.value.start_time)
    const endTime = new Date(startTime.getTime() + 60 * 60 * 1000) // +1 hour
    
    await api.post('/appointments', {
      trainer_id: form.value.trainer_id,
      start_time: startTime.toISOString().slice(0, 19).replace('T', ' '),
      end_time: endTime.toISOString().slice(0, 19).replace('T', ' ')
    })
    
    await fetchData()
    isModalOpen.value = false
    alert('Randevu talebi gönderildi!')
  } catch (e) {
    alert(e.response?.data?.message || 'Hata oluştu')
  }
}

onMounted(fetchData)
</script>
