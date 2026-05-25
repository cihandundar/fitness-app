<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApi } from '~/composables/useApi'
import { useToast } from '~/composables/useToast'

definePageMeta({
  middleware: 'admin',
  layout: false,
  title: 'Üyelik Onayları - Admin - FitApp'
})

const api = useApi()
const toast = useToast()

const loading = ref(true)
const processing = ref(false)
const memberships = ref<any[]>([])
const rejectionReason = ref('')
const selectedMembership = ref<any>(null)
const showRejectModal = ref(false)
const sidebarOpen = ref(false)

onMounted(async () => {
  await fetchPendingMemberships()
})

async function fetchPendingMemberships() {
  loading.value = true
  try {
    const response = await api.get('/user-memberships/pending')
    memberships.value = response.data?.data || []
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Üyelikler yüklenemedi')
  } finally {
    loading.value = false
  }
}

async function approveMembership(membership: any) {
  processing.value = true
  try {
    await api.post(`/user-memberships/${membership.id}/approve`)
    toast.success('Üyelik onaylandı!')
    memberships.value = memberships.value.filter(m => m.id !== membership.id)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Onaylama başarısız')
  } finally {
    processing.value = false
  }
}

function openRejectModal(membership: any) {
  selectedMembership.value = membership
  rejectionReason.value = ''
  showRejectModal.value = true
}

async function rejectMembership() {
  if (!rejectionReason.value.trim()) {
    toast.error('Red sebebini giriniz')
    return
  }

  processing.value = true
  try {
    await api.post(`/user-memberships/${selectedMembership.value.id}/reject`, {
      reason: rejectionReason.value
    })
    toast.success('Üyelik reddedildi')
    memberships.value = memberships.value.filter(m => m.id !== selectedMembership.value.id)
    showRejectModal.value = false
    selectedMembership.value = null
    rejectionReason.value = ''
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Reddetme başarısız')
  } finally {
    processing.value = false
  }
}

function closeRejectModal() {
  showRejectModal.value = false
  selectedMembership.value = null
  rejectionReason.value = ''
}

function openSidebar() {
  sidebarOpen.value = true
}

function closeSidebar() {
  sidebarOpen.value = false
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('tr-TR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <AppSidebar :is-open="sidebarOpen" @close="closeSidebar" />

    <main class="flex-1 h-screen overflow-y-auto min-w-0 bg-slate-950 lg:pl-72">
      <!-- Header -->
      <header class="lg:hidden sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50">
        <div class="flex items-center justify-between px-4 sm:px-6 h-16">
          <div class="flex items-center gap-2">
            <button @click="openSidebar" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
            </button>
            <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-amber-500/20 border border-amber-500/30">
              <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="text-amber-400 text-xs font-medium">Onay Bekliyor</span>
            </div>
            <h1 class="text-lg font-semibold text-white">Üyelik Onayları</h1>
          </div>
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
            <span class="text-white font-medium text-sm">A</span>
          </div>
        </div>
      </header>

      <header class="hidden lg:flex sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 items-center px-6 lg:px-8 justify-between w-full">
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-500/20 border border-amber-500/30">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-amber-400 text-sm font-medium">Onay Bekliyor</span>
          </div>
          <h1 class="text-xl font-semibold text-white">Üyelik Onayları</h1>
        </div>
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
            <span class="text-white font-medium text-sm">A</span>
          </div>
        </div>
      </header>

      <div class="p-4 sm:p-6 lg:p-8">
        <!-- Pending Count -->
        <div class="mb-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
              <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-semibold text-white">Onay Bekleyen Üyelikler</h2>
              <p class="text-slate-400 text-sm">{{ memberships.length }} üyelik onay bekliyor</p>
            </div>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Empty State -->
        <div v-else-if="memberships.length === 0" class="text-center py-16">
          <div class="inline-flex w-20 h-20 rounded-full bg-emerald-500/20 flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-white mb-2">Hepsi Tamam!</h3>
          <p class="text-slate-400">Onay bekleyen üyelik bulunmuyor.</p>
        </div>

        <!-- Memberships List -->
        <div v-else class="space-y-4">
          <div
            v-for="membership in memberships"
            :key="membership.id"
            class="bg-slate-900/50 border border-slate-800/50 rounded-2xl p-6 hover:border-slate-700/50 transition-all"
          >
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
              <!-- User Info -->
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                  {{ membership.user?.name?.charAt(0) || 'U' }}
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-white">{{ membership.user?.name }}</h3>
                  <p class="text-slate-400 text-sm">{{ membership.user?.email }}</p>
                </div>
              </div>

              <!-- Plan Info -->
              <div class="flex-1">
                <div class="inline-flex items-center px-4 py-2 rounded-xl bg-violet-500/20 border border-violet-500/30">
                  <svg class="w-5 h-5 text-violet-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <div>
                    <p class="text-white font-medium">{{ membership.plan?.name }}</p>
                    <p class="text-slate-400 text-sm">{{ membership.plan?.price }} ₺</p>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-3">
                <button
                  @click="approveMembership(membership)"
                  :disabled="processing"
                  class="flex-1 lg:flex-none px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Onayla
                </button>
                <button
                  @click="openRejectModal(membership)"
                  :disabled="processing"
                  class="flex-1 lg:flex-none px-5 py-2.5 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                  Reddet
                </button>
              </div>
            </div>

            <!-- Dates -->
            <div class="mt-4 pt-4 border-t border-slate-800/50 flex flex-wrap gap-4 text-sm">
              <div class="flex items-center gap-2 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Başvuru: {{ formatDate(membership.created_at) }}</span>
              </div>
              <div class="flex items-center gap-2 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Başlangıç: {{ formatDate(membership.start_date) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeRejectModal"></div>
      <div class="relative bg-slate-900 border border-slate-800 rounded-2xl p-6 w-full max-w-md">
        <h3 class="text-xl font-semibold text-white mb-4">Üyeliği Reddet</h3>
        <p class="text-slate-400 mb-4">Lütfen reddetme sebebini belirtin.</p>

        <textarea
          v-model="rejectionReason"
          rows="4"
          placeholder="Red sebebini giriniz..."
          class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/50 transition-all resize-none"
        ></textarea>

        <div class="flex gap-3 mt-6">
          <button
            @click="closeRejectModal"
            class="flex-1 px-5 py-2.5 rounded-xl bg-slate-800 text-white font-medium hover:bg-slate-700 transition-colors"
          >
            İptal
          </button>
          <button
            @click="rejectMembership"
            :disabled="processing || !rejectionReason.trim()"
            class="flex-1 px-5 py-2.5 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Reddet
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
