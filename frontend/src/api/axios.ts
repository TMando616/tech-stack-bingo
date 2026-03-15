import axios from 'axios'

/**
 * Axios インスタンスの設定
 * Sanctum (Cookie認証) を使用するため withCredentials を有効にします。
 */
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost',
  withCredentials: true,
  withXSRFToken: true, // これを追加
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// ポートが異なる場合、Axiosが自動でヘッダーを付けないことがあるため手動で補完
apiClient.interceptors.request.use(config => {
  const token = document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1];

  if (token) {
    config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
  }
  return config;
});

// LaravelのAPIプレフィックスを付けるためのラッパー
export const api = {
  get: (url: string, config?: any) => apiClient.get(`/api${url}`, config),
  post: (url: string, data?: any) => apiClient.post(`/api${url}`, data),
  patch: (url: string, data?: any) => apiClient.patch(`/api${url}`, data),
  delete: (url: string, config?: any) => apiClient.delete(`/api${url}`, config),
  csrf: () => apiClient.get('/sanctum/csrf-cookie') // CSRFトークン取得
}

export default apiClient
