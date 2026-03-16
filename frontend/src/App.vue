<script setup lang="ts">
/**
 * メインアプリケーションコンポーネント
 * 認証、複数ボード管理、ビンゴ判定、ラベル編集を統合。
 */
import { ref, onMounted, watch, computed } from 'vue'
import { useAuth } from './composables/useAuth'
import { useBingoBoards } from './composables/useBingoBoards'
import { useBingoItems } from './composables/useBingoItems'
import type { BingoBoard, BingoItem } from './types'

// コンポーネントのインポート
import LoginCard from './components/LoginCard.vue'
import Sidebar from './components/Sidebar.vue'
import BingoGrid from './components/BingoGrid.vue'
import EditItemModal from './components/EditItemModal.vue'

// 状態管理
const { user, isLoggingIn, login, logout, fetchUser } = useAuth()
const { boards, currentBoard, fetchBoards, createBoard, createBoardWithTemplate, resetBoards } = useBingoBoards()
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

const handleSelectBoard = (board: BingoBoard) => {
  currentBoard.value = board
}

// ビンゴ管理
watch(currentBoard, (newBoard) => {
  if (newBoard) {
    fetchBingoItems(newBoard.id)
  } else {
    resetItems()
  }
}, { immediate: true })

const handleCellClick = (item: BingoItem) => {
  if (isEditMode.value) {
    if (item.position === 12) return
    editTargetItem.value = item
    showEditModal.value = true
  } else {
    toggleAchieved(item)
  }
}

const handleSaveLabel = async (newLabel: string) => {
  if (!editTargetItem.value) return
  if (!newLabel || newLabel === editTargetItem.value.label) {
    showEditModal.value = false
    return
  }

  try {
    isSavingLabel.value = true
    await updateItemLabel(editTargetItem.value.id, newLabel)
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
      />

      <!-- メイン: ビンゴボード -->
      <section class="main-content">
        <div v-if="currentBoard" class="board-container">
          <h2>{{ currentBoard.title }}</h2>
          <div class="controls">
            <button class="btn-edit" :class="{ active: isEditMode }" @click="isEditMode = !isEditMode">
              {{ isEditMode ? '編集終了' : '名前を編集' }}
            </button>
            <div v-if="currentBoard" class="share-box">
              <input :value="shareUrl" readonly class="share-input">
              <button class="btn-copy" @click="copyShareUrl">🔗 共有</button>
            </div>
          </div>

          <div v-if="bingoCount > 0" class="bingo-banner">🎉 {{ bingoCount }} BINGO! 🎉</div>

          <div v-if="isLoading" class="loading">読み込み中...</div>
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
  </main>
</template>

<style scoped>
.container { max-width: 900px; margin: 0 auto; padding: 1rem; font-family: 'Helvetica Neue', Arial, sans-serif; }
header { text-align: center; margin-bottom: 1.5rem; }
h1 { font-size: 2rem; color: #2c3e50; margin: 0; }
.user-info { font-size: 0.9rem; margin-top: 0.5rem; }
.btn-logout { background: none; border: none; color: #e74c3c; text-decoration: underline; cursor: pointer; }

.app-layout { display: flex; gap: 2rem; margin-top: 1rem; }

/* メインコンテンツ */
.main-content { flex: 1; }
.board-container h2 { margin-top: 0; text-align: center; color: #2c3e50; }
.controls { text-align: center; margin-bottom: 1rem; }
.btn-edit { padding: 6px 12px; border-radius: 20px; border: 2px solid #3498db; background: white; color: #3498db; font-size: 0.8rem; font-weight: bold; cursor: pointer; }
.btn-edit.active { background: #e74c3c; border-color: #e74c3c; color: white; }

.share-box { margin-top: 1rem; display: flex; justify-content: center; gap: 8px; }
.share-input { width: 260px; padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.75rem; background: #f9f9f9; color: #666; }
.btn-copy { padding: 4px 12px; border: 1px solid #27ae60; background: #27ae60; color: white; border-radius: 4px; font-size: 0.8rem; cursor: pointer; }
.btn-copy:hover { background: #219150; }

.bingo-banner { background: #ffeb3b; color: #f44336; padding: 0.8rem; border-radius: 8px; font-size: 1.5rem; font-weight: bold; text-align: center; margin-bottom: 1rem; }

.loading { text-align: center; padding: 2rem; color: #666; }
.no-board { text-align: center; color: #999; margin-top: 4rem; }
</style>
