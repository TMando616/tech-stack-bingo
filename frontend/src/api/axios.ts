import axios from 'axios'

/**
 * Axios インスタンスの設定
 * バックエンド API (Laravel) への共通設定を行います。
 */
const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api', // Laravel 開発サーバーのデフォルトURL
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest' // Laravel 側で XHR として認識させるため
  }
})

export default apiClient
