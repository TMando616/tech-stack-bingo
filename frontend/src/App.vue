<script setup lang="ts">
/**
 * メインアプリケーションコンポーネント
 * 認証、複数ボード管理、ビンゴ判定、ラベル編集を統合。
 */
import { ref, onMounted, computed, watch } from 'vue'
import { api } from './api/axios'

// 型定義
interface User { id: number; name: string; email: string; }
interface BingoBoard { id: number; title: string; }
interface BingoItem { id: number; label: string; is_achieved: boolean; achieved_at: string | null; position: number; }

// 認証状態
const user = ref<User | null>(null)
const loginEmail = ref('test@example.com')
const loginPassword = ref('password')
const isLoggingIn = ref(false)

// ボード管理
const boards = ref<BingoBoard[]>([])
const currentBoard = ref<BingoBoard | null>(null)
const newBoardTitle = ref('')

// ビンゴ状態
const bingoItems = ref<BingoItem[]>([])
const isLoading = ref(false)
const isEditMode = ref(false)

// 認証: ログイン
const login = async () => {
  try {
    isLoggingIn.value = true
    await api.csrf()
    await api.post('/login', { email: loginEmail.value, password: loginPassword.value })
    await fetchUser()
    if (user.value) {
      await fetchBoards()
    }
  } catch (error) {
    console.error('ログイン失敗:', error)
    alert('ログインに失敗しました。')
  } finally {
    isLoggingIn.value = false
  }
}

const logout = async () => {
  try {
    await api.post('/logout')
    user.value = null
    boards.value = []
    currentBoard.value = null
    bingoItems.value = []
  } catch (error) {
    console.error('ログアウト失敗:', error)
  }
}

const fetchUser = async () => {
  try {
    const response = await api.get('/user')
    user.value = response.data
  } catch (error) {
    user.value = null
  }
}

// ボード管理: 取得
const fetchBoards = async () => {
  try {
    const response = await api.get('/bingo-boards')
    boards.value = response.data
    // まだ選択されていない場合に最初のボードを選択
    if (boards.value.length > 0 && !currentBoard.value) {
      currentBoard.value = boards.value[0]
    }
  } catch (error) {
    console.error('ボード取得失敗:', error)
  }
}

// ボード管理: 作成
const createBoard = async () => {
  if (!newBoardTitle.value.trim()) return
  try {
    const response = await api.post('/bingo-boards', { title: newBoardTitle.value.trim() })
    boards.value.unshift(response.data)
    currentBoard.value = response.data
    newBoardTitle.value = ''
  } catch (error) {
    console.error('ボード作成失敗:', error)
  }
}

// ビンゴ管理: 取得
const fetchBingoItems = async () => {
  if (!currentBoard.value) return
  try {
    isLoading.value = true
    const response = await api.get(`/bingo-items?bingo_board_id=${currentBoard.value.id}`)
    const data = response.data.data || response.data // ResourceCollectionの形式に対応
    bingoItems.value = data.sort((a: BingoItem, b: BingoItem) => a.position - b.position)
  } catch (error) {
    console.error('項目取得失敗:', error)
    bingoItems.value = []
  } finally {
    isLoading.value = false
  }
}

// ボード切り替え時に項目を再取得
watch(currentBoard, (newBoard) => {
  if (newBoard) fetchBingoItems()
}, { immediate: true })

const handleCellClick = (item: BingoItem) => {
  if (isEditMode.value) editLabel(item)
  else toggleAchieved(item)
}

const toggleAchieved = async (item: BingoItem) => {
  if (item.position === 12) return
  try {
    const response = await api.patch(`/bingo-items/${item.id}`, { is_achieved: !item.is_achieved })
    const updatedData = response.data.data || response.data
    const index = bingoItems.value.findIndex(i => i.id === item.id)
    if (index !== -1) {
      bingoItems.value[index] = updatedData
      // 念のためソートを維持
      bingoItems.value.sort((a: BingoItem, b: BingoItem) => a.position - b.position)
    }
  } catch (error) {
    console.error('更新失敗:', error)
  }
}

const editLabel = async (item: BingoItem) => {
  if (item.position === 12) return
  const newLabel = window.prompt(`${item.label} を編集:`, item.label)
  if (newLabel === null || newLabel === item.label) return
  try {
    const response = await api.patch(`/bingo-items/${item.id}`, { label: newLabel.trim() })
    const updatedData = response.data.data || response.data
    const index = bingoItems.value.findIndex(i => i.id === item.id)
    if (index !== -1) {
      bingoItems.value[index] = updatedData
    }
  } catch (error) {
    console.error('編集失敗:', error)
  }
}

const bingoCount = computed(() => {
  if (bingoItems.value.length < 25) return 0
  let count = 0
  const grid = bingoItems.value
  const lines = [
    [0, 1, 2, 3, 4], [5, 6, 7, 8, 9], [10, 11, 12, 13, 14], [15, 16, 17, 18, 19], [20, 21, 22, 23, 24],
    [0, 5, 10, 15, 20], [1, 6, 11, 16, 21], [2, 7, 12, 17, 22], [3, 8, 13, 18, 23], [4, 9, 14, 19, 24],
    [0, 6, 12, 18, 24], [4, 8, 12, 16, 20]
  ]
  for (const line of lines) { if (line.every(index => grid[index]?.is_achieved)) count++ }
  return count
})

const formatDate = (ds: string | null) => {
  if (!ds || typeof ds !== 'string') return ''
  const parts = ds.split('-')
  if (parts.length !== 3) return ds
  const [y, m, d] = parts
  return `${y}年${m}月${d}日`
}

onMounted(async () => {
  await fetchUser()
  if (user.value) {
    await fetchBoards()
  }
})
</script>

<template>
  <main class="container">
    <header>
      <h1>技術スタック・ビンゴ</h1>
      <div v-if="user" class="user-info">
        <span>こんにちは、<strong>{{ user.name }}</strong> さん</span>
        <button class="btn-logout" @click="logout">ログアウト</button>
      </div>
    </header>

    <div v-if="!user" class="login-card">
      <h2>ログイン</h2>
      <div class="form-group"><label>メールアドレス</label><input v-model="loginEmail" type="email"></div>
      <div class="form-group"><label>パスワード</label><input v-model="loginPassword" type="password"></div>
      <button class="btn-primary" :disabled="isLoggingIn" @click="login">
        {{ isLoggingIn ? 'ログイン中...' : 'ログイン' }}
      </button>
    </div>

    <div v-else class="app-layout">
      <!-- サイドバー: ボード一覧 -->
      <aside class="sidebar">
        <h3>マイボード</h3>
        <div class="board-list">
          <div 
            v-for="board in boards" 
            :key="board.id" 
            class="board-item"
            :class="{ active: currentBoard?.id === board.id }"
            @click="currentBoard = board"
          >
            {{ board.title }}
          </div>
        </div>
        <div class="create-board">
          <input v-model="newBoardTitle" placeholder="新しいボード名" @keyup.enter="createBoard">
          <button @click="createBoard">+</button>
        </div>
      </aside>

      <!-- メイン: ビンゴボード -->
      <section class="main-content">
        <div v-if="currentBoard" class="board-container">
          <h2>{{ currentBoard.title }}</h2>
          <div class="controls">
            <button class="btn-edit" :class="{ active: isEditMode }" @click="isEditMode = !isEditMode">
              {{ isEditMode ? '編集終了' : '名前を編集' }}
            </button>
          </div>

          <div v-if="bingoCount > 0" class="bingo-banner">🎉 {{ bingoCount }} BINGO! 🎉</div>

          <div v-if="isLoading" class="loading">読み込み中...</div>
          <div v-else class="bingo-grid" :class="{ editing: isEditMode }">
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
        <div v-else class="no-board">ボードを選択してください</div>
      </section>
    </div>
  </main>
</template>

<style scoped>
.container { max-width: 900px; margin: 0 auto; padding: 1rem; font-family: 'Helvetica Neue', Arial, sans-serif; }
header { text-align: center; margin-bottom: 1.5rem; }
h1 { font-size: 2rem; color: #2c3e50; margin: 0; }
.user-info { font-size: 0.9rem; margin-top: 0.5rem; }
.btn-logout { background: none; border: none; color: #e74c3c; text-decoration: underline; cursor: pointer; }

.login-card { background: white; border: 1px solid #ddd; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 400px; margin: 2rem auto; text-align: center; }
.form-group { margin-bottom: 1rem; text-align: left; }
.form-group label { display: block; font-size: 0.8rem; font-weight: bold; margin-bottom: 4px; }
.form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
.btn-primary { width: 100%; padding: 10px; background-color: #3498db; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }

.app-layout { display: flex; gap: 2rem; margin-top: 1rem; }

/* サイドバー */
.sidebar { width: 200px; border-right: 1px solid #eee; padding-right: 1rem; }
.sidebar h3 { font-size: 1rem; margin-bottom: 1rem; color: #666; }
.board-list { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
.board-item { padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; transition: 0.2s; background: #f9f9f9; }
.board-item:hover { background: #f0f0f0; }
.board-item.active { background: #3498db; color: white; font-weight: bold; }
.create-board { display: flex; gap: 4px; }
.create-board input { flex: 1; padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.8rem; }
.create-board button { background: #2ecc71; color: white; border: none; border-radius: 4px; padding: 0 8px; cursor: pointer; }

/* メインコンテンツ */
.main-content { flex: 1; }
.board-container h2 { margin-top: 0; text-align: center; color: #2c3e50; }
.controls { text-align: center; margin-bottom: 1rem; }
.btn-edit { padding: 6px 12px; border-radius: 20px; border: 2px solid #3498db; background: white; color: #3498db; font-size: 0.8rem; font-weight: bold; cursor: pointer; }
.btn-edit.active { background: #e74c3c; border-color: #e74c3c; color: white; }
.bingo-banner { background: #ffeb3b; color: #f44336; padding: 0.8rem; border-radius: 8px; font-size: 1.5rem; font-weight: bold; text-align: center; margin-bottom: 1rem; }
.bingo-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; aspect-ratio: 1/1; }
.bingo-grid.editing .bingo-cell:not(.is-free) { border: 2px dashed #3498db; }
.bingo-cell { background: #fff; border: 1px solid #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 4px; cursor: pointer; transition: 0.2s; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); user-select: none; }
.bingo-cell:hover { transform: scale(1.02); box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.bingo-cell.is-achieved { background: #4caf50; color: white; border-color: #43a047; }
.bingo-cell.is-free { background: #ff9800; color: white; border-color: #f57c00; cursor: default; }
.cell-label { font-weight: 700; font-size: 0.75rem; line-height: 1.1; }
.cell-date { font-size: 0.5rem; opacity: 0.9; margin-top: 2px; display: block; }
.edit-icon { font-size: 0.7rem; }
.no-board { text-align: center; color: #999; margin-top: 4rem; }
</style>
