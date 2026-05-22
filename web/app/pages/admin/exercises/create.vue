<template>
  <div class="min-h-screen bg-slate-950 flex overflow-hidden">
    <!-- Sidebar -->
    <aside class="hidden lg:flex w-72 bg-slate-900/95 backdrop-blur-xl border-r border-slate-800/50 flex-col sticky top-0 h-screen">
      <div class="flex flex-col h-full">
        <div class="flex items-center h-16 px-6 border-b border-slate-800/50 flex-shrink-0">
          <NuxtLink to="/admin" class="flex items-center space-x-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            </div>
            <span class="text-lg font-bold bg-gradient-to-r from-red-400 to-orange-400 bg-clip-text text-transparent">Admin Panel</span>
          </NuxtLink>
        </div>
        <nav class="flex-1 p-4 space-y-1">
          <NuxtLink to="/admin/exercises" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
            Geri Dön
          </NuxtLink>
        </nav>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto bg-slate-950 p-6 lg:p-8">
      <div class="max-w-4xl mx-auto">
        <header class="mb-8">
          <h1 class="text-3xl font-bold text-white">Yeni Egzersiz Ekle</h1>
          <p class="text-slate-400">Kütüphaneye yeni bir egzersiz tanımlayın.</p>
        </header>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Başlık ve Slug -->
            <div class="space-y-4 md:col-span-2">
              <div class="space-y-2">
                <label class="text-sm font-medium text-slate-300">Egzersiz Başlığı</label>
                <input 
                  v-model="form.name" 
                  @input="generateSlug"
                  type="text" 
                  placeholder="Örn: Bench Press"
                  class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-red-500 transition-all"
                  required
                >
              </div>
              <div class="space-y-2 text-xs text-slate-500">
                <span>Safe URL: </span>
                <span class="text-red-400 font-mono">{{ form.slug }}</span>
              </div>
            </div>

            <!-- Kas Grubu -->
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-300">Ana Kas Grubu</label>
              <select v-model="form.muscle_group" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-red-500 transition-all" required>
                <option value="chest">Göğüs</option>
                <option value="back">Sırt</option>
                <option value="shoulders">Omuz</option>
                <option value="legs">Bacak</option>
                <option value="arms">Kol</option>
                <option value="core">Karın</option>
              </select>
            </div>

            <!-- Zorluk -->
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-300">Zorluk Seviyesi</label>
              <select v-model="form.difficulty" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-red-500 transition-all" required>
                <option value="beginner">Başlangıç</option>
                <option value="intermediate">Orta</option>
                <option value="advanced">İleri</option>
              </select>
            </div>

            <!-- Görsel URL -->
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-300">Görsel URL (Image)</label>
              <input v-model="form.image" type="text" placeholder="https://..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-red-500 transition-all">
            </div>

            <!-- Video URL -->
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-300">Video URL (YouTube/MP4)</label>
              <input v-model="form.video_url" type="text" placeholder="https://..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-red-500 transition-all">
            </div>

            <!-- Açıklama -->
            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium text-slate-300">Açıklama</label>
              <textarea v-model="form.description" rows="4" placeholder="Egzersiz nasıl yapılır?" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-red-500 transition-all"></textarea>
            </div>
          </div>

          <!-- Kaydet Butonu -->
          <div class="pt-6 border-t border-slate-800 flex justify-end">
            <button 
              type="submit" 
              class="px-8 py-3 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-red-500/30 transition-all disabled:opacity-50"
              :disabled="loading"
            >
              {{ loading ? 'Kaydediliyor...' : 'Egzersizi Kaydet' }}
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'

definePageMeta({
  middleware: 'admin',
  layout: false
})

const loading = ref(false)

const form = reactive({
  name: '',
  slug: '',
  description: '',
  muscle_group: 'chest',
  equipment: 'none',
  difficulty: 'beginner',
  image: '',
  video_url: '',
  data: {} // PostgreSQL JSONB alanı için
})

// Başlıktan otomatik Slug üretme mantığı
const generateSlug = () => {
  form.slug = form.name
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

const handleSubmit = async () => {
  loading.value = true
  try {
    // Burada API isteği atılacak
    console.log('Form Gönderiliyor:', form)
    alert('Egzersiz başarıyla oluşturuldu! (Simüle edildi)')
    await navigateTo('/admin/exercises')
  } catch (error) {
    console.error('Hata:', error)
  } finally {
    loading.value = false
  }
}
</script>
