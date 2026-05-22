<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Ekipman Türleri</h1>
        <button @click="openCreateModal" class="flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-emerald-500/20 transition-all">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Yeni Ekipman
        </button>
      </header>

      <div class="p-6 lg:p-8">
        <div v-if="loading" class="flex justify-center py-20">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-emerald-500"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <div
            v-for="(eq, index) in equipmentTypes"
            :key="eq.id"
            draggable="true"
            @dragstart="handleDragStart($event, index, 'equipment')"
            @dragover.prevent
            @drop="handleDrop($event, index, 'equipment')"
            @dragend="handleDragEnd"
            :class="[
              'p-6 rounded-3xl bg-slate-900 border transition-all group relative overflow-hidden cursor-move',
              draggedIndex === index ? 'opacity-50 scale-95' : 'hover:border-emerald-500/50',
              draggedIndex !== null && draggedIndex < index && index <= dropIndex ? 'translate-y-16' : '',
              draggedIndex !== null && draggedIndex > index && index >= dropIndex ? '-translate-y-16' : '',
              eq.is_active ? 'border-slate-800' : 'border-slate-800/30 opacity-60'
            ]"
          >
             <!-- Background Shape -->
             <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>

             <div class="flex items-start justify-between relative z-10">
                <div class="flex items-center flex-1">
                   <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mr-4 border border-slate-700 overflow-hidden">
                      <img v-if="eq.image" :src="`/storage/${eq.image}`" class="w-full h-full object-cover">
                      <svg v-else class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                   </div>
                   <div class="flex-1">
                      <h3 class="text-white font-bold text-lg">{{ eq.name }}</h3>
                      <p class="text-xs text-slate-500">{{ eq.is_active ? 'Aktif' : 'Pasif' }}</p>
                   </div>
                </div>
                <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                   <button @click="openEditModal(eq)" class="p-2 text-slate-400 hover:text-white transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                   <button @click="deleteEquipment(eq.id)" class="p-2 text-slate-400 hover:text-red-400 transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
             </div>

             <div class="mt-6 flex items-center justify-between text-xs font-bold relative z-10">
                <button
                  @click="toggleStatus(eq)"
                  :class="[
                    'px-3 py-1 rounded-lg border transition-all',
                    eq.is_active
                      ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
                      : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ eq.is_active ? 'AKTİF' : 'PASİF' }}
                </button>
             </div>
          </div>

          <div v-if="equipmentTypes.length === 0" class="col-span-full py-20 text-center text-slate-500 italic">Ekipman türü bulunamadı.</div>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden p-8">
          <h3 class="text-xl font-bold text-white mb-6">{{ isEditing ? 'Ekipmanı Düzenle' : 'Yeni Ekipman Ekle' }}</h3>
          <form @submit.prevent="saveEquipment" class="space-y-4">
            <div>
              <label class="block text-sm text-slate-400 mb-2">Ekipman Adı</label>
              <input v-model="form.name" type="text" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all">
            </div>
            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" id="isActive" class="w-5 h-5 rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500">
              <label for="isActive" class="ml-3 text-sm text-slate-400">Aktif</label>
            </div>
            <div>
              <label class="block text-sm text-slate-400 mb-2">Görsel</label>
              <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden">
                  <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover">
                  <svg v-else class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
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
              <button type="submit" class="flex-1 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl font-bold shadow-lg">Kaydet</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, type Ref } from 'vue'

definePageMeta({
  middleware: 'admin',
  layout: false
})

// Type definitions
interface EquipmentType {
  id: number
  name: string
  is_active: boolean
  image?: string
  sort_order: number
}

interface EquipmentTypeForm {
  name: string
  is_active: boolean
}

const api = useApi()
const equipmentTypes = ref<EquipmentType[]>([])
const loading = ref(true)
const isModalOpen = ref(false)
const isEditing = ref(false)
const currentId = ref<number | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref('')
const imageFile = ref<File | null>(null)

const form = ref<EquipmentTypeForm>({
  name: '',
  is_active: true
})

const draggedIndex = ref<number | null>(null)
const dropIndex = ref<number | null>(null)

const fetchEquipmentTypes = async () => {
  try {
    const res = await api.get('/equipment-types')
    equipmentTypes.value = res.data.data
  } catch (e) {
    console.error('Ekipman türleri yüklenemedi')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { name: '', is_active: true }
  imagePreview.value = ''
  imageFile.value = null
  isModalOpen.value = true
}

const openEditModal = (eq: EquipmentType) => {
  isEditing.value = true
  currentId.value = eq.id
  form.value = { name: eq.name, is_active: eq.is_active }
  imagePreview.value = eq.image ? `/storage/${eq.image}` : ''
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

const saveEquipment = async () => {
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('is_active', form.value.is_active ? '1' : '0')
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    if (isEditing.value) {
      formData.append('_method', 'PUT')
      await api.post(`/equipment-types/${currentId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await api.post('/equipment-types', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    await fetchEquipmentTypes()
    closeModal()
  } catch (e) {
    alert('Hata oluştu')
  }
}

const toggleStatus = async (eq: EquipmentType) => {
  try {
    await api.put(`/equipment-types/${eq.id}`, { is_active: !eq.is_active })
    await fetchEquipmentTypes()
  } catch (e: Error | unknown) {
    const errorMessage = e instanceof Error ? e.message : 'Güncelleme başarısız'
    alert(errorMessage)
  }
}

const deleteEquipment = async (id: number) => {
  if (confirm('Bu ekipman türünü silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/equipment-types/${id}`)
      await fetchEquipmentTypes()
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
const handleDragStart = (e: DragEvent, index: number, type: string) => {
  draggedIndex.value = index
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move'
  }
}

const handleDrop = async (e: DragEvent, index: number, type: string) => {
  e.preventDefault()
  if (draggedIndex.value === null || draggedIndex.value === index) return

  dropIndex.value = index

  // Reorder array
  const newOrder = [...equipmentTypes.value]
  const [removed] = newOrder.splice(draggedIndex.value!, 1)
  newOrder.splice(index, 0, removed)
  equipmentTypes.value = newOrder

  // Save to backend
  try {
    await api.post('/equipment-types/reorder', {
      orders: equipmentTypes.value.map((eq, i) => ({ id: eq.id, sort_order: i }))
    })
  } catch (err) {
    console.error('Sıralama kaydedilemedi', err)
    // Revert on error
    await fetchEquipmentTypes()
  }

  draggedIndex.value = null
  dropIndex.value = null
}

const handleDragEnd = () => {
  draggedIndex.value = null
  dropIndex.value = null
}

onMounted(fetchEquipmentTypes)
</script>
