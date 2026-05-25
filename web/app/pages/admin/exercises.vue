<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Egzersiz Yönetimi</h1>
        <button @click="openCreateModal" class="flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-red-500/20 transition-all">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Yeni Egzersiz
        </button>
      </header>

      <div class="p-6 lg:p-8">
        <div v-if="loading" class="flex justify-center py-20">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-500"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <div v-for="exercise in exercises" :key="exercise.id" class="p-6 rounded-3xl bg-slate-900 border border-slate-800 hover:border-red-500/50 transition-all group relative overflow-hidden">
             <!-- Background Shape -->
             <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-red-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>

             <div class="flex items-start justify-between relative z-10">
                <div class="flex items-center flex-1">
                   <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mr-4 border border-slate-700 overflow-hidden">
                      <img v-if="exercise.image" :src="`/storage/${exercise.image}`" class="w-full h-full object-cover">
                      <svg v-else class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                   </div>
                   <div class="flex-1">
                      <h3 class="text-white font-bold text-lg">{{ exercise.name }}</h3>
                      <p class="text-xs text-slate-500 font-bold uppercase tracking-widest">{{ getMuscleGroupName(exercise.muscle_group_id) }}</p>
                   </div>
                </div>
                <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                   <button @click="openEditModal(exercise)" class="p-2 text-slate-400 hover:text-white transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                   <button @click="deleteExercise(exercise.id)" class="p-2 text-slate-400 hover:text-red-400 transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
             </div>

             <div class="mt-6 flex items-center justify-between text-xs font-bold relative z-10">
                <span class="px-3 py-1 rounded-lg bg-slate-800 text-slate-400 uppercase border border-slate-700">{{ getEquipmentName(exercise.equipment_type_id) }}</span>
                <span v-if="exercise.is_active" class="text-emerald-400">AKTİF</span>
                <span v-else class="text-slate-600">PASİF</span>
             </div>
          </div>

          <div v-if="exercises.length === 0" class="col-span-full py-20 text-center text-slate-500 italic">Egzersiz bulunamadı.</div>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm overflow-y-auto py-10">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden p-8">
          <h3 class="text-xl font-bold text-white mb-6">{{ isEditing ? 'Egzersizi Düzenle' : 'Yeni Egzersiz Ekle' }}</h3>
          <form @submit.prevent="saveExercise" class="space-y-4">
            <div>
              <label class="block text-sm text-slate-400 mb-2">Egzersiz Adı</label>
              <input v-model="form.name" type="text" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-slate-400 mb-2">Kas Grubu</label>
                <select v-model="form.muscle_group_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500 transition-all">
                  <option :value="null">Seçiniz</option>
                  <option v-for="mg in muscleGroups" :key="mg.id" :value="mg.id">{{ mg.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm text-slate-400 mb-2">Ekipman</label>
                <select v-model="form.equipment_type_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500 transition-all">
                  <option :value="null">Seçiniz</option>
                  <option v-for="eq in equipmentTypes" :key="eq.id" :value="eq.id">{{ eq.name }}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm text-slate-400 mb-2">Açıklama</label>
              <textarea v-model="form.description" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500 transition-all"></textarea>
            </div>
            <div>
              <label class="block text-sm text-slate-400 mb-2">Görsel</label>
              <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden">
                  <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover">
                  <svg v-else class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
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
            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" id="isActive" class="w-5 h-5 rounded border-slate-700 bg-slate-800 text-red-500 focus:ring-red-500">
              <label for="isActive" class="ml-3 text-sm text-slate-400">Aktif</label>
            </div>
            <div class="pt-4 flex gap-4">
              <button type="button" @click="closeModal" class="flex-1 py-4 bg-slate-800 text-slate-400 rounded-2xl font-bold">İptal</button>
              <button type="submit" class="flex-1 py-4 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-2xl font-bold shadow-lg">Kaydet</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'admin',
  layout: false
})

interface Exercise {
  id: number
  name: string
  description?: string
  muscle_group_id?: number
  equipment_type_id?: number
  difficulty?: string
  image?: string
  video_url?: string
  is_active?: boolean
}

interface MuscleGroup {
  id: number
  name: string
}

interface EquipmentType {
  id: number
  name: string
}

const api = useApi()
const exercises = ref([])
const muscleGroups = ref([])
const equipmentTypes = ref([])
const loading = ref(true)
const isModalOpen = ref(false)
const isEditing = ref(false)
const currentId = ref(null)
const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref('')
const imageFile = ref<File | null>(null)

const form = ref({
  name: '',
  muscle_group_id: null as number | null,
  equipment_type_id: null as number | null,
  description: '',
  is_active: true
})

const fetchExercises = async () => {
  try {
    const res = await api.get('/exercises')
    exercises.value = res.data.data
  } catch (e: any) {
    console.error('Egzersizler yüklenemedi', e?.response?.data ?? e)
  } finally {
    loading.value = false
  }
}

const fetchMuscleGroups = async () => {
  try {
    const res = await api.get('/muscle-groups')
    muscleGroups.value = res.data.data
  } catch (e) {
    console.error('Kas grupları yüklenemedi')
  }
}

const fetchEquipmentTypes = async () => {
  try {
    const res = await api.get('/equipment-types')
    equipmentTypes.value = res.data.data
  } catch (e) {
    console.error('Ekipman türleri yüklenemedi')
  }
}

const getMuscleGroupName = (id: number | null) => {
  if (!id) return 'Belirsiz'
  const mg = muscleGroups.value.find((m: MuscleGroup) => m.id === id)
  return mg ? mg.name : 'Belirsiz'
}

const getEquipmentName = (id: number | null) => {
  if (!id) return 'Belirsiz'
  const eq = equipmentTypes.value.find((e: EquipmentType) => e.id === id)
  return eq ? eq.name : 'Belirsiz'
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { name: '', muscle_group_id: null, equipment_type_id: null, description: '', is_active: true }
  imagePreview.value = ''
  imageFile.value = null
  isModalOpen.value = true
}

const openEditModal = (ex: Exercise) => {
  isEditing.value = true
  currentId.value = ex.id
  form.value = {
    name: ex.name,
    muscle_group_id: ex.muscle_group_id,
    equipment_type_id: ex.equipment_type_id,
    description: ex.description || '',
    is_active: ex.is_active ?? true
  }
  imagePreview.value = ex.image ? `/storage/${ex.image}` : ''
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

const saveExercise = async () => {
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('description', form.value.description)
    formData.append('is_active', form.value.is_active ? '1' : '0')
    if (form.value.muscle_group_id) {
      formData.append('muscle_group_id', form.value.muscle_group_id.toString())
    }
    if (form.value.equipment_type_id) {
      formData.append('equipment_type_id', form.value.equipment_type_id.toString())
    }
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    if (isEditing.value) {
      formData.append('_method', 'PUT')
      await api.post(`/exercises/${currentId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await api.post('/exercises', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    await fetchExercises()
    closeModal()
  } catch (e) {
    alert('Hata oluştu')
  }
}

const deleteExercise = async (id: number) => {
  if (confirm('Bu egzersizi silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/exercises/${id}`)
      await fetchExercises()
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

onMounted(() => {
  fetchExercises()
  fetchMuscleGroups()
  fetchEquipmentTypes()
})
</script>
