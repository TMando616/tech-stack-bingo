import axios from 'axios'

/**
 * Axios インスタンスの設定
 * Sanctum (Cookie認証) を使用するため withCredentials を有効にします。
 */
const apiClient = axios.create({
  baseURL: 'http://localhost:8000', // バックエンドのベースURL
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// LaravelのAPIプレフィックスを付けるためのラッパー
export const api = {
  get: (url: string) => apiClient.get(`/api${url}`),
  post: (url: string) => apiClient.post(`/api${url}`),
  patch: (url: string, data?: any) => apiClient.patch(`/api${url}`, data),
  delete: (url: string) => apiClient.delete(`/api${url}`),
  csrf: () => apiClient.get('/sanctum/csrf-cookie') // CSRFトークン取得
}

export default apiClient
