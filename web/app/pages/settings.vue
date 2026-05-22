<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <AppSidebar />
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 lg:pl-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between">
        <h1 class="text-xl font-semibold text-white">Ayarlar</h1>
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
             <span class="text-white font-medium text-sm">SA</span>
        </div>
      </header>
      
      <div class="p-6 lg:p-8 max-w-4xl mx-auto">
        <div class="space-y-6">
          <!-- Account Settings -->
          <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center">
              <svg class="w-6 h-6 mr-3 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              Güvenlik
            </h3>
            
            <form @submit.prevent="updatePassword" class="space-y-4">
              <div>
                <label class="block text-sm text-slate-400 mb-2">Mevcut Şifre</label>
                <input v-model="passForm.current_password" type="password" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500">
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm text-slate-400 mb-2">Yeni Şifre</label>
                  <input v-model="passForm.password" type="password" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500">
                </div>
                <div>
                  <label class="block text-sm text-slate-400 mb-2">Yeni Şifre (Tekrar)</label>
                  <input v-model="passForm.password_confirmation" type="password" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500">
                </div>
              </div>
              <div class="pt-4">
                <button type="submit" class="px-8 py-3 bg-violet-500 text-white font-bold rounded-xl hover:bg-violet-600 transition-all">Şifreyi Güncelle</button>
              </div>
            </form>
          </div>

          <!-- Notification Settings -->
          <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center">
              <svg class="w-6 h-6 mr-3 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
              Bildirimler
            </h3>
            
            <div class="space-y-4">
               <div v-for="setting in notificationSettings" :key="setting.id" class="flex items-center justify-between p-4 bg-slate-800/50 rounded-2xl">
                  <div>
                    <p class="text-white font-medium">{{ setting.title }}</p>
                    <p class="text-xs text-slate-500">{{ setting.desc }}</p>
                  </div>
                  <button @click="setting.enabled = !setting.enabled" :class="setting.enabled ? 'bg-violet-500' : 'bg-slate-700'" class="w-12 h-6 rounded-full relative transition-all duration-300">
                    <div :class="setting.enabled ? 'translate-x-6' : 'translate-x-1'" class="absolute top-1 w-4 h-4 bg-white rounded-full transition-transform duration-300"></div>
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

const passForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const notificationSettings = ref([
  { id: 1, title: 'E-posta Bildirimleri', desc: 'Yeni program atandığında haber ver.', enabled: true },
  { id: 2, title: 'Randevu Hatırlatıcıları', desc: 'Randevuya 1 saat kala bildirim gönder.', enabled: true },
  { id: 3, title: 'Gelişim Raporları', desc: 'Haftalık gelişim özetimi gönder.', enabled: false }
])

const updatePassword = () => {
  alert('Şifre güncelleme özelliği yakında aktif olacak.')
}
</script>
