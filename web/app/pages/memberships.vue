<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '~/composables/useApi'
import { useToast } from '~/composables/useToast'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  middleware: 'auth',
  layout: false,
  title: 'Üyelik Paketleri - FitApp'
})

const router = useRouter()
const authStore = useAuthStore()
const api = useApi()
const toast = useToast()

const loading = ref(true)
const loadingBranches = ref(true)
const plans = ref<any[]>([])
const branches = ref<any[]>([])
const currentMembership = ref<any>(null)
const checkingMembership = ref(true)

const showBranchSelection = ref(true)
const selectedBranch = ref<number | null>(null)

const activeBranches = computed(() => branches.value.filter(b => b.is_active))

onMounted(async () => {
  await Promise.all([fetchPlans(), fetchBranches(), checkCurrentMembership()])

  // Eğer kullanıcı zaten branş seçmişse direkt planları göster
  if (authStore.user?.preferred_branches && authStore.user.preferred_branches.length > 0) {
    selectedBranch.value = authStore.user.preferred_branches[0]
    showBranchSelection.value = false
  }
})

async function checkCurrentMembership() {
  // Admin kullanıcılar için kontrol yapma
  if (authStore.isAdmin) {
    checkingMembership.value = false
    return
  }

  try {
    const response = await api.get('/my-membership')
    currentMembership.value = response.data?.data || response.data

    // Aktif veya pending üyelik varsa uyarı göster
    if (currentMembership.value) {
      if (currentMembership.value.status === 'pending') {
        toast.warning('Onay bekleyen bir üyeliğiniz bulunuyor.')
      } else if (currentMembership.value.status === 'active') {
        toast.info('Zaten aktif bir üyeliğiniz bulunuyor.')
      }
    }
  } catch (e) {
    // Üyelik yoksa sessizce geç
  } finally {
    checkingMembership.value = false
  }
}

async function fetchPlans() {
  loading.value = true
  try {
    const response = await api.get('/membership-plans')
    plans.value = response.data.data.filter((p: any) => p.is_active)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Paketler yüklenemedi.')
  } finally {
    loading.value = false
  }
}

async function fetchBranches() {
  loadingBranches.value = true
  try {
    const response = await api.get('/branches')
    branches.value = response.data.data
  } catch (error: any) {
    toast.error('Branşlar yüklenemedi.')
  } finally {
    loadingBranches.value = false
  }
}

function selectBranch(branchId: number) {
  selectedBranch.value = branchId
}

async function saveBranchesAndContinue() {
  if (selectedBranch.value === null) {
    toast.error('Lütfen bir branş seçin.')
    return
  }

  try {
    await api.put('/profile', {
      preferred_branches: [selectedBranch.value]
    })
    // Update auth store
    authStore.user!.preferred_branches = [selectedBranch.value]
    showBranchSelection.value = false
    toast.success('Tercihleriniz kaydedildi!')
  } catch (error: any) {
    toast.error('Bir hata oluştu.')
  }
}

async function selectPlan(planId: string) {
  // Aktif veya pending üyelik varsa yeni üyeliğe izin verme
  if (currentMembership.value && ['active', 'pending'].includes(currentMembership.value.status)) {
    const message = currentMembership.value.status === 'pending'
      ? 'Onay bekleyen bir üyeliğiniz bulunuyor. Yeni üyelik alabilmeniz için mevcut başvurunun sonuçlanmasını bekleyin.'
      : 'Zaten aktif bir üyeliğiniz bulunuyor. Yeni üyelik alabilmeniz için mevcut üyeliğinizin sona ermesini bekleyin.'
    toast.error(message)
    return
  }

  try {
    await api.post('/user-memberships', {
      membership_plan_id: planId
    })

    toast.success('🎉 Üyelik başvurunuz alındı! Şubemize gelerek ödeme yapabilirsiniz.')

    // Refresh membership status
    await checkCurrentMembership()
  } catch (error: any) {
    console.error('Membership error:', error)
    console.error('Response:', error.response?.data)
    const message = error.response?.data?.message || error.response?.data?.error || 'Başvuru oluşturulamadı.'
    toast.error(message)
  }
}

// Paket özelliklerini Türkçeleştir
const featureLabels: Record<string, string> = {
  'universal_access': 'Tüm şubelerden erişim',
  'group_classes': 'Grup derslerine katılım',
  'personal_trainer': 'Kişisel antrenör desteği',
  'pool_access': 'Havuz kullanımı',
  'sauna_access': 'Sauna kullanımı',
  'locker': 'Kilitli dolap',
  'shower': 'Duş imkanı',
  'parking': 'Ücretsiz otopark',
  'wifi': 'Wi-Fi erişimi',
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Üyelik Paketleri</h1>
        <p class="text-lg text-gray-600">Sizin için en uygun paketi seçin</p>
      </div>

      <!-- Branch Selection Step -->
      <div v-if="showBranchSelection" class="bg-white rounded-2xl shadow-sm p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4 text-center">Ne Üzerinde Çalışmak İstiyorsun?</h2>
        <p class="text-gray-600 mb-8 text-center">İlgilendiğin alanları seç, sana uygun programlar hazırlayalım.</p>

        <div v-if="loadingBranches" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
          <div
            v-for="branch in activeBranches"
            :key="branch.id"
            @click="selectBranch(branch.id)"
            class="p-4 rounded-xl border-2 cursor-pointer transition-all hover:scale-105"
            :class="selectedBranch === branch.id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
          >
            <div class="w-16 h-16 rounded-lg mx-auto mb-3 flex items-center justify-center overflow-hidden bg-gray-100">
              <img v-if="branch.image" :src="`http://localhost:8000/${branch.image}`" class="w-full h-full object-cover">
              <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
              </svg>
            </div>
            <p class="text-gray-800 text-sm font-medium text-center">{{ branch.name }}</p>
          </div>
        </div>

        <div class="text-center">
          <button
            @click="saveBranchesAndContinue"
            :disabled="selectedBranch === null"
            class="py-3 px-8 rounded-xl font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:from-indigo-700 hover:to-purple-700"
          >
            Devam Et ve Paketleri Gör
          </button>
        </div>
      </div>

      <!-- Plans Loading -->
      <div v-if="!showBranchSelection && loading" class="flex justify-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Plans Grid -->
      <div v-if="!showBranchSelection && !loading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="bg-white rounded-2xl shadow-sm overflow-hidden transition-all hover:shadow-lg relative"
          :class="plan.is_featured ? 'ring-2 ring-indigo-600' : ''"
        >
          <!-- Featured Badge -->
          <div
            v-if="plan.is_featured"
            class="absolute top-0 right-0 bg-indigo-600 text-white px-4 py-1 text-sm font-semibold rounded-bl-lg"
          >
            Önerilen
          </div>

          <!-- Plan Header -->
          <div class="p-6" :class="plan.is_featured ? 'bg-indigo-50' : ''">
            <h3 class="text-xl font-bold text-gray-900">{{ plan.name }}</h3>
            <p class="text-gray-600 mt-2 text-sm">{{ plan.description }}</p>
            <div class="mt-4">
              <span class="text-4xl font-bold text-gray-900">{{ plan.price }}</span>
              <span class="text-gray-600">₺</span>
              <span class="text-gray-500 text-sm"> / {{ plan.duration_days }} gün</span>
            </div>
          </div>

          <!-- Plan Features -->
          <div class="p-6 pt-0">
            <ul class="space-y-3">
              <li class="flex items-center gap-2 text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                {{ plan.duration_days }} gün geçerli
              </li>
              <li v-if="plan.session_count" class="flex items-center gap-2 text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                {{ plan.session_count }} seans hakkı
              </li>
              <li v-if="plan.type === 'unlimited'" class="flex items-center gap-2 text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Sınırsız kullanım
              </li>
              <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-2 text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                {{ featureLabels[feature] || feature }}
              </li>
            </ul>

            <!-- Select Button -->
            <button
              @click="selectPlan(plan.id)"
              class="mt-6 w-full py-3 px-6 rounded-lg font-semibold transition-colors"
              :class="plan.is_featured
                ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                : 'bg-gray-100 text-gray-900 hover:bg-gray-200'"
            >
              Başvur
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && !showBranchSelection && plans.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aktif paket bulunmuyor</h3>
        <p class="mt-1 text-sm text-gray-500">Üyelik paketleri yakında eklenecektir.</p>
      </div>
    </div>
  </div>
</template>
