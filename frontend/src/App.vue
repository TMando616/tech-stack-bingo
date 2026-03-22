<script setup lang="ts">
/**
 * メインアプリケーションコンポーネント
 * 認証、複数ボード管理、ビンゴ判定、ラベル編集を統合。
 */
import { ref, onMounted, watch, computed } from 'vue'
import confetti from 'canvas-confetti'
import html2canvas from 'html2canvas'
import { useAuth } from './composables/useAuth'
import { useBingoBoards } from './composables/useBingoBoards'
import { useBingoItems } from './composables/useBingoItems'
import type { BingoBoard, BingoItem } from './types'

// コンポーネントのインポート
import LoginCard from './components/LoginCard.vue'
import Sidebar from './components/Sidebar.vue'
import BingoGrid from './components/BingoGrid.vue'
import EditItemModal from './components/EditItemModal.vue'
import DashboardModal from './components/DashboardModal.vue'

// ダークモード管理
const isDarkMode = ref(localStorage.getItem('theme') === 'dark')
watch(isDarkMode, (newVal) => {
  if (newVal) {
    document.body.classList.add('dark-mode')
    localStorage.setItem('theme', 'dark')
  } else {
    document.body.classList.remove('dark-mode')
    localStorage.setItem('theme', 'light')
  }
}, { immediate: true })

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
}

// 状態管理
const { user, isLoggingIn, login, logout, fetchUser } = useAuth()
const { 
  boards, 
  currentBoard, 
  fetchBoards, 
  createBoard, 
  updateBoard,
  toggleLike,
  createBoardWithTemplate, 
  deleteBoard, 
  resetBoards 
} = useBingoBoards()

const handleUpdateTheme = async (theme: string) => {
  if (!currentBoard.value) return
  await updateBoard(currentBoard.value.id, { theme })
}

const handleTogglePublic = async () => {
  if (!currentBoard.value) return
  await updateBoard(currentBoard.value.id, { is_public: !currentBoard.value.is_public })
}

const handleToggleLike = async () => {
  if (!currentBoard.value) return
  await toggleLike(currentBoard.value.id, currentBoard.value.is_liked)
}

const { 
  bingoItems, 
  isLoading, 
  fetchBingoItems, 
  toggleAchieved, 
  updateItemLabel, 
  bingoCount, 
  resetItems 
} = useBingoItems()

const isEditMode = ref(false)

// 統計ダッシュボード状態
const showDashboard = ref(false)

// 画像出力状態
const isExporting = ref(false)
const exportAsImage = async () => {
  if (!currentBoard.value) return
  try {
    isExporting.value = true
    // メインコンテンツ要素を指定 (背景色をテーマに合わせる)
    const element = document.querySelector('.main-content') as HTMLElement
    if (!element) return
    const canvas = await html2canvas(element, { 
      useCORS: true, 
      backgroundColor: isDarkMode.value ? '#121212' : '#ffffff' 
    })
    const link = document.createElement('a')
    link.download = `${currentBoard.value.title}_bingo.png`
    link.href = canvas.toDataURL()
    link.click()
  } catch (err) {
    console.error('画像出力エラー:', err)
    alert('画像の保存に失敗しました。')
  } finally {
    isExporting.value = false
  }
}

// データエクスポート
const exportToJson = () => {
  if (!currentBoard.value || !bingoItems.value.length) return
  const data = {
    board: currentBoard.value,
    items: bingoItems.value
  }
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `${currentBoard.value.title}.json`
  link.click()
  URL.revokeObjectURL(url)
}

const exportToCsv = () => {
  if (!currentBoard.value || !bingoItems.value.length) return
  const headers = ['Position', 'Label', 'Achieved', 'Achieved At', 'Description', 'Link']
  const rows = bingoItems.value.map(item => [
    item.position,
    `"${item.label.replace(/"/g, '""')}"`,
    item.is_achieved ? 'Yes' : 'No',
    item.achieved_at || '',
    `"${(item.description || '').replace(/"/g, '""')}"`,
    item.link || ''
  ])
  const csvContent = [headers, ...rows].map(e => e.join(',')).join('\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `${currentBoard.value.title}.csv`
  link.click()
  URL.revokeObjectURL(url)
}

// 編集モーダル状態
const showEditModal = ref(false)
const editTargetItem = ref<BingoItem | null>(null)
const isSavingLabel = ref(false)

// 共有機能
const shareUrl = computed(() => {
  if (!currentBoard.value?.share_id) return ''
  return `${window.location.origin}/share/${currentBoard.value.share_id}`
})

const copyShareUrl = () => {
  if (!shareUrl.value) return
  navigator.clipboard.writeText(shareUrl.value)
  alert('共有URLをコピーしました！')
}

// 認証: ログイン
const handleLogin = async (email: string, password: string) => {
  const success = await login(email, password)
  if (success) {
    await fetchBoards()
  }
}

const handleLogout = async () => {
  await logout()
  resetBoards()
  resetItems()
}

// ボード管理
const handleCreateBoard = async (title: string) => {
  await createBoard(title)
}

const handleCreateTemplate = async (key: string, name: string) => {
  await createBoardWithTemplate(key, name)
}

const handleDeleteBoard = async (boardId: number) => {
  await deleteBoard(boardId)
}

const handleSelectBoard = (board: BingoBoard) => {
  currentBoard.value = board
}

// ビンゴ管理
watch(currentBoard, (newBoard) => {
  if (newBoard) {
    fetchBingoItems(newBoard.id, newBoard.items)
  } else {
    resetItems()
  }
}, { immediate: true })

// ビンゴ達成時の演出 (紙吹雪)
watch(bingoCount, (newCount, oldCount) => {
  // カウントが増えた場合（初回読み込み時は oldCount が undefined または 0）
  if (oldCount !== undefined && newCount > oldCount) {
    confetti({
      particleCount: 150,
      spread: 70,
      origin: { y: 0.6 },
      zIndex: 1000,
      colors: ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6']
    })
  }
})

const handleCellClick = (item: BingoItem) => {
  if (isEditMode.value) {
    if (item.position === 12) return
    editTargetItem.value = item
    showEditModal.value = true
  } else {
    toggleAchieved(item)
  }
}

const handleSaveLabel = async (newLabel: string, description: string, link: string) => {
  if (!editTargetItem.value) return
  
  // 変更があるかチェック (ラベル, 概要, リンクのいずれかが異なる場合のみ更新)
  const isChanged = 
    newLabel !== editTargetItem.value.label || 
    description !== (editTargetItem.value.description || '') || 
    link !== (editTargetItem.value.link || '')

  if (!isChanged) {
    showEditModal.value = false
    return
  }

  try {
    isSavingLabel.value = true
    await updateItemLabel(editTargetItem.value.id, newLabel, description, link)
    showEditModal.value = false
  } catch (error) {
    alert('編集に失敗しました。')
  } finally {
    isSavingLabel.value = false
  }
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
      <div class="header-actions">
        <button class="btn-stat" @click="showDashboard = true">📊 統計</button>
        <button class="btn-theme" @click="toggleDarkMode">
          {{ isDarkMode ? '☀️ ライトモード' : '🌙 ダークモード' }}
        </button>
      </div>
      <div v-if="user" class="user-info">
        <span>こんにちは、<strong>{{ user.name }}</strong> さん</span>
        <button class="btn-logout" @click="handleLogout">ログアウト</button>
      </div>
    </header>

    <div v-if="!user">
      <LoginCard :is-loading="isLoggingIn" @login="handleLogin" />
    </div>

    <div v-else class="app-layout">
      <Sidebar 
        :boards="boards" 
        :current-board="currentBoard" 
        @select-board="handleSelectBoard"
        @create-board="handleCreateBoard"
        @create-template="handleCreateTemplate"
        @delete-board="handleDeleteBoard"
      />

      <!-- メイン: ビンゴボード -->
      <section class="main-content">
        <div v-if="currentBoard" class="board-container" :class="currentBoard.theme || 'default'">
          <h2>{{ currentBoard.title }}</h2>
          <div class="controls">
            <button class="btn-edit" :class="{ active: isEditMode }" @click="isEditMode = !isEditMode">
              {{ isEditMode ? '編集終了' : '名前を編集' }}
            </button>
            <div class="theme-selector" v-if="isEditMode">
              <button 
                v-for="t in ['default', 'blue', 'green', 'purple']" 
                :key="t"
                class="theme-dot"
                :class="[t, { active: currentBoard.theme === t }]"
                @click="handleUpdateTheme(t)"
                :title="t"
              ></button>
              <button 
                class="btn-public-toggle" 
                :class="{ 'is-public': currentBoard.is_public }"
                @click="handleTogglePublic"
              >
                {{ currentBoard.is_public ? '🔓 公開中' : '🔒 非公開' }}
              </button>
            </div>
            <div class="export-group">
              <button class="btn-export" @click="exportAsImage" :disabled="isExporting">
                {{ isExporting ? '生成中...' : '📷 画像' }}
              </button>
              <button class="btn-export btn-secondary" @click="exportToJson">
                JS
              </button>
              <button class="btn-export btn-secondary" @click="exportToCsv">
                CS
              </button>
            </div>
            <div v-if="currentBoard.share_id" class="share-box">
              <button class="btn-like" :class="{ 'is-liked': currentBoard.is_liked }" @click="handleToggleLike">
                {{ currentBoard.is_liked ? '❤️' : '🤍' }} {{ currentBoard.likes_count }}
              </button>
              <input :value="shareUrl" readonly class="share-input">
              <button class="btn-copy" @click="copyShareUrl">🔗 共有</button>
            </div>
          </div>

          <div v-if="bingoCount > 0" class="bingo-banner">🎉 {{ bingoCount }} BINGO! 🎉</div>

          <div v-if="isLoading" class="skeleton-grid">
            <div v-for="i in 25" :key="i" class="skeleton-cell"></div>
          </div>
          <BingoGrid 
            v-else
            :items="bingoItems" 
            :is-edit-mode="isEditMode"
            @cell-click="handleCellClick"
          />
        </div>
        <div v-else class="no-board">ボードを選択してください</div>
      </section>
    </div>

    <!-- 編集モーダル -->
    <EditItemModal 
      :show="showEditModal" 
      :item="editTargetItem" 
      :is-saving="isSavingLabel"
      @close="showEditModal = false"
      @save="handleSaveLabel"
    />

    <!-- 統計ダッシュボード -->
    <DashboardModal 
      :show="showDashboard" 
      @close="showDashboard = false" 
    />
  </main>
</template>

<style scoped>
.container { max-width: 900px; margin: 0 auto; padding: 1rem; font-family: 'Helvetica Neue', Arial, sans-serif; }
header { text-align: center; margin-bottom: 1.5rem; }
h1 { font-size: 2rem; color: #2c3e50; margin: 0; }
.header-actions { display: flex; justify-content: center; gap: 8px; margin-top: 10px; }
.btn-theme, .btn-stat { background: none; border: 1px solid #ddd; padding: 4px 12px; border-radius: 4px; cursor: pointer; color: inherit; font-size: 0.9rem; }
.btn-theme:hover, .btn-stat:hover { background: rgba(0,0,0,0.05); }
.user-info { font-size: 0.9rem; margin-top: 0.5rem; }
.btn-logout { background: none; border: none; color: #e74c3c; text-decoration: underline; cursor: pointer; }

.app-layout { display: flex; gap: 2rem; margin-top: 1rem; }

/* メインコンテンツ */
.main-content { flex: 1; }
.board-container h2 { margin-top: 0; text-align: center; color: #2c3e50; }
.controls { text-align: center; margin-bottom: 1rem; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; align-items: center; }
.btn-edit { padding: 6px 12px; border-radius: 20px; border: 2px solid #3498db; background: white; color: #3498db; font-size: 0.8rem; font-weight: bold; cursor: pointer; }
.btn-edit.active { background: #e74c3c; border-color: #e74c3c; color: white; }

.theme-selector { display: flex; gap: 6px; align-items: center; margin: 0 10px; }
.theme-dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; transition: 0.2s; }
.theme-dot.active { border-color: #333; transform: scale(1.2); }
.theme-dot.default { background: #f0f0f0; border-color: #ddd; }
.theme-dot.blue { background: #e3f2fd; }
.theme-dot.green { background: #e8f5e9; }
.theme-dot.purple { background: #f3e5f5; }
body.dark-mode .theme-dot.active { border-color: #fff; }

.btn-public-toggle { font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; border: 1px solid #ddd; background: #f9f9f9; cursor: pointer; }
.btn-public-toggle.is-public { border-color: #27ae60; color: #27ae60; background: #eafaf1; }

.btn-export { padding: 6px 12px; border-radius: 20px; border: 2px solid #27ae60; background: white; color: #27ae60; font-size: 0.8rem; font-weight: bold; cursor: pointer; }
.btn-export:hover:not(:disabled) { background: #eafaf1; }
.btn-export:disabled { opacity: 0.6; cursor: not-allowed; }

.export-group { display: flex; border: 2px solid #27ae60; border-radius: 20px; overflow: hidden; }
.export-group .btn-export { border: none; border-radius: 0; padding: 6px 10px; }
.export-group .btn-export:not(:last-child) { border-right: 1px solid #27ae60; }
.btn-secondary { background: #f9f9f9; color: #27ae60; font-size: 0.7rem; }
.btn-secondary:hover { background: #eafaf1; }

.share-box { display: flex; justify-content: center; gap: 8px; align-items: center; margin-top: 0; }
.share-input { width: 260px; padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.75rem; background: #f9f9f9; color: #666; }
.btn-copy { padding: 4px 12px; border: 1px solid #27ae60; background: #27ae60; color: white; border-radius: 4px; font-size: 0.8rem; cursor: pointer; }
.btn-copy:hover { background: #219150; }

.btn-like { background: white; border: 1px solid #ddd; border-radius: 20px; padding: 4px 10px; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: 0.2s; }
.btn-like:hover { transform: scale(1.05); background: #fff5f5; }
.btn-like.is-liked { border-color: #ff7675; color: #e74c3c; }

.bingo-banner { background: #ffeb3b; color: #f44336; padding: 0.8rem; border-radius: 8px; font-size: 1.5rem; font-weight: bold; text-align: center; margin-bottom: 1rem; }

.loading { text-align: center; padding: 2rem; color: #666; }
.no-board { text-align: center; color: #999; margin-top: 4rem; }

/* スケルトンスクリーン */
.skeleton-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; aspect-ratio: 1; max-width: 500px; margin: 0 auto; }
.skeleton-cell { background: #e0e0e0; border-radius: 8px; animation: pulse 1.5s infinite ease-in-out; }
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.4; }
  100% { opacity: 1; }
}
</style>

<style>
/* ダークモードのグローバルスタイル */
body.dark-mode { background-color: #121212; color: #e0e0e0; }
body.dark-mode .container h1, 
body.dark-mode .board-container h2 { color: #ffffff; }
body.dark-mode .btn-theme,
body.dark-mode .btn-stat { border-color: #555; }
body.dark-mode .btn-theme:hover,
body.dark-mode .btn-stat:hover { background: rgba(255,255,255,0.1); }
body.dark-mode .modal-content, 
body.dark-mode .board-item,
body.dark-mode .bingo-cell { background-color: #1e1e1e !important; color: #e0e0e0 !important; border-color: #333 !important; }
body.dark-mode .modal-content h3 { color: #ffffff; }
body.dark-mode .share-input { background: #333; color: #fff; border-color: #555; }
body.dark-mode .skeleton-cell { background: #333; }
body.dark-mode .btn-edit { background: transparent; }
body.dark-mode .btn-export { background: transparent; }
body.dark-mode .btn-public-toggle { background: #2d2d2d; border-color: #444; color: #999; }
body.dark-mode .btn-like { background: #2d2d2d; border-color: #444; color: #e0e0e0; }

/* ボードテーマ */
.board-container { padding: 1.5rem; border-radius: 12px; transition: 0.3s; }
.board-container.blue { background-color: #e3f2fd; }
.board-container.green { background-color: #e8f5e9; }
.board-container.purple { background-color: #f3e5f5; }

body.dark-mode .board-container.blue { background-color: #0d47a122; }
body.dark-mode .board-container.green { background-color: #1b5e2022; }
body.dark-mode .board-container.purple { background-color: #4a148c22; }
</style>
