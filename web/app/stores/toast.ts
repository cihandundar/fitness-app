import { defineStore } from 'pinia'

export type ToastType = 'success' | 'error' | 'warning' | 'info'

export interface Toast {
  id: number
  type: ToastType
  title?: string
  message: string
  duration: number
  remaining: number
}

export const useToastStore = defineStore('toast', {
  state: () => ({
    toasts: [] as Toast[],
    nextId: 1,
  }),

  actions: {
    add(type: ToastType, message: string, title?: string, duration = 5000) {
      const id = this.nextId++
      const toast: Toast = {
        id,
        type,
        title,
        message,
        duration,
        remaining: duration,
      }

      this.toasts.push(toast)

      // Timer başlat
      if (duration > 0) {
        const startTime = Date.now()
        const interval = setInterval(() => {
          const elapsed = Date.now() - startTime
          toast.remaining = Math.max(0, duration - elapsed)

          if (toast.remaining <= 0) {
            clearInterval(interval)
            this.remove(id)
          }
        }, 100)
      }

      return id
    },

    remove(id: number) {
      const index = this.toasts.findIndex(t => t.id === id)
      if (index > -1) {
        this.toasts.splice(index, 1)
      }
    },

    clear() {
      this.toasts = []
    },

    // Convenience methods
    success(message: string, title?: string, duration?: number) {
      return this.add('success', message, title, duration)
    },

    error(message: string, title?: string, duration?: number) {
      return this.add('error', message, title, duration)
    },

    warning(message: string, title?: string, duration?: number) {
      return this.add('warning', message, title, duration)
    },

    info(message: string, title?: string, duration?: number) {
      return this.add('info', message, title, duration)
    },
  },
})
