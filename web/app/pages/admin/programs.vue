<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Program Yönetimi</h1>
        <button @click="openCreateModal" class="flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-red-500/20 transition-all">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Yeni Program
        </button>
      </header>

      <div class="p-6 lg:p-8">
        <div v-if="loading" class="flex justify-center py-20">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-500"></div>
        </div>

        <div v-else class="bg-slate-900/50 border border-slate-800/50 rounded-2xl overflow-hidden shadow-2xl">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-slate-800/50 bg-slate-900/80">
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider">Program Bilgisi</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider">Seviye</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider">Süre</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider">Durum</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider text-right">İşlemler</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/50">
                <tr v-for="program in programs" :key="program.id" class="hover:bg-slate-800/30 transition-colors group">
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="w-12 h-12 rounded-xl bg-slate-800 mr-4 flex-shrink-0 flex items-center justify-center border border-slate-700 overflow-hidden">
                        <img v-if="program.image" :src="program.image" class="w-full h-full object-cover">
                        <svg v-else class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                      </div>
                      <div>
                        <div class="text-white font-bold">{{ program.title }}</div>
                        <div class="text-xs text-slate-500">ID: #{{ program.id }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="levelClass(program.level)" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border">
                      {{ program.level }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-slate-300 font-medium">{{ program.duration_weeks }} Hafta</td>
                  <td class="px-6 py-4">
                    <span :class="program.is_active ? 'text-green-400' : 'text-slate-500'" class="flex items-center text-xs font-bold uppercase">
                      <span class="w-1.5 h-1.5 rounded-full mr-2" :class="program.is_active ? 'bg-green-400' : 'bg-slate-500'"></span>
                      {{ program.is_active ? 'Aktif' : 'Pasif' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right space-x-2">
                    <button @click="openEditModal(program)" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button @click="deleteProgram(program.id)" class="p-2 text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-all">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="programs.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">Program bulunamadı.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
          <div class="px-8 py-6 border-b border-slate-800 flex justify-between items-center bg-slate-900/50">
            <h3 class="text-xl font-bold text-white">{{ isEditing ? 'Programı Düzenle' : 'Yeni Program Ekle' }}</h3>
            <button @click="isModalOpen = false" class="text-slate-500 hover:text-white transition-all">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          
          <form @submit.prevent="saveProgram" class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-400 mb-2">Program Adı</label>
                <input v-model="form.title" type="text" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500 transition-all">
              </div>

              <!-- Image Upload Section -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-400 mb-2">Program Görseli</label>
                <div @dragover.prevent @drop.prevent="onImageDrop" 
                     @click="$refs.fileInput.click()"
                     class="w-full h-40 border-2 border-dashed border-slate-700 rounded-2xl flex flex-col items-center justify-center text-slate-500 hover:border-red-500/50 hover:bg-slate-800/50 transition-all cursor-pointer overflow-hidden relative">
                  <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="onImageSelect">
                  <template v-if="imagePreview">
                    <img :src="imagePreview" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                      <span class="text-white text-sm font-bold">Değiştirmek için tıkla veya sürükle</span>
                    </div>
                  </template>
                  <template v-else>
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-sm">Görsel seçmek için tıkla veya sürükle bırak</span>
                  </template>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Seviye</label>
                <select v-model="form.level" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500">
                  <option value="beginner">Başlangıç</option>
                  <option value="intermediate">Orta</option>
                  <option value="advanced">İleri</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Süre (Hafta)</label>
                <input v-model="form.duration_weeks" type="number" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500">
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-400 mb-2">Açıklama</label>
                <textarea v-model="form.description" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500"></textarea>
              </div>
            </div>

            <div class="flex items-center space-x-6">
               <label class="flex items-center cursor-pointer group">
                  <input v-model="form.is_active" type="checkbox" class="hidden">
                  <div :class="form.is_active ? 'bg-green-500 border-green-500' : 'bg-slate-800 border-slate-700'" class="w-5 h-5 border-2 rounded mr-2 flex items-center justify-center transition-all">
                    <svg v-if="form.is_active" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                  </div>
                  <span class="text-sm text-slate-300 group-hover:text-white transition-all">Aktif Olarak Yayınla</span>
               </label>
            </div>

            <div class="flex gap-4 pt-4">
              <button type="button" @click="isModalOpen = false" class="flex-1 py-4 bg-slate-800 text-slate-400 rounded-2xl font-bold hover:bg-slate-700 transition-all">İptal</button>
              <button type="submit" :disabled="submitting" class="flex-1 py-4 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-2xl font-bold shadow-lg shadow-red-500/20 hover:shadow-red-500/40 transition-all disabled:opacity-50">
                {{ submitting ? 'Kaydediliyor...' : (isEditing ? 'Güncelle' : 'Oluştur') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

definePageMeta({
  middleware: 'admin',
  layout: false
})

interface Program {
  id: number
  title: string
  level: string
  duration_weeks: number
  is_active: boolean
  is_featured: boolean
}

const api = useApi()
const programs = ref<Program[]>([])
const loading = ref(true)
const submitting = ref(false)
const isModalOpen = ref(false)
const isEditing = ref(false)
const currentId = ref(null)
const imagePreview = ref<string | null>(null)
const selectedFile = ref<File | null>(null)

const form = ref({
  title: '',
  description: '',
  level: 'beginner',
  duration_weeks: 4,
  is_active: true
})

const fetchPrograms = async () => {
  try {
    const res = await api.get('/programs')
    programs.value = res.data.data
  } catch (e) {
    console.error('Programlar yüklenemedi')
  } finally {
    loading.value = false
  }
}

// Image Handling
const onImageSelect = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (input.files && input.files[0]) {
    handleImageFile(input.files[0])
  }
}

const onImageDrop = (e: DragEvent) => {
  if (e.dataTransfer && e.dataTransfer.files[0]) {
    handleImageFile(e.dataTransfer.files[0])
  }
}

const handleImageFile = (file: File) => {
  selectedFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

const levelClass = (level: string) => {
  switch (level) {
    case 'beginner': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
    case 'intermediate': return 'bg-amber-500/10 text-amber-400 border-amber-500/20'
    case 'advanced': return 'bg-rose-500/10 text-rose-400 border-rose-500/20'
    default: return 'bg-slate-500/10 text-slate-400'
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { title: '', description: '', level: 'beginner', duration_weeks: 4, is_active: true }
  imagePreview.value = null
  selectedFile.value = null
  isModalOpen.value = true
}

const openEditModal = (program: Program) => {
  isEditing.value = true
  currentId.value = program.id
  form.value = { ...program }
  imagePreview.value = program.image ? `http://localhost:8000/${program.image}` : null
  selectedFile.value = null
  isModalOpen.value = true
}

const saveProgram = async () => {
  submitting.value = true
  try {
    let programId = currentId.value
    if (isEditing.value) {
      await api.put(`/programs/${currentId.value}`, form.value)
    } else {
      const res = await api.post('/programs', form.value)
      programId = res.data.data.id
    }

    // Upload image if selected
    if (selectedFile.value && programId) {
      const formData = new FormData()
      formData.append('image', selectedFile.value)
      await api.post(`/programs/${programId}/upload-image`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }

    await fetchPrograms()
    isModalOpen.value = false
  } catch (e) {
    alert('Hata oluştu')
  } finally {
    submitting.value = false
  }
}

const deleteProgram = async (id: number) => {
  if (confirm('Bu programı silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/programs/${id}`)
      await fetchPrograms()
    } catch (e) {
      alert('Silinemedi')
    }
  }
}

onMounted(fetchPrograms)
</script>
