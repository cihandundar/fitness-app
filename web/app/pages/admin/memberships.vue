<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Üyelik Yönetimi</h1>
        <button @click="isModalOpen = true" class="flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-red-500/20 transition-all">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Yeni Plan
        </button>
      </header>

      <div class="p-6 lg:p-8">
        <!-- Tabs -->
        <div class="flex items-center space-x-2 mb-8">
          <button
            @click="activeTab = 'plans'"
            :class="[
              'px-6 py-3 rounded-xl font-medium transition-all',
              activeTab === 'plans'
                ? 'bg-gradient-to-r from-red-500 to-orange-600 text-white shadow-lg shadow-red-500/20'
                : 'bg-slate-800/50 text-slate-400 hover:bg-slate-800'
            ]"
          >
            <span class="mr-2">📋</span> Planlar
          </button>
          <button
            @click="activeTab = 'purchases'"
            :class="[
              'px-6 py-3 rounded-xl font-medium transition-all',
              activeTab === 'purchases'
                ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20'
                : 'bg-slate-800/50 text-slate-400 hover:bg-slate-800'
            ]"
          >
            <span class="mr-2">🛒</span> Satın Alanlar
            <span v-if="purchases.length > 0" class="ml-2 px-2 py-0.5 rounded-lg bg-white/20 text-xs">{{ purchases.length }}</span>
          </button>
        </div>

        <!-- Plans Tab -->
        <div v-if="activeTab === 'plans'">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div v-for="plan in plans" :key="plan.id" class="p-8 rounded-3xl bg-slate-900 border border-slate-800 hover:border-red-500/50 transition-all group relative">
               <div class="flex justify-between items-start mb-6">
                  <div :class="typeClass(plan.type)" class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest border">
                     {{ plan.type }}
                  </div>
                  <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                     <button @click="openEditModal(plan)" class="p-2 text-slate-400 hover:text-white transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                     <button @click="deletePlan(plan.id)" class="p-2 text-slate-400 hover:text-red-400 transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                  </div>
               </div>

               <h3 class="text-xl font-bold text-white mb-2">{{ plan.name }}</h3>
               <p class="text-sm text-slate-500 mb-6 line-clamp-2 h-10">{{ plan.description }}</p>

               <div class="flex items-baseline mb-6">
                  <span class="text-3xl font-black text-white">{{ formatPrice(plan.price) }}</span>
                  <span class="text-slate-500 ml-1 font-bold">TL</span>
               </div>

               <div class="pt-6 border-t border-slate-800 flex items-center text-xs font-bold text-slate-400 space-x-4">
                  <span v-if="plan.duration_days" class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ plan.duration_days }} GÜN</span>
                  <span v-if="plan.session_count" class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> {{ plan.session_count }} SEANS</span>
               </div>
            </div>
          </div>

          <div v-if="plans.length === 0" class="py-20 text-center text-slate-500 italic">Henüz plan oluşturulmamış.</div>
        </div>

        <!-- Purchases Tab -->
        <div v-if="activeTab === 'purchases'">
          <div v-if="loadingPurchases" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-emerald-500"></div>
          </div>

          <div v-else class="space-y-4">
            <div v-for="purchase in purchases" :key="purchase.id" class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-emerald-500/30 transition-all">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden">
                    <img v-if="purchase.user?.avatar" :src="purchase.user?.avatar" class="w-full h-full object-cover">
                    <svg v-else class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                  </div>
                  <div>
                    <h4 class="text-white font-bold">{{ purchase.user?.name }}</h4>
                    <p class="text-xs text-slate-500">{{ purchase.user?.email }}</p>
                  </div>
                </div>

                <div class="flex items-center space-x-6">
                  <div class="text-right">
                    <p class="text-xs text-slate-500">Plan</p>
                    <p class="text-white font-medium">{{ purchase.plan?.name }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-slate-500">Kalan Gün</p>
                    <p class="text-lg font-bold" :class="getDaysColor(purchase.remaining_days)">{{ purchase.remaining_days }} gün</p>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-slate-500">Durum</p>
                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="getStatusClass(purchase.status)">
                      {{ getStatusText(purchase.status) }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Progress Bar -->
              <div class="mt-4">
                <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full transition-all"
                    :style="{ width: ((purchase.total_days - purchase.remaining_days) / purchase.total_days * 100) + '%' }">
                  </div>
                </div>
                <div class="flex justify-between text-xs text-slate-600 mt-1">
                  <span>{{ formatDate(purchase.start_date) }}</span>
                  <span>{{ purchase.end_date ? formatDate(purchase.end_date) : '-' }}</span>
                </div>
              </div>
            </div>

            <div v-if="purchases.length === 0" class="py-20 text-center text-slate-500 italic">
              Henüz satın alan yok.
            </div>
          </div>
        </div>
      </div>

      <!-- Plan Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden p-8">
          <h3 class="text-xl font-bold text-white mb-6">{{ editingId ? 'Planı Düzenle' : 'Yeni Plan Ekle' }}</h3>
          <form @submit.prevent="savePlan" class="space-y-4">
            <div>
              <label class="block text-sm text-slate-400 mb-2">Plan Adı</label>
              <input v-model="form.name" type="text" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-slate-400 mb-2">Fiyat (TL)</label>
                <input v-model="form.price" type="number" step="0.01" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white">
              </div>
              <div>
                <label class="block text-sm text-slate-400 mb-2">Tür</label>
                <select v-model="form.type" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white">
                  <option value="gym">Salon</option>
                  <option value="pt">PT Seansı</option>
                  <option value="hybrid">Hibrit</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-slate-400 mb-2">Süre (Gün)</label>
                <input v-model="form.duration_days" type="number" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white">
              </div>
              <div>
                <label class="block text-sm text-slate-400 mb-2">Seans</label>
                <input v-model="form.session_count" type="number" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white">
              </div>
            </div>
            <div>
               <label class="flex items-center space-x-2 cursor-pointer">
                 <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-red-500">
                 <span class="text-sm text-slate-300">Aktif</span>
               </label>
            </div>
            <div class="pt-4 flex gap-4">
              <button type="button" @click="closeModal" class="flex-1 py-4 bg-slate-800 text-slate-400 rounded-2xl font-bold">İptal</button>
              <button type="submit" class="flex-1 py-4 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-2xl font-bold shadow-lg">Kaydet</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'

definePageMeta({
  middleware: 'admin',
  layout: false
})

interface MembershipPlan {
  id: number
  name: string
  price: number
  type: string
  features: string[]
  is_featured: boolean
  is_active: boolean
  duration_days: number
}

const api = useApi()
const plans = ref<MembershipPlan[]>([])
const purchases = ref([])
const activeTab = ref('plans')
const loadingPurchases = ref(false)
const isModalOpen = ref(false)
const editingId = ref<number | null>(null)

const form = ref({
  name: '',
  description: '',
  price: 0,
  type: 'gym',
  duration_days: 30,
  session_count: 0,
  is_active: true
})

const fetchPlans = async () => {
  try {
    const res = await api.get('/membership-plans')
    plans.value = res.data.data
  } catch (e) {
    console.error('Planlar yüklenemedi', e)
  }
}

const fetchPurchases = async () => {
  loadingPurchases.value = true
  try {
    const res = await api.get('/user-memberships')
    purchases.value = res.data.data
  } catch (e) {
    console.error('Satın alanlar yüklenemedi', e)
  } finally {
    loadingPurchases.value = false
  }
}

const typeClass = (type: string) => {
  switch (type) {
    case 'gym': return 'bg-blue-500/10 text-blue-400 border-blue-500/20'
    case 'pt': return 'bg-purple-500/10 text-purple-400 border-purple-500/20'
    case 'hybrid': return 'bg-orange-500/10 text-orange-400 border-orange-500/20'
    default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20'
  }
}

const getDaysColor = (days: number) => {
  if (days > 10) return 'text-emerald-400'
  if (days > 5) return 'text-orange-400'
  return 'text-red-400'
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'active': return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
    case 'expired': return 'bg-red-500/20 text-red-400 border-red-500/30'
    case 'cancelled': return 'bg-slate-500/20 text-slate-400 border-slate-500/30'
    default: return 'bg-slate-500/20 text-slate-400 border-slate-500/30'
  }
}

const getStatusText = (status: string) => {
  switch (status) {
    case 'active': return 'Aktif'
    case 'expired': return 'Sona Erdi'
    case 'cancelled': return 'İptal'
    default: return status
  }
}

const formatDate = (date: string) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', { day: 'numeric', month: 'short', year: 'numeric' })
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('tr-TR').format(price)
}

const openEditModal = (plan: MembershipPlan) => {
  editingId.value = plan.id
  form.value = { ...plan }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingId.value = null
  form.value = { name: '', description: '', price: 0, type: 'gym', duration_days: 30, session_count: 0, is_active: true }
}

const savePlan = async () => {
  try {
    if (editingId.value) {
      await api.put(`/membership-plans/${editingId.value}`, form.value)
    } else {
      await api.post('/membership-plans', form.value)
    }
    await fetchPlans()
    closeModal()
  } catch (e) {
    alert('Hata oluştu')
  }
}

const deletePlan = async (id: number) => {
  if (confirm('Bu planı silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/membership-plans/${id}`)
      await fetchPlans()
    } catch (e) {
      alert('Silinemedi')
    }
  }
}

watch(activeTab, (newVal) => {
  if (newVal === 'purchases') {
    fetchPurchases()
  }
})

onMounted(fetchPlans)
</script>
