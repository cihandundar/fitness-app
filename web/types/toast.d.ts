// Toast composable için global tip tanımlamaları
declare module '#app' {
  interface NuxtApp {
    $toast: ReturnType<typeof import '~/composables/useToast')['useToast']>
  }
}

export {}
