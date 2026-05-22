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
      <header class="mb-8 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-white">Öğrencilerim</h1>
          <p class="text-slate-400">Danışanlarını yönet, program ata ve gelişimlerini takip et.</p>
        </div>
        <button @click="openAddClientModal" class="flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-purple-500/20 transition-all">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Öğrenci Ekle
        </button>
      </header>

      <!-- Clients List -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-purple-500"></div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div v-for="client in clients" :key="client.id" class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-purple-500/50 transition-all group relative overflow-hidden">
          <!-- Background Shape -->
          <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-purple-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>

          <div class="flex items-center mb-4 relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center mr-4 overflow-hidden">
              <img v-if="client.avatar" :src="client.avatar" class="w-full h-full object-cover">
              <svg v-else class="w-8 h-8 text-slate-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </div>
            <div class="flex-1">
              <h3 class="text-white font-bold text-lg">{{ client.name }}</h3>
              <p class="text-xs text-slate-500">{{ client.email }}</p>
            </div>
            <div class="text-right">
              <span class="px-3 py-1 rounded-lg text-xs font-bold"
                :class="getDaysColor(client.pivot.remaining_days)">
                {{ client.pivot.remaining_days }} gün kaldı
              </span>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="mb-4 relative z-10">
            <div class="flex justify-between text-xs text-slate-500 mb-1">
              <span>Paket İlerlemesi</span>
              <span>{{ Math.round((1 - client.pivot.remaining_days / client.pivot.total_days) * 100) }}%</span>
            </div>
            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full transition-all"
                :style="{ width: (1 - client.pivot.remaining_days / client.pivot.total_days) * 100 + '%' }">
              </div>
            </div>
            <p class="text-xs text-slate-600 mt-1">Toplam: {{ client.pivot.total_days }} gün</p>
          </div>

          <div class="grid grid-cols-2 gap-3 relative z-10">
            <button @click="openAssignModal(client)" class="py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-purple-600 transition-all">Program Ata</button>
            <NuxtLink :to="`/trainer/client-progress/${client.id}`" class="py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700 transition-all text-center">Gelişim Gör</NuxtLink>
          </div>
        </div>

        <div v-if="clients.length === 0 && !loading" class="col-span-full py-20 text-center">
          <p class="text-slate-500 italic">Henüz bir öğrenciniz bulunmuyor.</p>
          <button @click="openAddClientModal" class="mt-4 px-6 py-2 bg-purple-500 text-white rounded-xl">Öğrenci Ekle</button>
        </div>
      </div>

      <!-- Add Client Modal -->
      <div v-if="isAddClientModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="text-xl font-bold text-white">Öğrenci Ekle</h3>
            <button @click="isAddClientModalOpen = false" class="text-slate-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Search -->
          <div class="p-4 border-b border-slate-800">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Kullanıcı ara..."
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-purple-500 transition-all"
            >
          </div>

          <!-- Users List -->
          <div class="p-4 space-y-3">
            <div
              v-for="user in filteredUsers"
              :key="user.id"
              @click="selectUser(user)"
              :class="[
                'p-4 rounded-xl border cursor-pointer transition-all',
                selectedUser?.id === user.id
                  ? 'bg-purple-500/20 border-purple-500'
                  : 'bg-slate-800/50 border-slate-700 hover:border-slate-600'
              ]"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center mr-3 overflow-hidden">
                    <img v-if="user.avatar" :src="user.avatar" class="w-full h-full object-cover">
                    <svg v-else class="w-6 h-6 text-slate-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                  </div>
                  <div>
                    <p class="text-white font-medium">{{ user.name }}</p>
                    <p class="text-xs text-slate-500">{{ user.email }}</p>
                  </div>
                </div>
                <svg v-if="selectedUser?.id === user.id" class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              </div>
            </div>

            <div v-if="filteredUsers.length === 0" class="text-center py-8 text-slate-500">
              Kullanıcı bulunamadı.
            </div>
          </div>

          <!-- Package Selection -->
          <div v-if="selectedUser" class="p-4 border-t border-slate-800 bg-slate-900/50">
            <div class="mb-4">
              <p class="text-sm text-slate-400 mb-2">Seçilen Kullanıcı: <span class="text-white font-bold">{{ selectedUser.name }}</span></p>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-400 mb-2">Paket Süresi (Gün)</label>
              <div class="grid grid-cols-3 gap-2">
                <button
                  v-for="days in [30, 60, 90]"
                  :key="days"
                  @click="packageDays = days"
                  :class="[
                    'py-3 rounded-xl border transition-all font-bold',
                    packageDays === days
                      ? 'bg-purple-500/20 border-purple-500 text-purple-400'
                      : 'bg-slate-800 border-slate-700 text-slate-400 hover:border-slate-600'
                  ]"
                >
                  {{ days }} Gün
                </button>
              </div>
              <input v-model.number="packageDays" type="number" placeholder="Veya özel gün sayısı" class="w-full mt-3 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-purple-500 transition-all">
            </div>
            <button @click="addClient" :disabled="!packageDays" class="w-full py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl font-bold disabled:opacity-50">
              Öğrenciyi Ekle
            </button>
          </div>
        </div>
      </div>

      <!-- Assign Program Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
          <div class="p-6 border-b border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-bold text-white">Program Ata: {{ selectedClient?.name }}</h3>
            <button @click="isModalOpen = false" class="text-slate-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-6 space-y-4">
            <label class="block text-sm font-medium text-slate-400 mb-1">Mevcut Programlar</label>
            <div class="max-h-60 overflow-y-auto space-y-2 pr-2">
              <div v-for="program in programs" :key="program.id"
                @click="selectedProgramId = program.id"
                :class="selectedProgramId === program.id ? 'border-purple-500 bg-purple-500/10' : 'border-slate-800 bg-slate-800/50'"
                class="p-4 border rounded-xl cursor-pointer hover:border-purple-500/50 transition-all"
              >
                <div class="font-bold text-white">{{ program.title }}</div>
                <div class="text-xs text-slate-500">{{ program.level }} • {{ program.duration_weeks }} Hafta</div>
              </div>
            </div>

            <button @click="assignProgram" :disabled="!selectedProgramId" class="w-full py-3 mt-4 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl font-bold disabled:opacity-50">Atamayı Tamamla</button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

definePageMeta({
  middleware: 'trainer',
  layout: false
})

// Type definitions
interface User {
  id: number
  name: string
  email: string
  role: string
  trainer_id?: number
}

interface Client extends User {
  trainer_id: number
  program_id?: number
}

interface Program {
  id: number
  title: string
  level: string
  duration_weeks: number
}

const api = useApi()
const clients = ref<Client[]>([])
const allUsers = ref<User[]>([])
const programs = ref<Program[]>([])
const loading = ref(true)
const isModalOpen = ref(false)
const isAddClientModalOpen = ref(false)
const selectedClient = ref<Client | null>(null)
const selectedProgramId = ref<number | null>(null)
const selectedUser = ref<User | null>(null)
const searchQuery = ref('')
const packageDays = ref(30)

const fetchData = async () => {
  try {
    const [clientsRes, programsRes, usersRes] = await Promise.all([
      api.get('/trainer/clients'),
      api.get('/programs'),
      api.get('/users')
    ])
    clients.value = clientsRes.data.data
    programs.value = programsRes.data.data
    allUsers.value = usersRes.data.data.filter((u: User) => u.role === 'user')
  } catch (e: Error | unknown) {
    const errorMessage = e instanceof Error ? e.message : 'Veriler yüklenemedi'
    console.error(errorMessage)
  } finally {
    loading.value = false
  }
}

const filteredUsers = computed(() => {
  if (!searchQuery.value) return allUsers.value
  const query = searchQuery.value.toLowerCase()
  return allUsers.value.filter((u: User) =>
    u.name.toLowerCase().includes(query) ||
    u.email.toLowerCase().includes(query)
  )
})

const openAddClientModal = () => {
  selectedUser.value = null
  searchQuery.value = ''
  packageDays.value = 30
  isAddClientModalOpen.value = true
}

const selectUser = (user: User) => {
  selectedUser.value = user
}

const addClient = async () => {
  if (!selectedUser.value || !packageDays.value) return

  try {
    await api.post('/trainer/add-client', {
      email: selectedUser.value.email,
      total_days: packageDays.value
    })
    alert('Öğrenci başarıyla eklendi!')
    isAddClientModalOpen.value = false
    await fetchData()
  } catch (e: Error | unknown) {
    const errorResponse = e as any // Axios error has response property
    alert(errorResponse.response?.data?.message || 'Öğrenci eklenirken bir hata oluştu.')
  }
}

const openAssignModal = (client: Client) => {
  selectedClient.value = client
  selectedProgramId.value = null
  isModalOpen.value = true
}

const assignProgram = async () => {
  if (!selectedClient.value || !selectedProgramId.value) return

  try {
    await api.post('/trainer/assign-program', {
      client_id: selectedClient.value.id,
      program_id: selectedProgramId.value
    })
    alert('Program başarıyla atandı!')
    isModalOpen.value = false
  } catch (e) {
    alert('Program atanırken bir hata oluştu.')
  }
}

const getDaysColor = (days: number) => {
  if (days > 10) return 'bg-emerald-500/20 text-emerald-400'
  if (days > 5) return 'bg-orange-500/20 text-orange-400'
  return 'bg-red-500/20 text-red-400'
}

onMounted(fetchData)
</script>
