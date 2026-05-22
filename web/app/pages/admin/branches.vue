<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Branş Yönetimi</h1>
        <button @click="openCreateModal" class="flex items-center px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-violet-500/20 transition-all">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Yeni Branş
        </button>
      </header>

      <div class="p-6 lg:p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
          <div v-for="(branch, index) in branches" :key="branch.id" 
               draggable="true"
               @dragstart="onDragStart($event, index)"
               @dragover.prevent
               @drop="onDrop($event, index)"
               class="p-6 rounded-3xl bg-slate-900 border border-slate-800 hover:border-violet-500/50 transition-all group relative cursor-move">
             <div class="flex justify-between items-start mb-4">
                <div class="w-20 h-20 rounded-2xl bg-violet-500/10 flex items-center justify-center text-violet-400 overflow-hidden border border-slate-800 shadow-xl">
                  <img v-if="branch.image" :src="`http://localhost:8000/${branch.image}`" class="w-full h-full object-cover">
                  <svg v-else-if="branch.name.toLowerCase().includes('fitness')" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                  </svg>
                  <svg v-else-if="branch.name.toLowerCase().includes('crossfit')" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <svg v-else class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                  </svg>
                </div>
                <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                   <button @click="openEditModal(branch)" class="p-2 text-slate-400 hover:text-white transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                   <button @click="deleteBranch(branch.id)" class="p-2 text-slate-400 hover:text-red-400 transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
             </div>
             
             <h3 class="text-xl font-bold text-white mb-2">{{ branch.name }}</h3>
             <p class="text-sm text-slate-500 mb-4">{{ branch.is_active ? 'Aktif' : 'Pasif' }} • Sıra: {{ branch.order }}</p>
          </div>
        </div>
        
        <div v-if="branches.length === 0" class="py-20 text-center text-slate-500 italic">Henüz branş oluşturulmamış.</div>
      </div>

      <!-- Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden p-8 animate-in zoom-in-95 duration-200">
          <h3 class="text-xl font-bold text-white mb-6">{{ editingId ? 'Branşı Düzenle' : 'Yeni Branş Ekle' }}</h3>
          <form @submit.prevent="saveBranch" class="space-y-4">
            <div>
              <label class="block text-sm text-slate-400 mb-2">Branş Adı</label>
              <input v-model="form.name" type="text" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-violet-500 transition-all">
            </div>
            
            <!-- Image Upload Section -->
            <div>
              <label class="block text-sm text-slate-400 mb-2">Görsel (Sürükle bırak veya tıkla)</label>
              <div @dragover.prevent @drop.prevent="onImageDrop" 
                   @click="$refs.fileInput.click()"
                   class="w-full h-32 border-2 border-dashed border-slate-700 rounded-2xl flex flex-col items-center justify-center text-slate-500 hover:border-violet-500/50 hover:bg-slate-800/50 transition-all cursor-pointer overflow-hidden relative">
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="onImageSelect">
                <template v-if="imagePreview">
                  <img :src="imagePreview" class="absolute inset-0 w-full h-full object-cover">
                  <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                    <span class="text-white text-xs font-bold">Değiştirmek için tıkla</span>
                  </div>
                </template>
                <template v-else>
                  <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span class="text-xs">JPG, PNG veya SVG</span>
                </template>
              </div>
            </div>

            <div>
              <label class="block text-sm text-slate-400 mb-2">Sıralama</label>
              <input v-model="form.order" type="number" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white">
            </div>
            <div class="flex items-center space-x-2 py-2">
               <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-violet-500">
               <label class="text-sm text-slate-300 cursor-pointer">Aktif</label>
            </div>
            <div class="pt-4 flex gap-4">
              <button type="button" @click="closeModal" class="flex-1 py-4 bg-slate-800 text-slate-400 rounded-2xl font-bold">İptal</button>
              <button type="submit" class="flex-1 py-4 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-2xl font-bold shadow-lg shadow-violet-500/20">Kaydet</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

definePageMeta({
  middleware: 'admin',
  layout: false
})

// Type definitions
interface Branch {
  id: number
  name: string
  order: number
  is_active: boolean
  image?: string
}

interface BranchForm {
  name: string
  order: number
  is_active: boolean
}

const api = useApi()
const branches = ref<Branch[]>([])
const isModalOpen = ref(false)
const editingId = ref<number | null>(null)
const imagePreview = ref<string | null>(null)
const selectedFile = ref<File | null>(null)

const form = ref<BranchForm>({
  name: '',
  order: 0,
  is_active: true
})

const fetchBranches = async () => {
  try {
    const res = await api.get('/branches')
    branches.value = res.data.data
  } catch (e) {
    console.error('Branşlar yüklenemedi', e)
  }
}

// Drag & Drop Sorting
const dragIndex = ref<number | null>(null)

const onDragStart = (e: DragEvent, index: number) => {
  dragIndex.value = index
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move'
  }
}

const onDrop = async (e: DragEvent, index: number) => {
  if (dragIndex.value === null || dragIndex.value === index) return
  
  const items = [...branches.value]
  const draggedItem = items.splice(dragIndex.value, 1)[0]
  items.splice(index, 0, draggedItem)
  
  // Update orders locally
  items.forEach((item, i) => {
    item.order = i + 1
  })
  
  branches.value = items
  dragIndex.value = null
  
  // Save to backend
  try {
    await api.post('/branches/update-order', {
      orders: items.map(item => ({ id: item.id, order: item.order }))
    })
  } catch (e) {
    console.error('Order update failed', e)
    fetchBranches() // Revert on failure
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

const openCreateModal = () => {
  editingId.value = null
  form.value = { name: '', order: branches.value.length + 1, is_active: true }
  imagePreview.value = null
  selectedFile.value = null
  isModalOpen.value = true
}

const openEditModal = (branch: Branch) => {
  editingId.value = branch.id
  form.value = { name: branch.name, order: branch.order, is_active: branch.is_active }
  imagePreview.value = branch.image ? `http://localhost:8000/${branch.image}` : null
  selectedFile.value = null
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingId.value = null
  imagePreview.value = null
  selectedFile.value = null
}

const saveBranch = async () => {
  try {
    let branchId = editingId.value
    if (editingId.value) {
      await api.put(`/branches/${editingId.value}`, form.value)
    } else {
      const res = await api.post('/branches', form.value)
      branchId = res.data.data.id
    }

    // Upload image if selected
    if (selectedFile.value && branchId) {
      const formData = new FormData()
      formData.append('image', selectedFile.value)
      await api.post(`/branches/${branchId}/upload-image`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }

    await fetchBranches()
    closeModal()
  } catch (e: Error | unknown) {
    const errorMessage = e instanceof Error ? e.message : 'Hata oluştu'
    alert(errorMessage)
  }
}

const deleteBranch = async (id: number) => {
  if (confirm('Bu branşı silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/branches/${id}`)
      await fetchBranches()
    } catch (e) {
      alert('Silinemedi')
    }
  }
}

onMounted(fetchBranches)
</script>
