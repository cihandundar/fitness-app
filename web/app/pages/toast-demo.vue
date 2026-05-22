<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center px-4">
    <div class="w-full max-w-2xl">
      <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-white mb-2">Toast Bildirimleri</h1>
        <p class="text-slate-400">Bildirim sistemini test et</p>
      </div>

      <div class="bg-slate-900/50 backdrop-blur-xl rounded-2xl border border-slate-800/50 p-8">
        <div class="grid grid-cols-2 gap-3">
          <button
            @click="toast.success('İşlem tamamlandı')"
            class="p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 font-medium hover:bg-emerald-500/30 transition-all text-sm"
          >
            Başarılı
          </button>

          <button
            @click="toast.error('Bir hata oluştu')"
            class="p-3 rounded-xl bg-red-500/20 border border-red-500/30 text-red-400 font-medium hover:bg-red-500/30 transition-all text-sm"
          >
            Hata
          </button>

          <button
            @click="toast.warning('Lütfen kontrol edin')"
            class="p-3 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 font-medium hover:bg-amber-500/30 transition-all text-sm"
          >
            Uyarı
          </button>

          <button
            @click="toast.info('Bilgi mesajı')"
            class="p-3 rounded-xl bg-blue-500/20 border border-blue-500/30 text-blue-400 font-medium hover:bg-blue-500/30 transition-all text-sm"
          >
            Bilgi
          </button>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-800">
          <h3 class="text-white font-medium mb-3 text-sm">Süre Seçenekleri</h3>
          <div class="flex flex-wrap gap-2">
            <button
              @click="toast.success('2 saniye', '', 2000)"
              class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs transition-all"
            >
              2sn
            </button>
            <button
              @click="toast.success('5 saniye', '', 5000)"
              class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs transition-all"
            >
              5sn
            </button>
            <button
              @click="toast.success('10 saniye', '', 10000)"
              class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs transition-all"
            >
              10sn
            </button>
            <button
              @click="toast.success('Sonsuz', '', 0)"
              class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs transition-all"
            >
              Sonsuz
            </button>
          </div>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-800">
          <button
            @click="useToastStore().clear()"
            class="w-full py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-sm transition-all"
          >
            Temizle
          </button>
        </div>
      </div>

      <p class="text-center text-slate-600 text-sm mt-8">
        <NuxtLink to="/" class="text-slate-500 hover:text-slate-400">Ana sayfaya dön</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
const toast = useToast()
</script>
