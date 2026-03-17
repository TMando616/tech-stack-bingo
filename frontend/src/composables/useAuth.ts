import { ref } from 'vue'
import { api } from '../api/axios'
import type { User } from '../types'
import { useToast } from 'vue-toastification'

export function useAuth() {
  const user = ref<User | null>(null)
  const isLoggingIn = ref(false)
  const toast = useToast()

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
      toast.success('ログインしました！')
      return true
    } catch (error) {
      console.error('ログイン失敗:', error)
      toast.error('ログインに失敗しました。メールアドレスまたはパスワードを確認してください。')
      return false
    } finally {
      isLoggingIn.value = false
    }
  }

  const logout = async () => {
    try {
      await api.post('/logout')
      user.value = null
      toast.info('ログアウトしました。')
    } catch (error) {
      console.error('ログアウト失敗:', error)
      toast.error('ログアウトに失敗しました。')
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
