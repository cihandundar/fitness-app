<template>
  <aside
    class="hidden lg:flex lg:flex-col fixed inset-y-0 left-0 w-72 bg-slate-900/95 backdrop-blur-xl border-r border-slate-800/50 z-50"
  >
    <!-- Logo -->
    <div class="flex items-center h-16 px-6 border-b border-slate-800/50">
      <NuxtLink to="/dashboard" class="flex items-center space-x-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20.57 14.86L22 13.43L20.57 12L17 15.57L8.43 7L12 3.43L10.57 2L9.14 3.43L7.71 2L5.57 4.14L4.14 2.71L2.71 4.14L4.14 5.57L2 7.71L3.43 9.14L2 10.57L3.43 12L7 8.43L15.57 17L12 20.57L13.43 22L14.86 20.57L16.29 22L18.43 19.86L19.86 21.29L21.29 19.86L19.86 18.43L22 16.29L20.57 14.86Z"/>
          </svg>
        </div>
        <span class="text-lg font-bold bg-gradient-to-r from-violet-400 to-purple-400 bg-clip-text text-transparent">FitApp</span>
      </NuxtLink>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
      <!-- User Info Card -->
      <div class="mb-6 p-4 rounded-2xl bg-gradient-to-br from-violet-500/10 to-purple-500/10 border border-violet-500/20">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
            <span class="text-white font-semibold">{{ userInitials }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-white font-medium truncate">{{ authStore.user?.name }}</p>
            <p class="text-sm text-slate-400 capitalize">{{ authStore.user?.role }}</p>
          </div>
        </div>
      </div>

      <!-- Main Menu -->
      <div class="space-y-1">
        <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Ana Menü</p>

        <NuxtLink
          to="/dashboard"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/dashboard') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          <span class="ml-3">Dashboard</span>
        </NuxtLink>

        <NuxtLink
          to="/programs"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/programs') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <span class="ml-3">Programlar</span>
        </NuxtLink>

        <NuxtLink
          to="/exercises"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/exercises') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
          <span class="ml-3">Egzersizler</span>
        </NuxtLink>

        <NuxtLink
          to="/progress"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/progress') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          <span class="ml-3">İlerleme</span>
        </NuxtLink>

        <NuxtLink
          v-if="authStore.user?.role === 'trainer' || authStore.user?.role === 'admin' || authStore.user?.role === 'super_admin'"
          to="/trainer"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all text-purple-400 hover:bg-purple-500/10 border border-purple-500/20 mt-4"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <span class="ml-3 font-semibold">Eğitmen Paneli</span>
        </NuxtLink>

        <!-- Admin Links -->
        <div v-if="authStore.user?.role === 'admin' || authStore.user?.role === 'super_admin'" class="pt-4 mt-4 border-t border-slate-800/50">
          <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Yönetim</p>
          
          <NuxtLink to="/" class="flex items-center px-3 py-2.5 rounded-xl text-emerald-400 hover:bg-emerald-500/10 transition-all mb-2 border border-emerald-500/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            <span class="ml-3 font-bold">Websitesini Görüntüle</span>
          </NuxtLink>

          <NuxtLink to="/admin" class="flex items-center px-3 py-2.5 rounded-xl text-red-400 hover:bg-red-500/10 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            <span class="ml-3 font-bold">Admin Panel</span>
          </NuxtLink>

          <NuxtLink to="/admin/users" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span class="ml-3">Kullanıcılar</span>
          </NuxtLink>

          <NuxtLink to="/admin/payments" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-2 2a2 2 0 110-4 2 2 0 010 4zM11 3a2 2 0 10-4 0m12 18H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z"/></svg>
            <span class="ml-3">Ödemeler</span>
          </NuxtLink>

          <NuxtLink to="/admin/programs" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="ml-3">Programlar</span>
          </NuxtLink>

          <NuxtLink to="/admin/exercises" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span class="ml-3">Egzersizler</span>
          </NuxtLink>

          <NuxtLink to="/admin/muscle-groups" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <span class="ml-3">Kas Grupları</span>
          </NuxtLink>

          <NuxtLink to="/admin/equipment" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span class="ml-3">Ekipmanlar</span>
          </NuxtLink>

          <NuxtLink to="/admin/memberships" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/></svg>
            <span class="ml-3">Üyelikler</span>
          </NuxtLink>

          <NuxtLink to="/admin/branches" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span class="ml-3">Branşlar</span>
          </NuxtLink>

          <NuxtLink to="/admin/settings" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="ml-3">Sistem Ayarları</span>
          </NuxtLink>
        </div>
      </div>

      <!-- Settings Menu -->
      <div class="space-y-1 mt-6">
        <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Ayarlar</p>

        <NuxtLink
          to="/profile"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/profile') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <span class="ml-3">Profil</span>
        </NuxtLink>

        <NuxtLink
          to="/settings"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/settings') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span class="ml-3">Ayarlar</span>
        </NuxtLink>
      </div>

      <!-- Logout -->
      <div class="mt-6 pt-6 border-t border-slate-800/50">
        <button
          @click="handleLogout"
          class="flex items-center w-full px-3 py-2.5 rounded-xl text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          <span class="ml-3">Çıkış Yap</span>
        </button>
      </div>
    </nav>
  </aside>

  <!-- Mobile Sidebar -->
  <aside
    v-if="isOpen"
    class="lg:hidden fixed inset-y-0 left-0 z-50 w-72 bg-slate-900/95 backdrop-blur-xl border-r border-slate-800/50 pointer-events-auto"
  >
    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-slate-800/50">
      <NuxtLink to="/dashboard" class="flex items-center space-x-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20.57 14.86L22 13.43L20.57 12L17 15.57L8.43 7L12 3.43L10.57 2L9.14 3.43L7.71 2L5.57 4.14L4.14 2.71L2.71 4.14L4.14 5.57L2 7.71L3.43 9.14L2 10.57L3.43 12L7 8.43L15.57 17L12 20.57L13.43 22L14.86 20.57L16.29 22L18.43 19.86L19.86 21.29L21.29 19.86L19.86 18.43L22 16.29L20.57 14.86Z"/>
          </svg>
        </div>
        <span class="text-lg font-bold bg-gradient-to-r from-violet-400 to-purple-400 bg-clip-text text-transparent">FitApp</span>
      </NuxtLink>
      <button @click="closeSidebar" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/50">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100vh-64px)]">
      <!-- User Info Card -->
      <div class="mb-6 p-4 rounded-2xl bg-gradient-to-br from-violet-500/10 to-purple-500/10 border border-violet-500/20">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
            <span class="text-white font-semibold">{{ userInitials }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-white font-medium truncate">{{ authStore.user?.name }}</p>
            <p class="text-sm text-slate-400 capitalize">{{ authStore.user?.role }}</p>
          </div>
        </div>
      </div>

      <!-- Main Menu -->
      <div class="space-y-1">
        <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Ana Menü</p>

        <NuxtLink
          to="/dashboard"
          @click="closeSidebar"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/dashboard') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          <span class="ml-3">Dashboard</span>
        </NuxtLink>

        <NuxtLink
          to="/programs"
          @click="closeSidebar"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/programs') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <span class="ml-3">Programlar</span>
        </NuxtLink>

        <NuxtLink
          to="/exercises"
          @click="closeSidebar"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/exercises') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
          <span class="ml-3">Egzersizler</span>
        </NuxtLink>

        <NuxtLink
          to="/progress"
          @click="closeSidebar"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/progress') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          <span class="ml-3">İlerleme</span>
        </NuxtLink>

        <!-- Admin Links Mobile -->
        <div v-if="authStore.user?.role === 'admin' || authStore.user?.role === 'super_admin'" class="pt-4 mt-4 border-t border-slate-800/50">
          <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Yönetim</p>
          
          <NuxtLink to="/" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-emerald-400 hover:bg-emerald-500/10 transition-all mb-2 border border-emerald-500/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            <span class="ml-3 font-bold">Websitesini Görüntüle</span>
          </NuxtLink>

          <NuxtLink to="/admin" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-red-400 hover:bg-red-500/10 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            <span class="ml-3 font-bold">Admin Panel</span>
          </NuxtLink>

          <NuxtLink to="/admin/users" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span class="ml-3">Kullanıcılar</span>
          </NuxtLink>

          <NuxtLink to="/admin/payments" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-2 2a2 2 0 110-4 2 2 0 010 4zM11 3a2 2 0 10-4 0m12 18H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z"/></svg>
            <span class="ml-3">Ödemeler</span>
          </NuxtLink>

          <NuxtLink to="/admin/programs" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="ml-3">Programlar</span>
          </NuxtLink>

          <NuxtLink to="/admin/exercises" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span class="ml-3">Egzersizler</span>
          </NuxtLink>

          <NuxtLink to="/admin/muscle-groups" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <span class="ml-3">Kas Grupları</span>
          </NuxtLink>

          <NuxtLink to="/admin/equipment" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span class="ml-3">Ekipmanlar</span>
          </NuxtLink>

          <NuxtLink to="/admin/memberships" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/></svg>
            <span class="ml-3">Üyelikler</span>
          </NuxtLink>

          <NuxtLink to="/admin/branches" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span class="ml-3">Branşlar</span>
          </NuxtLink>

          <NuxtLink to="/admin/settings" @click="closeSidebar" class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="ml-3">Sistem Ayarları</span>
          </NuxtLink>
        </div>
      </div>

      <!-- Settings Menu -->
      <div class="space-y-1 mt-6">
        <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Ayarlar</p>

        <NuxtLink
          to="/profile"
          @click="closeSidebar"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/profile') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <span class="ml-3">Profil</span>
        </NuxtLink>

        <NuxtLink
          to="/settings"
          @click="closeSidebar"
          class="flex items-center px-3 py-2.5 rounded-xl transition-all"
          :class="isActive('/settings') ? 'bg-violet-500/20 text-violet-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/50'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span class="ml-3">Ayarlar</span>
        </NuxtLink>
      </div>

      <!-- Logout -->
      <div class="mt-6 pt-6 border-t border-slate-800/50">
        <button
          @click="handleLogout"
          class="flex items-center w-full px-3 py-2.5 rounded-xl text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          <span class="ml-3">Çıkış Yap</span>
        </button>
      </div>
    </nav>
  </aside>

  <!-- Overlay (Mobile) -->
  <div
    v-if="isOpen"
    @click="closeSidebar"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden pointer-events-auto"
  ></div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  isOpen?: boolean
}>(), {
  isOpen: false
})

const emit = defineEmits<{
  close: []
}>()

const authStore = useAuthStore()
const route = useRoute()

const userInitials = computed(() => {
  if (!authStore.user?.name) return 'U'
  return authStore.user.name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2)
})

const isActive = (path: string) => {
  return route.path === path || route.path.startsWith(path + '/')
}

const closeSidebar = () => {
  emit('close')
}

const handleLogout = async () => {
  try {
    await authStore.logout()
  } finally {
    closeSidebar()
    await navigateTo('/login')
  }
}
</script>
