<template>
  <footer class="bg-slate-900/50 border-t border-slate-800/50 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Brand -->
        <div class="col-span-1 md:col-span-2">
          <div class="flex items-center space-x-2 mb-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.57 14.86L22 13.43L20.57 12L17 15.57L8.43 7L12 3.43L10.57 2L9.14 3.43L7.71 2L5.57 4.14L4.14 2.71L2.71 4.14L4.14 5.57L2 7.71L3.43 9.14L2 10.57L3.43 12L7 8.43L15.57 17L12 20.57L13.43 22L14.86 20.57L16.29 22L18.43 19.86L19.86 21.29L21.29 19.86L19.86 18.43L22 16.29L20.57 14.86Z"/>
              </svg>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-violet-400 to-purple-400 bg-clip-text text-transparent">FitApp</span>
          </div>
          <p class="text-slate-400 max-w-md leading-relaxed">
            Fitness hedeflerinize ulaşmanız için kişiselleştirilmiş antrenman programları ve egzersiz rehberi.
          </p>
        </div>

        <!-- Links -->
        <div>
          <h3 class="text-sm font-semibold text-slate-200 uppercase tracking-wider mb-4">Hızlı Linkler</h3>
          <ul class="space-y-3">
            <li><NuxtLink to="/programs" class="text-slate-400 hover:text-white transition-colors">Programlar</NuxtLink></li>
            <li><NuxtLink to="/exercises" class="text-slate-400 hover:text-white transition-colors">Egzersizler</NuxtLink></li>
            <li><NuxtLink to="/about" class="text-slate-400 hover:text-white transition-colors">Hakkımızda</NuxtLink></li>
          </ul>
        </div>

        <!-- Legal -->
        <div>
          <h3 class="text-sm font-semibold text-slate-200 uppercase tracking-wider mb-4">Yasal</h3>
          <ul class="space-y-3">
            <li><NuxtLink to="/privacy" class="text-slate-400 hover:text-white transition-colors">Gizlilik Politikası</NuxtLink></li>
            <li><NuxtLink to="/terms" class="text-slate-400 hover:text-white transition-colors">Kullanım Koşulları</NuxtLink></li>
            <li><NuxtLink to="/contact" class="text-slate-400 hover:text-white transition-colors">İletişim</NuxtLink></li>
          </ul>
        </div>
      </div>

      <!-- Bottom -->
      <div class="border-t border-slate-800/50 mt-8 pt-8 text-center">
        <p class="text-slate-500 text-sm">&copy; {{ new Date().getFullYear() }} FitApp. Tüm hakları saklıdır.</p>
      </div>
    </div>
  </footer>
</template>
