<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <AppSidebar />
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 lg:pl-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between">
        <h1 class="text-xl font-semibold text-white">Egzersizler</h1>
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
             <span class="text-white font-medium text-sm">SA</span>
        </div>
      </header>
      
      <div class="p-6 lg:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
          <div>
            <h2 class="text-3xl font-bold text-white mb-2">Hareketleri Keşfet</h2>
            <p class="text-slate-400">Doğru form ve teknik ile egzersizleri öğrenin.</p>
          </div>
          <div class="relative max-w-md w-full">
            <input v-model="searchQuery" type="text" placeholder="Egzersiz ara..." class="w-full bg-slate-900 border border-slate-800 rounded-2xl px-12 py-3 text-white outline-none focus:border-violet-500 transition-all">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          <div v-for="ex in filteredExercises" :key="ex.id" class="p-4 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:bg-slate-800/50 transition-all cursor-pointer group text-center">
            <div class="aspect-square rounded-xl bg-slate-800 mb-4 flex items-center justify-center overflow-hidden">
               <img v-if="ex.image" :src="`/storage/${ex.image}`" class="w-full h-full object-cover group-hover:scale-110 transition-all">
               <svg v-else class="w-12 h-12 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h4 class="text-white font-medium mb-1 truncate">{{ ex.name }}</h4>
            <p class="text-xs text-slate-500 capitalize">{{ ex.muscle_group || 'Belirsiz' }}</p>
          </div>
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

interface Exercise {
  id: number
  name: string
  description?: string
  muscle_group?: string
  muscle_group_id?: number
  equipment?: string
  equipment_type_id?: number
  difficulty?: string
  image?: string
  is_active?: boolean
}

const api = useApi()
const exercises = ref([])
const searchQuery = ref('')

const fetchExercises = async () => {
  try {
    const res = await api.get('/exercises')
    exercises.value = res.data.data
  } catch (e) {
    console.error('Egzersizler yüklenemedi')
  }
}

const filteredExercises = computed(() => {
  if (!searchQuery.value) return exercises.value
  return exercises.value.filter(e => {
    const name = e.name?.toLowerCase() || ''
    const muscleGroup = e.muscle_group?.toLowerCase() || ''
    return name.includes(searchQuery.value.toLowerCase()) || muscleGroup.includes(searchQuery.value.toLowerCase())
  })
})

onMounted(fetchExercises)
</script>
