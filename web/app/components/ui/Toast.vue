<template>
  <Teleport to="body">
    <TransitionGroup
      tag="div"
      name="toast"
      class="fixed top-4 right-4 z-50 flex flex-col gap-3 pointer-events-none"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'pointer-events-auto min-w-[300px] max-w-md p-4 rounded-xl border shadow-xl backdrop-blur-xl flex items-start gap-3 relative',
          toastClasses[toast.type]
        ]"
      >
        <!-- Icon -->
        <div class="flex-shrink-0 mt-0.5">
          <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <svg v-else-if="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <svg v-else-if="toast.type === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium">{{ toast.message }}</p>
        </div>

        <!-- Close Button -->
        <button
          @click="remove(toast.id)"
          class="flex-shrink-0 p-0.5 rounded-lg hover:bg-black/20 transition-colors opacity-60 hover:opacity-100"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Progress Bar -->
        <div
          v-if="toast.duration > 0"
          class="absolute bottom-0 left-0 h-0.5 bg-current opacity-20 rounded-b-xl transition-all"
          :style="{ width: `${(toast.remaining / toast.duration) * 100}%` }"
        />
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup lang="ts">
const toastStore = useToastStore()

const toasts = computed(() => toastStore.toasts)

const toastClasses = {
  success: 'bg-emerald-500 border-emerald-500/30 text-white',
  error: 'bg-red-500 border-red-500/30 text-white',
  warning: 'bg-amber-500 border-amber-500/30 text-white',
  info: 'bg-blue-500 border-blue-500/30 text-white',
}

const remove = (id: number) => {
  toastStore.remove(id)
}
</script>

<style scoped>
/* Toast Animasyonları */
.toast-enter-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-leave-active {
  transition: all 0.2s ease-in;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(20px) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(10px) scale(0.95);
}

.toast-move {
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
