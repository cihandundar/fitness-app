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
          <NuxtLink to="/trainer/clients" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="ml-3">Öğrencilerim</span>
          </NuxtLink>
          <NuxtLink to="/trainer/appointments" class="flex items-center px-3 py-2.5 rounded-xl bg-purple-500/10 text-purple-400 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="ml-3">Randevular</span>
          </NuxtLink>
        </nav>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 p-6 lg:p-8">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-white">Randevu Takvimi</h1>
        <p class="text-slate-400">Gelecek antrenman seanslarını yönetin ve talepleri onaylayın.</p>
      </header>

      <!-- Appointments List -->
      <div class="bg-slate-900/50 border border-slate-800/50 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-800/50 bg-slate-900/80">
                <th class="px-6 py-4 text-slate-400 font-medium text-sm">Öğrenci</th>
                <th class="px-6 py-4 text-slate-400 font-medium text-sm">Tarih / Saat</th>
                <th class="px-6 py-4 text-slate-400 font-medium text-sm">Durum</th>
                <th class="px-6 py-4 text-slate-400 font-medium text-sm text-right">İşlemler</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
              <tr v-for="appointment in appointments" :key="appointment.id" class="hover:bg-slate-800/30 transition-colors">
                <td class="px-6 py-4">
                  <div class="text-white font-medium">{{ appointment.user?.name }}</div>
                  <div class="text-xs text-slate-500">{{ appointment.user?.email }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-slate-200">{{ formatDate(appointment.start_time) }}</div>
                  <div class="text-xs text-slate-500">{{ formatTime(appointment.start_time) }} - {{ formatTime(appointment.end_time) }}</div>
                </td>
                <td class="px-6 py-4">
                  <span :class="statusClass(appointment.status)" class="px-2.5 py-1 rounded-lg text-xs font-semibold capitalize">
                    {{ appointment.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <template v-if="appointment.status === 'pending'">
                    <button @click="updateStatus(appointment.id, 'confirmed')" class="px-3 py-1.5 bg-green-500/10 text-green-400 rounded-lg text-xs font-bold hover:bg-green-500 hover:text-white transition-all">Onayla</button>
                    <button @click="updateStatus(appointment.id, 'cancelled')" class="px-3 py-1.5 bg-red-500/10 text-red-400 rounded-lg text-xs font-bold hover:bg-red-500 hover:text-white transition-all">Reddet</button>
                  </template>
                  <button v-else @click="deleteAppointment(appointment.id)" class="p-2 text-slate-500 hover:text-red-400 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
              <tr v-if="appointments.length === 0">
                <td colspan="4" class="px-6 py-12 text-center text-slate-500 italic">
                  Henüz bir randevu kaydı bulunmuyor.
                </td>
              </tr>
            </tbody>
          </table>
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
const appointments = ref([])

const fetchAppointments = async () => {
  try {
    const res = await api.get('/appointments')
    appointments.value = res.data.data
  } catch (e) {
    console.error('Randevular yüklenemedi')
  }
}

const statusClass = (status: string) => {
  switch (status) {
    case 'confirmed': return 'bg-green-500/10 text-green-400 border border-green-500/20'
    case 'pending': return 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
    case 'cancelled': return 'bg-red-500/10 text-red-400 border border-red-500/20'
    case 'completed': return 'bg-blue-500/10 text-blue-400 border border-blue-500/20'
    default: return 'bg-slate-500/10 text-slate-400'
  }
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' })
}

const formatTime = (dateStr: string) => {
  return new Date(dateStr).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })
}

const updateStatus = async (id: number, status: string) => {
  try {
    await api.put(`/appointments/${id}`, { status })
    await fetchAppointments()
  } catch (e) {
    alert('Hata oluştu')
  }
}

const deleteAppointment = async (id: number) => {
  if (confirm('Bu randevuyu silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/appointments/${id}`)
      await fetchAppointments()
    } catch (e) {
      alert('Silinemedi')
    }
  }
}

onMounted(fetchAppointments)
</script>
