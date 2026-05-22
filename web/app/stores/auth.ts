import { defineStore } from 'pinia'
import { useApi } from '~/composables/useApi'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as any,
    token: null as string | null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
  },

  actions: {
    async register(name: string, email: string, password: string, password_confirmation: string) {
      const api = useApi()
      const response = await api.post('/auth/register', {
        name,
        email,
        password,
        password_confirmation,
      })
      this.token = response.data.token
      this.user = response.data.user
      localStorage.setItem('token', response.data.token)
    },

    async login(email: string, password: string) {
      const api = useApi()
      const response = await api.post('/auth/login', {
        email,
        password,
      })
      this.token = response.data.token
      this.user = response.data.user
      localStorage.setItem('token', response.data.token)
    },

    async logout() {
      const api = useApi()
      try {
        await api.post('/auth/logout')
      } catch (e) {
        console.error('Logout API error:', e)
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('token')
      }
    },

    async fetchUser() {
      const api = useApi()
      const response = await api.get('/auth/me')
      this.user = response.data
    },

    init() {
      const token = localStorage.getItem('token')
      if (token) {
        this.token = token
        this.fetchUser()
      }
    },
  },
})