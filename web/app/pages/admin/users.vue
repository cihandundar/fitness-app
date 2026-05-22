<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Kullanıcı Yönetimi</h1>
        <div class="flex items-center space-x-3">
           <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 text-[10px] font-bold border border-red-500/20 uppercase tracking-widest">Admin Panel</span>
        </div>
      </header>

      <div class="p-6 lg:p-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-slate-400 text-sm mb-2">Toplam Kullanıcı</p>
            <p class="text-3xl font-bold text-white">{{ users.length }}</p>
          </div>
          <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-slate-400 text-sm mb-2">Admin</p>
            <p class="text-3xl font-bold text-amber-400">{{ usersByRole('admin').length }}</p>
          </div>
          <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-slate-400 text-sm mb-2">Eğitmen</p>
            <p class="text-3xl font-bold text-purple-400">{{ usersByRole('trainer').length }}</p>
          </div>
          <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-slate-400 text-sm mb-2">Üye</p>
            <p class="text-3xl font-bold text-blue-400">{{ usersByRole('user').length }}</p>
          </div>
        </div>

        <!-- Users Table -->
        <div class="bg-slate-900/50 border border-slate-800/50 rounded-2xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-slate-800/50 bg-slate-900/80">
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm">Kullanıcı</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm">Rol</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm">Durum</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm">Kayıt Tarihi</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm text-right">İşlemler</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/50">
                <tr v-for="user in users" :key="user.id" class="hover:bg-slate-800/30 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="w-10 h-10 rounded-full bg-slate-800 mr-3 flex items-center justify-center overflow-hidden border border-slate-700">
                        <img v-if="user.avatar" :src="user.avatar" class="w-full h-full object-cover">
                        <svg v-else class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                      </div>
                      <div>
                        <div class="text-white font-medium">{{ user.name }}</div>
                        <div class="text-xs text-slate-500">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="roleClass(user.role)" class="px-2.5 py-1 rounded-lg text-xs font-semibold uppercase">
                      {{ roleLabel(user.role) }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <span :class="user.is_active ? 'bg-green-400' : 'bg-red-400'" class="w-2 h-2 rounded-full mr-2"></span>
                      <span class="text-sm text-slate-300">{{ user.is_active ? 'Aktif' : 'Pasif' }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-slate-400 text-sm">
                    {{ new Date(user.created_at).toLocaleDateString('tr-TR') }}
                  </td>
                  <td class="px-6 py-4 text-right space-x-2">
                    <select @change="updateRole(user, $event)" class="bg-slate-800 border border-slate-700 rounded-lg px-2 py-1 text-xs text-white outline-none">
                      <option :selected="user.role === 'user'" value="user">Üye</option>
                      <option :selected="user.role === 'trainer'" value="trainer">Eğitmen</option>
                      <option :selected="user.role === 'admin'" value="admin">Admin</option>
                    </select>
                    <button @click="deleteUser(user.id)" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
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

// Type definitions
interface User {
  id: number
  name: string
  email: string
  role: 'user' | 'trainer' | 'admin' | 'super_admin'
  is_active: boolean
  created_at: string
}

const api = useApi()
const users = ref<User[]>([])

const fetchUsers = async () => {
  try {
    const res = await api.get('/users')
    users.value = res.data.data
  } catch (e: Error | unknown) {
    const errorMessage = e instanceof Error ? e.message : 'Kullanıcılar yüklenemedi'
    console.error(errorMessage)
  }
}

const usersByRole = (role: string) => {
  return users.value.filter((u: User) => u.role === role)
}

const roleClass = (role: string) => {
  switch (role) {
    case 'super_admin': return 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
    case 'admin': return 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
    case 'trainer': return 'bg-purple-500/10 text-purple-400 border border-purple-500/20'
    case 'user': return 'bg-blue-500/10 text-blue-400 border border-blue-500/20'
    default: return 'bg-slate-500/10 text-slate-400'
  }
}

const roleLabel = (role: string) => {
  switch (role) {
    case 'super_admin': return 'Süper Admin'
    case 'admin': return 'Admin'
    case 'trainer': return 'Eğitmen'
    case 'user': return 'Üye'
    default: return role
  }
}

const updateRole = async (user: User, event: Event) => {
  const target = event.target as HTMLSelectElement
  const newRole = target.value
  try {
    await api.put(`/users/${user.id}`, { role: newRole })
    await fetchUsers()
  } catch (e: Error | unknown) {
    const errorMessage = e instanceof Error ? e.message : 'Rol güncellenemedi'
    alert(errorMessage)
  }
}

const deleteUser = async (id: number) => {
  if (confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')) {
    try {
      await api.delete(`/users/${id}`)
      await fetchUsers()
    } catch (e) {
      alert('Silinemedi')
    }
  }
}

onMounted(fetchUsers)
</script>
