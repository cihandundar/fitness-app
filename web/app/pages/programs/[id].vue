<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <AppSidebar />

    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 lg:pl-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <div class="flex items-center">
           <button @click="navigateTo('/programs')" class="mr-4 text-slate-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></button>
           <h1 class="text-xl font-semibold text-white">Program Detayı</h1>
        </div>
      </header>

      <div v-if="loading" class="flex justify-center py-20">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-violet-500"></div>
      </div>

      <div v-else-if="program" class="p-6 lg:p-8 max-w-5xl mx-auto w-full">
         <div class="relative h-64 rounded-3xl overflow-hidden mb-8 group">
            <img v-if="program.image" :src="program.image" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-8">
               <span class="px-3 py-1 rounded-full bg-violet-500 text-white text-[10px] font-bold uppercase tracking-widest mb-3 inline-block">{{ program.level }}</span>
               <h2 class="text-4xl font-black text-white uppercase">{{ program.title }}</h2>
            </div>
         </div>

         <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
               <section>
                  <h3 class="text-lg font-bold text-white mb-4">Program Hakkında</h3>
                  <p class="text-slate-400 leading-relaxed">{{ program.description }}</p>
               </section>

               <section>
                  <h3 class="text-lg font-bold text-white mb-4">Antrenman Planı</h3>
                  <div class="space-y-4">
                     <div v-for="workout in program.workouts" :key="workout.id" class="p-6 rounded-2xl bg-slate-900 border border-slate-800 hover:border-violet-500/50 transition-all group">
                        <div class="flex items-center justify-between">
                           <div>
                              <p class="text-[10px] font-bold text-violet-400 uppercase mb-1">Gün {{ workout.day_number }}</p>
                              <h4 class="text-xl font-bold text-white">{{ workout.title }}</h4>
                              <p class="text-sm text-slate-500">{{ workout.duration_minutes }} Dakika • {{ workout.exercises?.length || 0 }} Egzersiz</p>
                           </div>
                           <button @click="startWorkout(workout.id)" class="px-6 py-3 bg-violet-500 text-white rounded-xl font-bold shadow-lg shadow-violet-500/20 hover:scale-105 transition-all">BAŞLAT</button>
                        </div>
                        
                        <!-- Mini Exercise List -->
                        <div class="mt-6 pt-6 border-t border-slate-800 grid grid-cols-2 gap-2">
                           <div v-for="ex in workout.exercises" :key="ex.id" class="text-xs text-slate-500 flex items-center">
                              <span class="w-1.5 h-1.5 rounded-full bg-slate-700 mr-2"></span>
                              {{ ex.name }}
                           </div>
                        </div>
                     </div>
                  </div>
               </section>
            </div>

            <div class="space-y-6">
               <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800">
                  <h3 class="text-sm font-bold text-slate-400 uppercase mb-4">İstatistikler</h3>
                  <div class="space-y-4">
                     <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">Toplam Süre</span>
                        <span class="text-white font-bold">{{ program.duration_weeks }} Hafta</span>
                     </div>
                     <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">Antrenmanlar</span>
                        <span class="text-white font-bold">{{ program.workouts?.length || 0 }} Gün</span>
                     </div>
                     <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">Zorluk</span>
                        <span class="text-white font-bold capitalize">{{ program.level }}</span>
                     </div>
                  </div>
               </div>
               
               <div class="p-6 rounded-3xl bg-gradient-to-br from-violet-500/10 to-purple-600/10 border border-violet-500/20">
                  <h3 class="text-white font-bold mb-2">Eğitmen Notu</h3>
                  <p class="text-sm text-slate-400 italic">"Bu program hedeflerine ulaşman için özel olarak seçildi. Hareketleri doğru formda yapmaya özen göster."</p>
               </div>
            </div>
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
const route = useRoute()
const program = ref(null)
const loading = ref(true)

const fetchProgram = async () => {
  try {
    const res = await api.get(`/programs/${route.params.id}`)
    program.value = res.data.data
  } catch (e) {
     console.error('Program yüklenemedi')
  } finally {
     loading.value = false
  }
}

const startWorkout = (workoutId: number) => {
   navigateTo({
      path: '/workouts/active',
      query: { workout_id: workoutId }
   })
}

onMounted(fetchProgram)
</script>
