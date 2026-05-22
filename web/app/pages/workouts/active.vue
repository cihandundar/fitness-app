<template>
  <div class="min-h-screen bg-slate-950 flex flex-col">
    <!-- Header -->
    <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 justify-between">
      <div class="flex items-center space-x-4">
         <button @click="confirmExit" class="text-slate-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
         <h1 class="text-lg font-bold text-white">Antrenman Takibi</h1>
      </div>
      <div class="flex items-center space-x-4">
         <div class="text-right">
            <p class="text-[10px] text-slate-500 font-bold uppercase">Süre</p>
            <p class="text-sm font-mono text-violet-400">{{ formatTime(timer) }}</p>
         </div>
         <button @click="finishWorkout" class="px-4 py-2 bg-green-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-green-500/20">BİTİR</button>
      </div>
    </header>

    <!-- Content -->
    <main class="flex-1 overflow-y-auto p-4 md:p-8 max-w-3xl mx-auto w-full space-y-6">
       <!-- Active Exercise -->
       <div v-if="currentExercise" class="space-y-6">
          <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative overflow-hidden">
             <div class="absolute top-0 right-0 p-4">
                <span class="px-3 py-1 rounded-full bg-violet-500/10 text-violet-400 text-[10px] font-bold uppercase">{{ currentExercise.muscle_group }}</span>
             </div>
             <h2 class="text-2xl font-black text-white mb-1">{{ currentExercise.name }}</h2>
             <p class="text-slate-500 text-sm mb-6">{{ currentExercise.equipment }}</p>

             <!-- Sets Table -->
             <div class="space-y-3">
                <div v-for="(set, index) in currentSets" :key="index" class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4 duration-300">
                   <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-400 border border-slate-700">{{ index + 1 }}</div>
                   <div class="flex-1">
                      <input v-model="set.weight" type="number" placeholder="Kg" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-center focus:border-violet-500 outline-none transition-all">
                   </div>
                   <div class="flex-1">
                      <input v-model="set.reps" type="number" placeholder="Tekrar" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-center focus:border-violet-500 outline-none transition-all">
                   </div>
                   <button @click="saveSet(index)" :class="set.saved ? 'bg-green-500' : 'bg-slate-700'" class="w-10 h-10 rounded-xl flex items-center justify-center text-white transition-all">
                      <svg v-if="set.saved" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                   </button>
                </div>
             </div>

             <button @click="addSet" class="w-full mt-6 py-3 border border-dashed border-slate-700 rounded-2xl text-slate-500 font-bold hover:text-white hover:border-slate-500 transition-all">+ SET EKLE</button>
          </div>

          <!-- History Comparison -->
          <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
             <h3 class="text-sm font-bold text-slate-400 uppercase mb-4">Geçmiş Performans</h3>
             <div v-if="history.length > 0" class="space-y-3">
                <div v-for="h in history.slice(0, 3)" :key="h.id" class="flex justify-between items-center text-sm">
                   <span class="text-slate-500">{{ formatDate(h.created_at) }}</span>
                   <span class="text-white font-bold">{{ h.weight }}kg x {{ h.reps }}</span>
                </div>
             </div>
             <p v-else class="text-slate-600 text-xs italic">Bu egzersiz için henüz geçmiş kayıt yok.</p>
          </div>
       </div>

       <!-- Exercise Selector -->
       <div class="space-y-4">
          <h3 class="text-lg font-bold text-white">Sıradaki Egzersizler</h3>
          <div v-for="ex in remainingExercises" :key="ex.id" @click="selectExercise(ex)" class="p-4 bg-slate-900 border border-slate-800 rounded-2xl flex items-center justify-between cursor-pointer hover:border-violet-500/50 transition-all">
             <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center mr-4"><svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
                <div>
                   <p class="text-white font-bold text-sm">{{ ex.name }}</p>
                   <p class="text-[10px] text-slate-500 uppercase font-bold">{{ ex.muscle_group }}</p>
                </div>
             </div>
             <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </div>
       </div>
    </main>

    <!-- Rest Timer Overlay -->
    <div v-if="restTimer > 0" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 backdrop-blur-md">
       <div class="text-center">
          <p class="text-slate-400 font-bold uppercase tracking-widest mb-4">Dinlenme</p>
          <div class="text-8xl font-black text-white mb-8">{{ restTimer }}s</div>
          <button @click="restTimer = 0" class="px-8 py-4 bg-slate-800 text-white rounded-2xl font-bold">ATLA</button>
       </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

definePageMeta({
  middleware: 'auth',
  layout: false
})

// Type definitions
interface Exercise {
  id: number
  name: string
  muscle_group: string
  equipment: string
  difficulty: string
}

interface WorkoutSet {
  weight: number | null
  reps: number | null
  saved: boolean
}

interface HistoryRecord {
  weight: number
  reps: number
  date: string
}

const api = useApi()
const route = useRoute()

const sessionId = ref<number | null>(null)
const timer = ref(0)
const restTimer = ref(0)
const currentExercise = ref<Exercise | null>(null)
const currentSets = ref<WorkoutSet[]>([])
const remainingExercises = ref<Exercise[]>([])
const history = ref<HistoryRecord[]>([])

let timerInterval = null
let restInterval = null

const startSession = async () => {
  try {
    const res = await api.post('/workout-tracking/start', { 
       workout_id: route.query.workout_id || null 
    })
    sessionId.value = res.data.data.id
    
    // Eğer bir workout_id varsa egzersizleri getir
    if (route.query.workout_id) {
       const wRes = await api.get(`/workouts/${route.query.workout_id}`)
       remainingExercises.value = wRes.data.data.exercises
       if (remainingExercises.value.length > 0) {
          selectExercise(remainingExercises.value[0])
       }
    } else {
       // Boş antrenman ise tüm egzersizleri seçtirebiliriz
       const eRes = await api.get('/exercises')
       remainingExercises.value = eRes.data.data
    }

    timerInterval = setInterval(() => { timer.value++ }, 1000)
  } catch (e) {
    console.error('Antrenman başlatılamadı')
  }
}

const selectExercise = async (ex: Exercise) => {
  currentExercise.value = ex
  currentSets.value = [{ weight: null, reps: null, saved: false }]

  // Egzersiz geçmişini getir
  try {
     const hRes = await api.get(`/workout-tracking/exercise-history/${ex.id}`)
     history.value = hRes.data.data
  } catch (e: Error | unknown) {
    const errorMessage = e instanceof Error ? e.message : 'Egzersiz geçmişi yüklenemedi'
    console.error(errorMessage)
  }

  // Listeden çıkar
  remainingExercises.value = remainingExercises.value.filter(item => item.id !== ex.id)
}

const addSet = () => {
  currentSets.value.push({ weight: currentSets.value[currentSets.value.length-1]?.weight || null, reps: null, saved: false })
}

const saveSet = async (index: number) => {
  const set = currentSets.value[index]
  if (!set.weight || !set.reps) return

  try {
    await api.post('/workout-tracking/log-set', {
       completed_workout_id: sessionId.value,
       exercise_id: currentExercise.value.id,
       set_number: index + 1,
       weight: set.weight,
       reps: set.reps
    })
    set.saved = true
    
    // Dinlenme sayacını başlat (60 sn standart)
    restTimer.value = 60
    restInterval = setInterval(() => {
       if (restTimer.value > 0) restTimer.value--
       else clearInterval(restInterval)
    }, 1000)
  } catch (e) {
    alert('Set kaydedilemedi')
  }
}

const finishWorkout = async () => {
  if (confirm('Antrenmanı bitirmek istiyor musunuz?')) {
    try {
      await api.post(`/workout-tracking/finish/${sessionId.value}`, {
         notes: 'Uygulama üzerinden tamamlandı.'
      })
      clearInterval(timerInterval)
      navigateTo('/dashboard')
    } catch (e) {
      alert('Hata oluştu')
    }
  }
}

const formatTime = (s: number) => {
  const h = Math.floor(s / 3600)
  const m = Math.floor((s % 3600) / 60)
  const sec = s % 60
  return [h, m, sec].map(v => v < 10 ? "0" + v : v).filter((v,i) => v !== "00" || i > 0).join(":")
}

const formatDate = (d: string) => new Date(d).toLocaleDateString('tr-TR')

const confirmExit = () => {
  if (confirm('Antrenman iptal edilecek, emin misiniz?')) {
     navigateTo('/dashboard')
  }
}

onMounted(startSession)
onUnmounted(() => {
   clearInterval(timerInterval)
   clearInterval(restInterval)
})
</script>
