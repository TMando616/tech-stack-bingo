<script setup lang="ts">
/**
 * メインアプリケーションコンポーネント
 * 認証、ビンゴボードの表示、ラベル編集、ビンゴ判定を管理します。
 */
import { ref, onMounted, computed } from 'vue'
import { api } from './api/axios'

// 型定義
interface User {
  id: number
  name: string
  email: string
}

interface BingoItem {
  id: number
  label: string
  is_achieved: boolean
  achieved_at: string | null
  position: number
}

// 認証状態
const user = ref<User | null>(null)
const loginEmail = ref('test@example.com')
const loginPassword = ref('password')
const isLoggingIn = ref(false)

// ビンゴ状態
const bingoItems = ref<BingoItem[]>([])
const isLoading = ref(false)
const isEditMode = ref(false)

// 認証: ログイン
const login = async () => {
  try {
    isLoggingIn.value = true
    // 1. CSRFクッキー取得
    await api.csrf()
    // 2. ログイン実行
    await api.post('/login', {
      email: loginEmail.value,
      password: loginPassword.value
    })
    // 3. ユーザー情報取得
    await fetchUser()
    // 4. ビンゴ取得
    await fetchBingoItems()
  } catch (error) {
    console.error('ログインに失敗しました:', error)
    alert('ログインに失敗しました。メールアドレスとパスワードを確認してください。')
  } finally {
    isLoggingIn.value = false
  }
}

// 認証: ログアウト
const logout = async () => {
  try {
    await api.post('/logout')
    user.value = null
    bingoItems.value = []
  } catch (error) {
    console.error('ログアウトに失敗しました:', error)
  }
}

// ユーザー情報取得
const fetchUser = async () => {
  try {
    const response = await api.get('/user')
    user.value = response.data
  } catch (error) {
    user.value = null
  }
}

// ビンゴ項目取得
const fetchBingoItems = async () => {
  if (!user.value) return
  try {
    isLoading.value = true
    const response = await api.get('/bingo-items')
    bingoItems.value = response.data.data.sort((a: BingoItem, b: BingoItem) => a.position - b.position)
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  } finally {
    isLoading.value = false
  }
}

// アクション: マスクリック
const handleCellClick = (item: BingoItem) => {
  if (isEditMode.value) editLabel(item)
  else toggleAchieved(item)
}

// アクション: 達成トグル
const toggleAchieved = async (item: BingoItem) => {
  if (item.position === 12) return
  try {
    const updatedStatus = !item.is_achieved
    const response = await api.patch(`/bingo-items/${item.id}`, { is_achieved: updatedStatus })
    const index = bingoItems.value.findIndex(i => i.id === item.id)
    if (index !== -1) bingoItems.value[index] = response.data.data
  } catch (error) {
    console.error('更新に失敗しました:', error)
  }
}

// アクション: ラベル編集
const editLabel = async (item: BingoItem) => {
  if (item.position === 12) return
  const newLabel = window.prompt(`${item.label} の名前を編集:`, item.label)
  if (!newLabel || newLabel === item.label || newLabel.trim() === '') return
  try {
    const response = await api.patch(`/bingo-items/${item.id}`, { label: newLabel.trim() })
    const index = bingoItems.value.findIndex(i => i.id === item.id)
    if (index !== -1) bingoItems.value[index] = response.data.data
  } catch (error) {
    console.error('ラベルの更新に失敗しました:', error)
  }
}

// 判定: ビンゴ数
const bingoCount = computed(() => {
  if (bingoItems.value.length < 25) return 0
  let count = 0
  const grid = bingoItems.value
  const lines = [
    [0, 1, 2, 3, 4], [5, 6, 7, 8, 9], [10, 11, 12, 13, 14], [15, 16, 17, 18, 19], [20, 21, 22, 23, 24],
    [0, 5, 10, 15, 20], [1, 6, 11, 16, 21], [2, 7, 12, 17, 22], [3, 8, 13, 18, 23], [4, 9, 14, 19, 24],
    [0, 6, 12, 18, 24], [4, 8, 12, 16, 20]
  ]
  for (const line of lines) {
    if (line.every(index => grid[index]?.is_achieved)) count++
  }
  return count
})

const formatDate = (dateString: string | null) => {
  if (!dateString) return ''
  const [year, month, day] = dateString.split('-')
  return `${year}年${month}月${day}日`
}

onMounted(async () => {
  await fetchUser()
  if (user.value) await fetchBingoItems()
})
</script>

<template>
  <main class="container">
    <header>
      <h1>技術スタック・ビンゴ</h1>
      
      <!-- ログイン中のユーザー情報 -->
      <div v-if="user" class="user-info">
        <span>こんにちは、<strong>{{ user.name }}</strong> さん</span>
        <button class="btn-logout" @click="logout">ログアウト</button>
      </div>
    </header>

    <!-- ログインフォーム (未ログイン時) -->
    <div v-if="!user" class="login-card">
      <h2>ログイン</h2>
      <p>デモ用アカウントでログインできます。</p>
      <div class="form-group">
        <label>メールアドレス</label>
        <input v-model="loginEmail" type="email" placeholder="test@example.com">
      </div>
      <div class="form-group">
        <label>パスワード</label>
        <input v-model="loginPassword" type="password">
      </div>
      <button class="btn-primary" :disabled="isLoggingIn" @click="login">
        {{ isLoggingIn ? 'ログイン中...' : 'ログイン' }}
      </button>
    </div>

    <!-- ビンゴ画面 (ログイン時) -->
    <div v-else>
      <p class="description">習得した技術にチェックを入れてビンゴを目指そう！</p>
      
      <div class="controls">
        <button 
          class="btn-edit" 
          :class="{ 'active': isEditMode }"
          @click="isEditMode = !isEditMode"
        >
          {{ isEditMode ? '編集終了' : '名前を編集する' }}
        </button>
      </div>

      <div v-if="bingoCount > 0" class="bingo-banner">
        🎉 {{ bingoCount }} BINGO! 🎉
      </div>

      <div v-if="isLoading" class="loading">読み込み中...</div>
      <div v-else class="bingo-grid" :class="{ 'editing': isEditMode }">
        <div 
          v-for="item in bingoItems" 
          :key="item.id" 
          class="bingo-cell"
          :class="{ 'is-achieved': item.is_achieved, 'is-free': item.position === 12 }"
          @click="handleCellClick(item)"
        >
          <div class="cell-content">
            <span class="cell-label">{{ item.label }}</span>
            <span v-if="item.achieved_at && !isEditMode" class="cell-date">{{ formatDate(item.achieved_at) }}</span>
            <span v-if="isEditMode && item.position !== 12" class="edit-icon">✏️</span>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.container { max-width: 600px; margin: 0 auto; padding: 2rem; font-family: 'Helvetica Neue', Arial, sans-serif; color: #333; }
header { text-align: center; margin-bottom: 2rem; }
h1 { font-size: 2.5rem; color: #2c3e50; margin-bottom: 1rem; }
.description { text-align: center; margin-bottom: 1rem; color: #666; }

/* ユーザー情報・ログイン・ログアウト */
.user-info { display: flex; justify-content: center; align-items: center; gap: 1rem; font-size: 0.9rem; margin-top: -1rem; margin-bottom: 1rem; }
.btn-logout { background: none; border: none; color: #e74c3c; text-decoration: underline; cursor: pointer; padding: 0; }
.login-card { background: white; border: 1px solid #ddd; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 400px; margin: 0 auto; }
.form-group { margin-bottom: 1rem; text-align: left; }
.form-group label { display: block; font-size: 0.9rem; font-weight: bold; margin-bottom: 4px; }
.form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
.btn-primary { width: 100%; padding: 10px; background-color: #3498db; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
.btn-primary:disabled { background-color: #bdc3c7; cursor: not-allowed; }

/* コントロール・ビンゴグリッド */
.controls { text-align: center; margin-bottom: 1.5rem; }
.btn-edit { padding: 8px 16px; border-radius: 20px; border: 2px solid #3498db; background: white; color: #3498db; font-weight: bold; cursor: pointer; transition: 0.2s; }
.btn-edit:hover { background: #3498db; color: white; }
.btn-edit.active { background: #e74c3c; border-color: #e74c3c; color: white; }
.bingo-banner { background: #ffeb3b; color: #f44336; padding: 1rem; border-radius: 12px; font-size: 1.8rem; font-weight: bold; text-align: center; margin-bottom: 1rem; animation: pulse 1s infinite; }
@keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
.loading { text-align: center; padding: 2rem; }
.bingo-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; aspect-ratio: 1/1; }
.bingo-grid.editing .bingo-cell:not(.is-free) { border: 2px dashed #3498db; }
.bingo-cell { background: #fff; border: 2px solid #eee; border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 8px; cursor: pointer; transition: 0.3s; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); user-select: none; }
.bingo-cell:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
.cell-content { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.bingo-cell.is-achieved { background: #4caf50; color: white; border-color: #43a047; }
.bingo-cell.is-free { background: #ff9800; color: white; border-color: #f57c00; cursor: default; }
.cell-label { font-weight: 700; font-size: 0.8rem; line-height: 1.2; }
.cell-date { font-size: 0.6rem; opacity: 0.9; background: rgba(255,255,255,0.2); padding: 2px 4px; border-radius: 4px; }
.edit-icon { font-size: 0.8rem; }
</style>
