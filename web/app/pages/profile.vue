<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <AppSidebar />
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 lg:pl-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between">
        <h1 class="text-xl font-semibold text-white">{{ isOwnProfile ? 'Profilim' : 'Kullanıcı Profili' }}</h1>
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
             <span class="text-white font-medium text-sm">{{ authStore.userInitials }}</span>
        </div>
      </header>

      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-violet-500"></div>
      </div>

      <div v-else-if="profileUser" class="p-6 lg:p-8 max-w-6xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden mb-8">
          <div class="h-32 bg-gradient-to-r from-violet-600 to-purple-800"></div>
          <div class="px-8 pb-8 relative">
            <div class="absolute -top-12 left-8">
              <div class="w-24 h-24 rounded-2xl bg-slate-900 border-4 border-slate-900 overflow-hidden shadow-2xl">
                 <img v-if="profileUser.avatar" :src="profileUser.avatar" class="w-full h-full object-cover">
                 <div v-else class="w-full h-full bg-violet-500 flex items-center justify-center text-white text-3xl font-bold">
                   {{ getUserInitials(profileUser.name) }}
                 </div>
              </div>
            </div>

            <div class="pt-16 flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div>
                <h2 class="text-2xl font-bold text-white">{{ profileUser.name }}</h2>
                <p class="text-slate-400">{{ profileUser.email }} • <span class="capitalize" :class="getRoleClass(profileUser.role)">{{ getRoleLabel(profileUser.role) }}</span></p>
              </div>
              <div v-if="isOwnProfile" class="flex gap-3">
                <button @click="isEditing = !isEditing" class="px-6 py-2.5 rounded-xl bg-slate-800 text-white font-medium hover:bg-slate-700 transition-all">
                  {{ isEditing ? 'İptal' : 'Profili Düzenle' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Form -->
        <div v-if="isEditing && isOwnProfile" class="bg-slate-900 border border-slate-800 rounded-3xl p-8 animate-in fade-in slide-in-from-bottom-4 duration-500 mb-8">
           <form @submit.prevent="updateProfile" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm text-slate-400 mb-2">Ad Soyad</label>
                  <input v-model="form.name" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500 transition-all">
                </div>
                <div>
                  <label class="block text-sm text-slate-400 mb-2">E-posta</label>
                  <input v-model="form.email" type="email" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none opacity-50 cursor-not-allowed" disabled>
                </div>
              </div>

              <div class="pt-4">
                <button type="submit" class="w-full py-4 bg-gradient-to-r from-violet-500 to-purple-600 text-white font-bold rounded-2xl shadow-lg shadow-violet-500/20 hover:shadow-violet-500/40 transition-all">
                  Değişiklikleri Kaydet
                </button>
              </div>
           </form>
        </div>

        <!-- User Info Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Membership Info (only for regular users) -->
          <div v-if="profileUser.role === 'user'" class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Üyelik Bilgileri</h3>
            <div v-if="membership" class="space-y-4">
              <div class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl">
                <div>
                  <p class="text-slate-400 text-sm">Paket</p>
                  <p class="text-white font-semibold">{{ membership.plan?.name || '-' }}</p>
                </div>
                <div class="text-right">
                  <p class="text-slate-400 text-sm">Durum</p>
                  <span class="px-3 py-1 rounded-lg text-xs font-semibold" :class="getStatusClass(membership.status)">
                    {{ getStatusLabel(membership.status) }}
                  </span>
                </div>
              </div>
              <div v-if="membership.status === 'active'" class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-slate-800/50 rounded-xl text-center">
                  <p class="text-2xl font-bold text-violet-400">{{ membership.remaining_days || 0 }}</p>
                  <p class="text-slate-400 text-xs">Kalan Gün</p>
                </div>
                <div class="p-4 bg-slate-800/50 rounded-xl text-center">
                  <p class="text-2xl font-bold text-emerald-400">{{ membership.total_days || 0 }}</p>
                  <p class="text-slate-400 text-xs">Toplam Gün</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-slate-500">
              <p>Üyelik bilgisi bulunmuyor</p>
            </div>
          </div>

          <!-- Personal Info -->
          <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Kişisel Bilgiler</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-slate-400">Telefon</span>
                <span class="text-white">{{ profileUser.phone || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Doğum Tarihi</span>
                <span class="text-white">{{ profileUser.date_of_birth ? formatDate(profileUser.date_of_birth) : '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Cinsiyet</span>
                <span class="text-white">{{ profileUser.gender ? (profileUser.gender === 'male' ? 'Erkek' : 'Kadın') : '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Boy</span>
                <span class="text-white">{{ profileUser.height || '-' }} cm</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Kilo</span>
                <span class="text-white">{{ profileUser.weight || '-' }} kg</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Assigned Programs -->
        <div v-if="profileUser.role === 'user'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 mb-8">
          <h3 class="text-lg font-semibold text-white mb-4">Atanan Programlar</h3>
          <div v-if="assignedPrograms.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="program in assignedPrograms" :key="program.id" class="p-4 bg-slate-800/50 rounded-xl hover:bg-slate-800 transition-all">
              <h4 class="text-white font-semibold mb-2">{{ program.title }}</h4>
              <p class="text-slate-400 text-sm mb-3">{{ program.description?.substring(0, 80) }}...</p>
              <div class="flex items-center justify-between">
                <span class="text-xs text-violet-400">{{ program.level }}</span>
                <span class="text-xs text-slate-500">{{ program.duration_weeks }} hafta</span>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-slate-500">
            <p>Henüz atanmış program yok</p>
          </div>
        </div>

        <!-- Progress Logs -->
        <div v-if="profileUser.role === 'user'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4">İlerleme Kayıtları</h3>
          <div v-if="progressLogs.length > 0" class="space-y-4">
            <div v-for="log in progressLogs.slice(0, 5)" :key="log.id" class="p-4 bg-slate-800/50 rounded-xl">
              <div class="flex items-center justify-between mb-2">
                <span class="text-white font-medium">{{ formatDate(log.created_at) }}</span>
                <span class="text-xs text-emerald-400">{{ log.weight }} kg</span>
              </div>
              <p v-if="log.notes" class="text-slate-400 text-sm">{{ log.notes }}</p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-slate-500">
            <p>Henüz ilerleme kaydı yok</p>
          </div>
        </div>
      </div>

      <div v-else class="p-8 text-center">
        <p class="text-slate-500">Kullanıcı bulunamadı</p>
        <NuxtLink to="/dashboard" class="inline-block mt-4 px-6 py-2 bg-violet-500 text-white rounded-xl">Dashboard'a Dön</NuxtLink>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

definePageMeta({
  middleware: 'auth',
  layout: false
})

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const api = useApi()

const profileUser = ref<any>(null)
const membership = ref<any>(null)
const assignedPrograms = ref<any[]>([])
const progressLogs = ref<any[]>([])
const loading = ref(true)
const isEditing = ref(false)

const form = ref({
  name: '',
  email: ''
})

// URL'den kullanıcı ID'si al, yoksa kendi profili
const userId = computed(() => route.params.id as string | undefined)

const isOwnProfile = computed(() => {
  return !userId.value || userId.value === authStore.user?.id.toString()
})

const canViewProfile = computed(() => {
  // Kendi profili her zaman görülebilir
  if (isOwnProfile.value) return true
  // Admin ve PT'ler herkesin profilini görebilir
  return authStore.isAdmin || authStore.user?.role === 'trainer'
})

const fetchProfileData = async () => {
  loading.value = true
  try {
    const targetUserId = userId.value || authStore.user?.id.toString()

    // Kullanıcı bilgisini al
    const userRes = await api.get(`/users/${targetUserId}`)
    profileUser.value = userRes.data.data

    // Kendi profili değilse ve izni yoksa engelle
    if (!canViewProfile.value) {
      router.push('/dashboard')
      return
    }

    // Formu doldur
    if (isOwnProfile.value) {
      form.value = {
        name: profileUser.value?.name || '',
        email: profileUser.value?.email || ''
      }
    }

    // Üyelik bilgisini al (sadece user rolü için)
    if (profileUser.value?.role === 'user') {
      try {
        const membershipRes = await api.get(`/users/${targetUserId}/membership`)
        membership.value = membershipRes.data.data

        // Programları al
        const programsRes = await api.get(`/users/${targetUserId}/programs`)
        assignedPrograms.value = programsRes.data.data || []

        // İlerleme kayıtlarını al
        const progressRes = await api.get(`/users/${targetUserId}/progress`)
        progressLogs.value = progressRes.data.data || []
      } catch (e) {
        // Üyelik bilgisi yoksa sessizce geç
      }
    }
  } catch (e: any) {
    console.error('Profil yüklenemedi:', e)
    if (e.response?.status === 404) {
      profileUser.value = null
    } else if (!isOwnProfile.value) {
      router.push('/dashboard')
    }
  } finally {
    loading.value = false
  }
}

const getUserInitials = (name: string) => {
  return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'
}

const getRoleLabel = (role: string) => {
  const labels: Record<string, string> = {
    'super_admin': 'Süper Admin',
    'admin': 'Admin',
    'trainer': 'Eğitmen',
    'user': 'Üye'
  }
  return labels[role] || role
}

const getRoleClass = (role: string) => {
  const classes: Record<string, string> = {
    'super_admin': 'text-rose-400',
    'admin': 'text-amber-400',
    'trainer': 'text-purple-400',
    'user': 'text-blue-400'
  }
  return classes[role] || 'text-slate-400'
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    'pending': 'Onay Bekliyor',
    'active': 'Aktif',
    'rejected': 'Reddedildi',
    'expired': 'Süresi Doldu'
  }
  return labels[status] || status
}

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    'pending': 'bg-amber-500/20 text-amber-400',
    'active': 'bg-emerald-500/20 text-emerald-400',
    'rejected': 'bg-red-500/20 text-red-400',
    'expired': 'bg-gray-500/20 text-gray-400'
  }
  return classes[status] || 'bg-slate-500/20 text-slate-400'
}

const formatDate = (date: string) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const updateProfile = async () => {
  try {
    await api.put('/profile', { name: form.value.name })
    await authStore.fetchUser()
    profileUser.value.name = form.value.name
    isEditing.value = false
    alert('Profil güncellendi!')
  } catch (e) {
    alert('Güncellenemedi')
  }
}

onMounted(fetchProfileData)

// Route değiştiğinde veriyi yenile
watch(() => route.params.id, fetchProfileData)
</script>
