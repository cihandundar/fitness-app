<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <AppSidebar />
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 lg:pl-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between">
        <h1 class="text-xl font-semibold text-white">Profilim</h1>
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
             <span class="text-white font-medium text-sm">SA</span>
        </div>
      </header>
      
      <div class="p-6 lg:p-8 max-w-4xl mx-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden mb-8">
          <div class="h-32 bg-gradient-to-r from-violet-600 to-purple-800"></div>
          <div class="px-8 pb-8 relative">
            <div class="absolute -top-12 left-8">
              <div class="w-24 h-24 rounded-2xl bg-slate-900 border-4 border-slate-900 overflow-hidden shadow-2xl">
                 <img v-if="user?.avatar" :src="user.avatar" class="w-full h-full object-cover">
                 <div v-else class="w-full h-full bg-violet-500 flex items-center justify-center text-white text-3xl font-bold">
                   {{ userInitials }}
                 </div>
              </div>
            </div>
            
            <div class="pt-16 flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div>
                <h2 class="text-2xl font-bold text-white">{{ user?.name }}</h2>
                <p class="text-slate-400">{{ user?.email }} • <span class="capitalize text-violet-400">{{ user?.role }}</span></p>
              </div>
              <button @click="isEditing = !isEditing" class="px-6 py-2.5 rounded-xl bg-slate-800 text-white font-medium hover:bg-slate-700 transition-all">
                {{ isEditing ? 'İptal' : 'Profili Düzenle' }}
              </button>
            </div>
          </div>
        </div>

        <div v-if="isEditing" class="bg-slate-900 border border-slate-800 rounded-3xl p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
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
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
  layout: false
})

const authStore = useAuthStore()
const api = useApi()
const user = computed(() => authStore.user)
const isEditing = ref(false)

const form = ref({
  name: user.value?.name || '',
  email: user.value?.email || ''
})

const userInitials = computed(() => {
  return user.value?.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'
})

const updateProfile = async () => {
  try {
    await api.put('/profile', { name: form.value.name })
    await authStore.fetchUser()
    isEditing.value = false
    alert('Profil güncellendi!')
  } catch (e) {
    alert('Güncellenemedi')
  }
}
</script>
