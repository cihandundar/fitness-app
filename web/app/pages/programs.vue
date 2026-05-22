<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <!-- Sidebar -->
    <AppSidebar />

    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 lg:pl-72">
      <!-- Dashboard Header Style Header -->
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between">
        <h1 class="text-xl font-semibold text-white">Programlar</h1>
        <div class="flex items-center space-x-4">
           <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
             <span class="text-white font-medium text-sm">SA</span>
           </div>
        </div>
      </header>
      
      <div class="p-6 lg:p-8">
        <header class="mb-8">
          <h2 class="text-3xl font-bold text-white mb-2">Hedefine Uygun Programı Seç</h2>
          <p class="text-slate-400">Uzmanlar tarafından hazırlanmış hazır programlar veya sana özel atananlar.</p>
        </header>

        <!-- Filters -->
        <div class="flex flex-wrap gap-4 mb-8">
          <button v-for="cat in categories" :key="cat" 
            @click="activeCategory = cat"
            :class="activeCategory === cat ? 'bg-violet-500 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800'"
            class="px-5 py-2 rounded-xl text-sm font-medium transition-all"
          >
            {{ cat }}
          </button>
        </div>

        <!-- Programs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <div v-for="program in filteredPrograms" :key="program.id" 
            @click="navigateTo(`/programs/${program.id}`)"
            class="group relative bg-slate-900/50 border border-slate-800/50 rounded-3xl overflow-hidden hover:border-violet-500/50 transition-all cursor-pointer">
            <div class="aspect-video bg-slate-800 relative overflow-hidden">
              <img v-if="program.image" :src="program.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
              <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900">
                <svg class="w-12 h-12 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              </div>
              <div class="absolute top-4 left-4">
                <span class="px-3 py-1 rounded-full bg-black/50 backdrop-blur-md text-white text-xs font-bold uppercase tracking-wider">{{ program.level }}</span>
              </div>
            </div>
            <div class="p-6">
              <h3 class="text-xl font-bold text-white mb-2">{{ program.title }}</h3>
              <p class="text-slate-400 text-sm mb-6 line-clamp-2">{{ program.description }}</p>
              
              <div class="flex items-center justify-between mt-auto">
                <div class="flex items-center text-slate-500 text-xs space-x-4">
                  <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ program.duration_weeks }} Hafta</span>
                  <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>{{ program.exercises_count || 0 }} Egzersiz</span>
                </div>
                <button class="p-2 rounded-xl bg-violet-500/10 text-violet-400 group-hover:bg-violet-500 group-hover:text-white transition-all">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
              </div>
            </div>
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

const api = useApi()
const programs = ref([])
const activeCategory = ref('Tümü')
const categories = ['Tümü', 'Kilo Verme', 'Kas Kütlesi', 'Dayanıklılık', 'Esneklik']

const fetchPrograms = async () => {
  try {
    const res = await api.get('/programs')
    programs.value = res.data.data
  } catch (e) {
    console.error('Programlar yüklenemedi')
  }
}

const filteredPrograms = computed(() => {
  if (activeCategory.value === 'Tümü') return programs.value
  return programs.value.filter(p => p.category === activeCategory.value)
})

onMounted(fetchPrograms)
</script>
