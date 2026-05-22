<template>
  <div class="min-h-screen bg-slate-950">
    <AppSidebar />

    <!-- Main Content -->
    <main class="min-h-screen lg:ml-72">
      <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50 h-16 flex items-center px-6 lg:px-8 justify-between w-full">
        <h1 class="text-xl font-semibold text-white">Ödeme Takibi</h1>
        <div class="flex items-center space-x-2">
           <span class="text-slate-500 text-sm">Toplam Tahsilat:</span>
           <span class="text-emerald-400 font-bold">{{ totalRevenue }} TL</span>
        </div>
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
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider">Kullanıcı</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider">Miktar</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider">Yöntem</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider">Tarih</th>
                  <th class="px-6 py-4 text-slate-400 font-medium text-sm uppercase tracking-wider">Durum</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/50">
                <tr v-for="payment in payments" :key="payment.id" class="hover:bg-slate-800/30 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-white font-medium">{{ payment.user?.name }}</div>
                    <div class="text-xs text-slate-500">{{ payment.user?.email }}</div>
                  </td>
                  <td class="px-6 py-4 text-white font-bold">{{ payment.amount }} {{ payment.currency }}</td>
                  <td class="px-6 py-4 text-slate-400 text-sm capitalize">{{ payment.payment_method }}</td>
                  <td class="px-6 py-4 text-slate-400 text-sm">{{ new Date(payment.created_at).toLocaleDateString('tr-TR') }}</td>
                  <td class="px-6 py-4">
                    <span :class="statusClass(payment.status)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest border">
                      {{ payment.status }}
                    </span>
                  </td>
                </tr>
                <tr v-if="payments.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">Ödeme kaydı bulunamadı.</td>
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
definePageMeta({
  middleware: 'admin',
  layout: false
})

const api = useApi()
const payments = ref([])
const loading = ref(true)

const fetchPayments = async () => {
  try {
    const res = await api.get('/payments')
    payments.value = res.data.data
  } catch (e) {
    console.error('Ödemeler yüklenemedi')
  } finally {
    loading.value = false
  }
}

const totalRevenue = computed(() => {
  return payments.value
    .filter(p => p.status === 'completed')
    .reduce((acc, p) => acc + parseFloat(p.amount), 0)
    .toFixed(2)
})

const statusClass = (status: string) => {
  switch (status) {
    case 'completed': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
    case 'pending': return 'bg-amber-500/10 text-amber-400 border-amber-500/20'
    case 'failed': return 'bg-rose-500/10 text-rose-400 border-rose-500/20'
    default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20'
  }
}

onMounted(fetchPayments)
</script>
