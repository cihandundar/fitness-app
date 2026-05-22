import { beforeEach, vi } from 'vitest'

// localStorage mock - stores values in memory
const store = new Map<string, string>()

const localStorageMock = {
  getItem: (key: string) => store.get(key) ?? null,
  setItem: (key: string, value: string) => store.set(key, value),
  removeItem: (key: string) => store.delete(key),
  clear: () => store.clear(),
  length: 0,
  key: (index: number) => null,
}

Object.defineProperty(global, 'localStorage', {
  value: localStorageMock,
  writable: true,
})

// Her testten önce localStorage'ı temizle
beforeEach(() => {
  store.clear()
})

// Global console mock - error logları görmek için
global.console = {
  ...console,
  error: vi.fn(),
  warn: vi.fn(),
}

// Nuxt context mock
global.__NUXT__ = {
  context: {
    pinia: {
      instances: {},
    },
  },
}
