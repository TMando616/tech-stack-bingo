import { ref } from 'vue'
import { api } from '../api/axios'
import type { User } from '../types'

export function useAuth() {
  const user = ref<User | null>(null)
  const isLoggingIn = ref(false)

  const fetchUser = async () => {
    try {
      const response = await api.get('/user')
      user.value = response.data
    } catch (error) {
      user.value = null
    }
  }

  const login = async (email: string, password: string) => {
    try {
      isLoggingIn.value = true
      await api.csrf()
      await api.post('/login', { email, password })
      await fetchUser()
      return true
    } catch (error) {
      console.error('ログイン失敗:', error)
      alert('ログインに失敗しました。')
      return false
    } finally {
      isLoggingIn.value = false
    }
  }

  const logout = async () => {
    try {
      await api.post('/logout')
      user.value = null
    } catch (error) {
      console.error('ログアウト失敗:', error)
    }
  }

  return {
    user,
    isLoggingIn,
    login,
    logout,
    fetchUser
  }
}
