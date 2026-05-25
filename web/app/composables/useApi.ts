import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    'Accept': 'application/json',
  },
})

// her istekte token varsa header'a ekle
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export const useApi = () => {
  return {
    get: (url: string) => api.get(url),
    post: (url: string, data?: any, config?: any) => {
      // FormData ise Content-Type header'ını gönderme (axios otomatik ayarlar)
      if (data instanceof FormData) {
        return api.post(url, data, {
          ...config,
          headers: {
            ...(config?.headers || {}),
            'Content-Type': undefined // FormData için undefined bırak
          }
        })
      }
      return api.post(url, data, config)
    },
    put: (url: string, data?: any) => api.put(url, data),
    delete: (url: string) => api.delete(url),
  }
}

export default api