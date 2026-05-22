import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'

// Use vi.hoisted() to create variables before mock hoisting
const { mockApiGet, mockApiPost, mockApiPut, mockApiDelete, mockApiPatch } = vi.hoisted(() => ({
  mockApiGet: vi.fn(),
  mockApiPost: vi.fn(),
  mockApiPut: vi.fn(),
  mockApiDelete: vi.fn(),
  mockApiPatch: vi.fn(),
}))

// Mock axios - use hoisted variables
vi.mock('axios', () => ({
  default: {
    create: vi.fn(() => ({
      get: mockApiGet,
      post: mockApiPost,
      put: mockApiPut,
      delete: mockApiDelete,
      patch: mockApiPatch,
      interceptors: {
        request: { use: vi.fn() },
        response: { use: vi.fn() },
      },
    })),
  },
}))

// Import after mocking
import { useAuthStore } from '../../app/stores/auth'

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    localStorage.clear()

    // Reset all mock implementations
    mockApiGet.mockReset()
    mockApiPost.mockReset()
    mockApiPut.mockReset()
    mockApiDelete.mockReset()
    mockApiPatch.mockReset()
  })

  // ==================== Initial State ====================

  it('has correct initial state', () => {
    const store = useAuthStore()

    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
  })

  it('isLoggedIn returns false when token is null', () => {
    const store = useAuthStore()

    expect(store.isLoggedIn).toBe(false)
  })

  it('isAdmin returns false when user is null', () => {
    const store = useAuthStore()

    expect(store.isAdmin).toBe(false)
  })

  // ==================== Register ====================

  it('can register user successfully', async () => {
    const store = useAuthStore()

    const mockResponse = {
      data: {
        token: 'test-token-123',
        user: {
          id: 1,
          name: 'Test User',
          email: 'test@example.com',
          role: 'user',
        },
      },
    }

    mockApiPost.mockResolvedValue(mockResponse)

    await store.register('Test User', 'test@example.com', 'password123', 'password123')

    expect(mockApiPost).toHaveBeenCalledWith('/auth/register', {
      name: 'Test User',
      email: 'test@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    })

    expect(store.token).toBe('test-token-123')
    expect(store.user).toEqual(mockResponse.data.user)
    expect(localStorage.getItem('token')).toBe('test-token-123')
  })

  it('handles register error', async () => {
    const store = useAuthStore()

    const mockError = new Error('Registration failed')
    mockApiPost.mockRejectedValue(mockError)

    await expect(
      store.register('Test User', 'test@example.com', 'password123', 'password123')
    ).rejects.toThrow('Registration failed')
  })

  // ==================== Login ====================

  it('can login user successfully', async () => {
    const store = useAuthStore()

    const mockResponse = {
      data: {
        token: 'login-token-456',
        user: {
          id: 1,
          name: 'Test User',
          email: 'test@example.com',
          role: 'user',
        },
      },
    }

    mockApiPost.mockResolvedValue(mockResponse)

    await store.login('test@example.com', 'password123')

    expect(mockApiPost).toHaveBeenCalledWith('/auth/login', {
      email: 'test@example.com',
      password: 'password123',
    })

    expect(store.token).toBe('login-token-456')
    expect(store.user).toEqual(mockResponse.data.user)
    expect(localStorage.getItem('token')).toBe('login-token-456')
  })

  it('handles login error', async () => {
    const store = useAuthStore()

    const mockError = new Error('Invalid credentials')
    mockApiPost.mockRejectedValue(mockError)

    await expect(
      store.login('test@example.com', 'wrongpassword')
    ).rejects.toThrow('Invalid credentials')
  })

  it('isLoggedIn returns true after login', async () => {
    const store = useAuthStore()

    const mockResponse = {
      data: {
        token: 'login-token-456',
        user: {
          id: 1,
          name: 'Test User',
          email: 'test@example.com',
          role: 'user',
        },
      },
    }

    mockApiPost.mockResolvedValue(mockResponse)

    await store.login('test@example.com', 'password123')

    expect(store.isLoggedIn).toBe(true)
  })

  it('isAdmin returns true for admin user', async () => {
    const store = useAuthStore()

    const mockResponse = {
      data: {
        token: 'admin-token-789',
        user: {
          id: 1,
          name: 'Admin User',
          email: 'admin@example.com',
          role: 'admin',
        },
      },
    }

    mockApiPost.mockResolvedValue(mockResponse)

    await store.login('admin@example.com', 'admin123')

    expect(store.isAdmin).toBe(true)
  })

  it('isAdmin returns false for regular user', async () => {
    const store = useAuthStore()

    const mockResponse = {
      data: {
        token: 'user-token-123',
        user: {
          id: 1,
          name: 'Regular User',
          email: 'user@example.com',
          role: 'user',
        },
      },
    }

    mockApiPost.mockResolvedValue(mockResponse)

    await store.login('user@example.com', 'user123')

    expect(store.isAdmin).toBe(false)
  })

  // ==================== Logout ====================

  it('can logout user successfully', async () => {
    const store = useAuthStore()

    // Önce login ol
    const loginResponse = {
      data: {
        token: 'test-token',
        user: { id: 1, name: 'Test User', email: 'test@example.com', role: 'user' },
      },
    }
    mockApiPost.mockResolvedValue(loginResponse)

    await store.login('test@example.com', 'password123')

    // Sonra logout
    const logoutResponse = { data: { message: 'Logged out' } }
    mockApiPost.mockResolvedValue(logoutResponse)

    await store.logout()

    expect(mockApiPost).toHaveBeenCalledWith('/auth/logout')
    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(localStorage.getItem('token')).toBeNull()
  })

  it('clears state even if logout API fails', async () => {
    const store = useAuthStore()

    // Login
    const loginResponse = {
      data: {
        token: 'test-token',
        user: { id: 1, name: 'Test User', email: 'test@example.com', role: 'user' },
      },
    }
    mockApiPost.mockResolvedValue(loginResponse)

    await store.login('test@example.com', 'password123')

    // Logout API hatası
    mockApiPost.mockRejectedValueOnce(new Error('Network error'))

    await store.logout()

    // State yine de temizlenmeli
    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(localStorage.getItem('token')).toBeNull()
  })

  it('isLoggedIn returns false after logout', async () => {
    const store = useAuthStore()

    // Login
    const loginResponse = {
      data: {
        token: 'test-token',
        user: { id: 1, name: 'Test User', email: 'test@example.com', role: 'user' },
      },
    }
    mockApiPost.mockResolvedValue(loginResponse)

    await store.login('test@example.com', 'password123')

    expect(store.isLoggedIn).toBe(true)

    // Logout
    mockApiPost.mockResolvedValue({ data: { message: 'Logged out' } })
    await store.logout()

    expect(store.isLoggedIn).toBe(false)
  })

  // ==================== Fetch User ====================

  it('can fetch current user', async () => {
    const store = useAuthStore()

    const mockUser = {
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
      role: 'user',
    }

    mockApiGet.mockResolvedValue({ data: mockUser })

    await store.fetchUser()

    expect(mockApiGet).toHaveBeenCalledWith('/auth/me')
    expect(store.user).toEqual(mockUser)
  })

  it('handles fetch user error', async () => {
    const store = useAuthStore()

    const mockError = new Error('Unauthorized')
    mockApiGet.mockRejectedValue(mockError)

    await expect(store.fetchUser()).rejects.toThrow('Unauthorized')
  })

  // ==================== Init ====================

  it('init does nothing when token is not in localStorage', () => {
    const store = useAuthStore()

    store.init()

    expect(store.token).toBeNull()
    expect(mockApiGet).not.toHaveBeenCalled()
  })

  it('init fetches user when token exists in localStorage', async () => {
    localStorage.setItem('token', 'existing-token-123')

    const mockUser = {
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
      role: 'user',
    }
    mockApiGet.mockResolvedValue({ data: mockUser })

    const store = useAuthStore()

    // Manually set token and call fetchUser
    store.token = 'existing-token-123'
    await store.fetchUser()

    expect(store.token).toBe('existing-token-123')
    expect(mockApiGet).toHaveBeenCalledWith('/auth/me')
    expect(store.user).toEqual(mockUser)
  })
})
