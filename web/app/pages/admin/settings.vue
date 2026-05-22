<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Sistem Ayarları</h1>
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
            <span class="text-white font-medium text-sm">AD</span>
          </div>
        </div>
      </header>

      <div class="p-6 lg:p-8">
        <!-- Tabs -->
        <div class="flex items-center space-x-2 mb-8 overflow-x-auto pb-2">
          <button
            @click="activeTab = 'equipment'"
            :class="[
              'px-6 py-3 rounded-xl font-medium transition-all whitespace-nowrap',
              activeTab === 'equipment'
                ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20'
                : 'bg-slate-800/50 text-slate-400 hover:bg-slate-800'
            ]"
          >
            <span class="mr-2">🏋️</span> Ekipman Türleri
          </button>
          <button
            @click="activeTab = 'muscles'"
            :class="[
              'px-6 py-3 rounded-xl font-medium transition-all whitespace-nowrap',
              activeTab === 'muscles'
                ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white shadow-lg shadow-violet-500/20'
                : 'bg-slate-800/50 text-slate-400 hover:bg-slate-800'
            ]"
          >
            <span class="mr-2">💪</span> Kas Grupları
          </button>
        </div>

        <!-- Equipment Types Tab -->
        <div v-if="activeTab === 'equipment'" class="space-y-6">
          <!-- Add Equipment Form -->
          <div class="p-6 rounded-3xl bg-slate-900/50 border border-slate-800/50">
            <h3 class="text-lg font-semibold text-white mb-4">Yeni Ekipman Ekle</h3>
            <form @submit.prevent="addEquipment" class="flex flex-col sm:flex-row gap-4">
              <input
                v-model="equipmentForm.name"
                type="text"
                placeholder="Ekipman adı (örn: Dumbbell)"
                class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all"
                required
              >
              <input
                v-model="equipmentForm.icon"
                type="text"
                placeholder="Emoji (örn: 🏋️)"
                class="w-32 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all"
                maxlength="4"
              >
              <input
                v-model.number="equipmentForm.sort_order"
                type="number"
                placeholder="Sıra"
                class="w-24 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all"
              >
              <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-emerald-500/20 transition-all">
                Ekle
              </button>
            </form>
          </div>

          <!-- Equipment List -->
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div
              v-for="eq in equipmentTypes"
              :key="eq.id"
              class="p-4 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-emerald-500/30 transition-all group"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <span class="text-2xl">{{ eq.icon || '🏋️' }}</span>
                  <div>
                    <h4 class="text-white font-medium">{{ eq.name }}</h4>
                    <p class="text-xs text-slate-500">Sıra: {{ eq.sort_order }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button
                    @click="toggleEquipmentStatus(eq)"
                    :class="[
                      'w-3 h-3 rounded-full transition-all',
                      eq.is_active ? 'bg-emerald-400' : 'bg-slate-600'
                    ]"
                  />
                  <button
                    @click="deleteEquipment(eq.id)"
                    class="p-2 text-slate-400 hover:text-red-400 transition-all opacity-0 group-hover:opacity-100"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Muscle Groups Tab -->
        <div v-if="activeTab === 'muscles'" class="space-y-6">
          <!-- Add Muscle Group Form -->
          <div class="p-6 rounded-3xl bg-slate-900/50 border border-slate-800/50">
            <h3 class="text-lg font-semibold text-white mb-4">Yeni Kas Grubu Ekle</h3>
            <form @submit.prevent="addMuscleGroup" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
              <input
                v-model="muscleForm.name"
                type="text"
                placeholder="Türkçe ad (örn: Göğüs)"
                class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500 transition-all"
                required
              >
              <input
                v-model="muscleForm.name_en"
                type="text"
                placeholder="İngilizce ad (örn: Chest)"
                class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500 transition-all"
              >
              <input
                v-model="muscleForm.icon"
                type="text"
                placeholder="Emoji (örn: 🦍)"
                class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500 transition-all"
                maxlength="4"
              >
              <select v-model="muscleForm.color" class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500 transition-all">
                <option value="violet">Violet</option>
                <option value="emerald">Emerald</option>
                <option value="red">Red</option>
                <option value="blue">Blue</option>
                <option value="orange">Orange</option>
                <option value="pink">Pink</option>
              </select>
              <button type="submit" class="px-6 py-3 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-violet-500/20 transition-all">
                Ekle
              </button>
            </form>
          </div>

          <!-- Muscle Groups List -->
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div
              v-for="mg in muscleGroups"
              :key="mg.id"
              class="p-4 rounded-2xl bg-slate-900/50 border transition-all group"
              :class="[
                mg.is_active ? 'border-slate-800/50 hover:border-violet-500/30' : 'border-slate-800/30 opacity-60'
              ]"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <span class="text-2xl">{{ mg.icon || '💪' }}</span>
                  <div>
                    <h4 class="text-white font-medium">{{ mg.name }}</h4>
                    <p class="text-xs text-slate-500">{{ mg.name_en }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="text-xs px-2 py-1 rounded-lg bg-slate-800 text-slate-400">{{ mg.color }}</span>
                  <button
                    @click="toggleMuscleStatus(mg)"
                    :class="[
                      'w-3 h-3 rounded-full transition-all',
                      mg.is_active ? 'bg-violet-400' : 'bg-slate-600'
                    ]"
                  />
                  <button
                    @click="deleteMuscleGroup(mg.id)"
                    class="p-2 text-slate-400 hover:text-red-400 transition-all opacity-0 group-hover:opacity-100"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

definePageMeta({
  middleware: 'admin',
  layout: false
})

interface EquipmentType {
  id: number
  name: string
  is_active: boolean
  sort_order: number
}

interface MuscleGroup {
  id: number
  name: string
  is_active: boolean
}

const api = useApi()
const activeTab = ref('equipment')
const loading = ref(false)

const equipmentTypes = ref<EquipmentType[]>([])
const muscleGroups = ref<MuscleGroup[]>([])

const equipmentForm = ref({
  name: '',
  icon: '🏋️',
  sort_order: 0
})

const muscleForm = ref({
  name: '',
  name_en: '',
  icon: '💪',
  color: 'violet'
})

const fetchEquipmentTypes = async () => {
  try {
    const res = await api.get('/equipment-types')
    equipmentTypes.value = res.data.data
  } catch (e) {
    console.error('Ekipman türleri yüklenemedi')
  }
}

const fetchMuscleGroups = async () => {
  try {
    const res = await api.get('/muscle-groups')
    muscleGroups.value = res.data.data
  } catch (e) {
    console.error('Kas grupları yüklenemedi')
  }
}

const addEquipment = async () => {
  try {
    await api.post('/equipment-types', equipmentForm.value)
    equipmentForm.value = { name: '', icon: '🏋️', sort_order: 0 }
    await fetchEquipmentTypes()
  } catch (e) {
    alert('Ekleme başarısız')
  }
}

const toggleEquipmentStatus = async (eq: EquipmentType) => {
  try {
    await api.put(`/equipment-types/${eq.id}`, { is_active: !eq.is_active })
    await fetchEquipmentTypes()
  } catch (e: Error | unknown) {
    const errorMessage = e instanceof Error ? e.message : 'Güncelleme başarısız'
    alert(errorMessage)
  }
}

const deleteEquipment = async (id: number) => {
  if (confirm('Bu ekipman türünü silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/equipment-types/${id}`)
      await fetchEquipmentTypes()
    } catch (e) {
      alert('Silme başarısız')
    }
  }
}

const addMuscleGroup = async () => {
  try {
    await api.post('/muscle-groups', muscleForm.value)
    muscleForm.value = { name: '', name_en: '', icon: '💪', color: 'violet' }
    await fetchMuscleGroups()
  } catch (e) {
    alert('Ekleme başarısız')
  }
}

const toggleMuscleStatus = async (mg: MuscleGroup) => {
  try {
    await api.put(`/muscle-groups/${mg.id}`, { is_active: !mg.is_active })
    await fetchMuscleGroups()
  } catch (e: Error | unknown) {
    const errorMessage = e instanceof Error ? e.message : 'Güncelleme başarısız'
    alert(errorMessage)
  }
}

const deleteMuscleGroup = async (id: number) => {
  if (confirm('Bu kas grubunu silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/muscle-groups/${id}`)
      await fetchMuscleGroups()
    } catch (e) {
      alert('Silme başarısız')
    }
  }
}

onMounted(() => {
  fetchEquipmentTypes()
  fetchMuscleGroups()
})
</script>
