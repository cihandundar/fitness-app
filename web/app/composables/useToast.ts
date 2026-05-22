import { useToastStore } from '~/stores/toast'

export const useToast = () => {
  const toastStore = useToastStore()

  return {
    success: (message: string, duration = 4000) =>
      toastStore.add('success', message, '', duration),

    error: (message: string, duration = 5000) =>
      toastStore.add('error', message, '', duration),

    warning: (message: string, duration = 4000) =>
      toastStore.add('warning', message, '', duration),

    info: (message: string, duration = 4000) =>
      toastStore.add('info', message, '', duration),
  }
}
