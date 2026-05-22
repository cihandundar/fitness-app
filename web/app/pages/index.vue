<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
      <!-- Background Gradient -->
      <div class="absolute inset-0 bg-gradient-to-b from-violet-500/10 to-transparent"></div>
      <div class="absolute inset-0">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-violet-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center px-4 py-2 rounded-full bg-slate-800/50 border border-slate-700/50 text-slate-300 text-sm mb-8">
          <span class="w-2 h-2 rounded-full bg-green-400 mr-2 animate-pulse"></span>
          1000+ Aktif Kullanıcı
        </div>

        <!-- Heading -->
        <h1 class="text-5xl md:text-7xl font-bold mb-6">
          <span class="bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">
            Fitness Hedeflerine
          </span>
          <br />
          <span class="bg-gradient-to-r from-violet-400 to-purple-400 bg-clip-text text-transparent">
            Ulaş
          </span>
        </h1>

        <!-- Subtitle -->
        <p class="text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
          Kişiselleştirilmiş antrenman programları, detaylı egzersiz rehberi ve ilerleme takibi ile fitness yolculuğunu bir üst seviyeye taşı.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <NuxtLink
            to="/register"
            class="px-8 py-4 rounded-2xl bg-gradient-to-r from-violet-500 to-purple-600 text-white font-semibold shadow-lg shadow-violet-500/20 hover:shadow-violet-500/40 transition-all flex items-center"
          >
            Ücretsiz Başla
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </NuxtLink>
          <NuxtLink
            to="/programs"
            class="px-8 py-4 rounded-2xl bg-slate-800/50 border border-slate-700/50 text-white font-semibold hover:bg-slate-800 transition-all"
          >
            Programları İncele
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Sports Disciplines Section -->
    <section v-if="branches.length > 0" class="py-20 bg-slate-900/30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Branşlarımız</h2>
          <p class="text-slate-400 max-w-2xl mx-auto text-lg">Uzman eğitmenler eşliğinde hedeflerine ulaşabileceğin spor dalları.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
          <div v-for="branch in branches" :key="branch.id" class="group relative aspect-square rounded-3xl bg-slate-900/50 border border-slate-800/50 hover:border-violet-500/50 transition-all overflow-hidden shadow-2xl">
            <!-- Background Image -->
            <div class="absolute inset-0 z-0">
              <img v-if="branch.image" :src="`http://localhost:8000/${branch.image}`" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
              <div v-else class="w-full h-full bg-slate-800 flex items-center justify-center text-slate-700">
                <svg v-if="branch.name.toLowerCase().includes('fitness')" class="w-20 h-20 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                </svg>
                <svg v-else-if="branch.name.toLowerCase().includes('crossfit')" class="w-20 h-20 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <svg v-else class="w-20 h-20 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
              </div>
              <!-- Overlay Gradient -->
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent opacity-80 group-hover:opacity-60 transition-opacity"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 h-full flex flex-col justify-end p-6 md:p-8">
              <h3 class="text-xl md:text-2xl font-bold text-white group-hover:text-violet-300 transition-colors">{{ branch.name }}</h3>
              <div class="w-0 group-hover:w-12 h-1 bg-violet-500 transition-all duration-300 mt-2"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Membership Plans Section -->
    <section v-if="plans.length > 0" class="py-24 relative overflow-hidden">
      <div class="absolute inset-0 bg-violet-500/5 blur-3xl rounded-full -translate-y-1/2"></div>
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Üyelik Paketleri</h2>
          <p class="text-slate-400 max-w-2xl mx-auto text-lg">Sana en uygun paketi seç, hedeflerine hemen ulaşmaya başla.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="plan in plans" :key="plan.id" 
               :class="plan.is_featured ? 'bg-gradient-to-b from-violet-600/20 to-purple-600/10 border-2 border-violet-500 shadow-2xl shadow-violet-500/10' : 'bg-slate-900/50 border border-slate-800/50 hover:border-slate-700'"
               class="p-8 rounded-3xl transition-all flex flex-col relative group">
            <div v-if="plan.is_featured" class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-violet-500 text-white text-sm font-bold">En Popüler</div>
            
            <h3 class="text-xl font-bold text-white mb-2">{{ plan.name }}</h3>
            <div class="mb-6">
              <span class="text-4xl font-bold text-white">{{ Math.floor(plan.price) }}₺</span>
              <span class="text-slate-500 ml-2">/aylık</span>
            </div>
            
            <ul class="space-y-4 mb-8 flex-1">
              <li v-for="(feature, idx) in plan.features" :key="idx" class="flex items-center" :class="plan.is_featured ? 'text-slate-100' : 'text-slate-400'">
                <svg class="w-5 h-5 text-violet-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ feature }}
              </li>
            </ul>
            
            <NuxtLink to="/register" 
                     :class="plan.is_featured ? 'bg-gradient-to-r from-violet-500 to-purple-600 shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40' : 'bg-slate-800 hover:bg-slate-700'"
                     class="w-full py-4 rounded-2xl text-white font-semibold transition-all text-center">
              Satın Al
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

definePageMeta({
})

const authStore = useAuthStore()
const api = useApi()

const branches = ref([])
const plans = ref([])

const fetchData = async () => {
  try {
    const [branchesRes, plansRes] = await Promise.all([
      api.get('/branches'),
      api.get('/membership-plans')
    ])
    branches.value = branchesRes.data.data
    plans.value = plansRes.data.data
  } catch (e) {
    console.error('Data fetch error:', e)
  }
}

onMounted(() => {
  fetchData()
})
</script>
