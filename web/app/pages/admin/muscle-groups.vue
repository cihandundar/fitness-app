<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Kas Grupları</h1>
        <button @click="openCreateModal" class="flex items-center px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-violet-500/20 transition-all">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Yeni Kas Grubu
        </button>
      </header>

      <div class="p-6 lg:p-8">
        <div v-if="loading" class="flex justify-center py-20">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-violet-500"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <div
            v-for="(mg, index) in muscleGroups"
            :key="mg.id"
            draggable="true"
            @dragstart="handleDragStart($event, index)"
            @dragover.prevent
            @drop="handleDrop($event, index)"
            @dragend="handleDragEnd"
            :class="[
              'p-6 rounded-3xl bg-slate-900 border transition-all group relative overflow-hidden cursor-move',
              draggedIndex === index ? 'opacity-50 scale-95' : '',
              draggedIndex !== null && draggedIndex < index && index <= dropIndex ? 'translate-y-16' : '',
              draggedIndex !== null && draggedIndex > index && index >= dropIndex ? '-translate-y-16' : '',
              mg.is_active ? 'border-slate-800 hover:border-violet-500/50' : 'border-slate-800/30 opacity-60'
            ]"
          >
             <!-- Background Shape -->
             <div class="absolute -right-10 -bottom-10 w-32 h-32 rounded-full transition-transform duration-500"
                  :class="[
                    `bg-${mg.color || 'violet'}-500/5`,
                    'group-hover:scale-150'
                  ]"></div>

             <div class="flex items-start justify-between relative z-10">
                <div class="flex items-center flex-1">
                   <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mr-4 border border-slate-700 overflow-hidden">
                      <img v-if="mg.image" :src="`/storage/${mg.image}`" class="w-full h-full object-cover">
                      <svg v-else class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                   </div>
                   <div class="flex-1">
                      <h3 class="text-white font-bold text-lg">{{ mg.name }}</h3>
                   </div>
                </div>
                <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                   <button @click="openEditModal(mg)" class="p-2 text-slate-400 hover:text-white transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                   <button @click="deleteMuscleGroup(mg.id)" class="p-2 text-slate-400 hover:text-red-400 transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
             </div>

             <div class="mt-6 flex items-center justify-between text-xs font-bold relative z-10">
                <span class="px-3 py-1 rounded-lg bg-slate-800 text-slate-400 border border-slate-700 uppercase">{{ mg.color || 'violet' }}</span>
                <button
                  @click="toggleStatus(mg)"
                  :class="[
                    'px-3 py-1 rounded-lg border transition-all',
                    mg.is_active
                      ? 'bg-violet-500/20 text-violet-400 border-violet-500/30'
                      : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ mg.is_active ? 'AKTİF' : 'PASİF' }}
                </button>
             </div>
          </div>

          <div v-if="muscleGroups.length === 0" class="col-span-full py-20 text-center text-slate-500 italic">Kas grubu bulunamadı.</div>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden p-8">
          <h3 class="text-xl font-bold text-white mb-6">{{ isEditing ? 'Kas Grubunu Düzenle' : 'Yeni Kas Grubu Ekle' }}</h3>
          <form @submit.prevent="saveMuscleGroup" class="space-y-4">
            <div>
              <label class="block text-sm text-slate-400 mb-2">Ad</label>
              <input v-model="form.name" type="text" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-slate-400 mb-2">Renk</label>
                <select v-model="form.color" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500 transition-all">
                  <option value="violet">Violet</option>
                  <option value="emerald">Emerald</option>
                  <option value="red">Red</option>
                  <option value="blue">Blue</option>
                  <option value="orange">Orange</option>
                  <option value="pink">Pink</option>
                  <option value="cyan">Cyan</option>
                  <option value="yellow">Yellow</option>
                </select>
              </div>
              <div class="flex items-center pt-6">
                <input v-model="form.is_active" type="checkbox" id="isActive" class="w-5 h-5 rounded border-slate-700 bg-slate-800 text-violet-500 focus:ring-violet-500">
                <label for="isActive" class="ml-3 text-sm text-slate-400">Aktif</label>
              </div>
            </div>
            <div>
              <label class="block text-sm text-slate-400 mb-2">Görsel</label>
              <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden">
                  <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover">
                  <svg v-else class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <div class="flex-1">
                  <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    @change="handleImageChange"
                    class="hidden"
                  >
                  <button
                    type="button"
                    @click="$refs.fileInput?.click()"
                    class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl text-slate-400 hover:text-white hover:border-slate-600 transition-all"
                  >
                    Görsel Seç
                  </button>
                  <p class="text-xs text-slate-500 mt-2">PNG, JPG maks. 2MB</p>
                </div>
              </div>
            </div>
            <div class="pt-4 flex gap-4">
              <button type="button" @click="closeModal" class="flex-1 py-4 bg-slate-800 text-slate-400 rounded-2xl font-bold">İptal</button>
              <button type="submit" class="flex-1 py-4 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-2xl font-bold shadow-lg">Kaydet</button>
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

interface MuscleGroup {
  id: number
  name: string
  color: string
  is_active: boolean
  image?: string
}

const api = useApi()
const muscleGroups = ref<MuscleGroup[]>([])
const loading = ref(true)
const isModalOpen = ref(false)
const isEditing = ref(false)
const currentId = ref<number | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref('')
const imageFile = ref<File | null>(null)

const form = ref({
  name: '',
  color: 'violet',
  is_active: true
})

const draggedIndex = ref<number | null>(null)
const dropIndex = ref<number | null>(null)

const fetchMuscleGroups = async () => {
  try {
    const res = await api.get('/muscle-groups')
    muscleGroups.value = res.data.data
  } catch (e) {
    console.error('Kas grupları yüklenemedi')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { name: '', color: 'violet', is_active: true }
  imagePreview.value = ''
  imageFile.value = null
  isModalOpen.value = true
}

const openEditModal = (mg: MuscleGroup) => {
  isEditing.value = true
  currentId.value = mg.id
  form.value = { name: mg.name, color: mg.color, is_active: mg.is_active }
  imagePreview.value = mg.image ? `/storage/${mg.image}` : ''
  imageFile.value = null
  isModalOpen.value = true
}

const handleImageChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    imageFile.value = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

const saveMuscleGroup = async () => {
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('color', form.value.color)
    formData.append('is_active', form.value.is_active ? '1' : '0')
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    if (isEditing.value) {
      formData.append('_method', 'PUT')
      await api.post(`/muscle-groups/${currentId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await api.post('/muscle-groups', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    await fetchMuscleGroups()
    closeModal()
  } catch (e) {
    alert('Hata oluştu')
  }
}

const toggleStatus = async (mg: MuscleGroup) => {
  try {
    await api.put(`/muscle-groups/${mg.id}`, { is_active: !mg.is_active })
    await fetchMuscleGroups()
  } catch (e: Error | unknown) {
    const errorMessage = e instanceof Error ? e.message : 'Güncelleme başarısız'
    alert(errorMessage)
  }
}

const deleteMuscleGroup = async (id: number) => {
  if (confirm('Bu kas grubunu silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/muscle-groups/${id}`)
      await fetchMuscleGroups()
    } catch (e) {
      alert('Silinemedi')
    }
  }
}

const closeModal = () => {
  isModalOpen.value = false
  imagePreview.value = ''
  imageFile.value = null
}

// Drag & Drop handlers
const handleDragStart = (e: DragEvent, index: number) => {
  draggedIndex.value = index
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move'
  }
}

const handleDrop = async (e: DragEvent, index: number) => {
  e.preventDefault()
  if (draggedIndex.value === null || draggedIndex.value === index) return

  dropIndex.value = index

  // Reorder array
  const newOrder = [...muscleGroups.value]
  const [removed] = newOrder.splice(draggedIndex.value, 1)
  newOrder.splice(index, 0, removed)
  muscleGroups.value = newOrder

  // Save to backend
  try {
    await api.post('/muscle-groups/reorder', {
      orders: muscleGroups.value.map((mg, i) => ({ id: mg.id, sort_order: i }))
    })
  } catch (err) {
    console.error('Sıralama kaydedilemedi', err)
    // Revert on error
    await fetchMuscleGroups()
  }

  draggedIndex.value = null
  dropIndex.value = null
}

const handleDragEnd = () => {
  draggedIndex.value = null
  dropIndex.value = null
}

onMounted(fetchMuscleGroups)
</script>
