<script setup lang="ts">
/**
 * メインアプリケーションコンポーネント
 * ビンゴボードの表示、達成状況の更新、ラベル編集、ビンゴ判定を管理します。
 */
import { ref, onMounted, computed } from 'vue'
import apiClient from './api/axios'

// ビンゴ項目の型定義
interface BingoItem {
  id: number
  label: string
  is_achieved: boolean
  achieved_at: string | null
  position: number
}

// 状態管理
const bingoItems = ref<BingoItem[]>([])
const isLoading = ref(true)
const isEditMode = ref(false) // 編集モードかどうかのフラグ

// 日付のフォーマット (YYYY-MM-DD -> YYYY年MM月DD日)
const formatDate = (dateString: string | null) => {
  if (!dateString) return ''
  const [year, month, day] = dateString.split('-')
  return `${year}年${month}月${day}日`
}

// APIからビンゴ項目を取得
const fetchBingoItems = async () => {
  try {
    isLoading.value = true
    const response = await apiClient.get('/bingo-items')
    bingoItems.value = response.data.data.sort((a: BingoItem, b: BingoItem) => a.position - b.position)
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  } finally {
    isLoading.value = false
  }
}

// マスクリック時のアクション
const handleCellClick = (item: BingoItem) => {
  if (isEditMode.value) {
    editLabel(item)
  } else {
    toggleAchieved(item)
  }
}

// 達成状況の更新 (トグル)
const toggleAchieved = async (item: BingoItem) => {
  if (item.position === 12) return

  try {
    const updatedStatus = !item.is_achieved
    const response = await apiClient.patch(`/bingo-items/${item.id}`, {
      is_achieved: updatedStatus
    })
    
    const index = bingoItems.value.findIndex(i => i.id === item.id)
    if (index !== -1) {
      bingoItems.value[index] = response.data.data
    }
  } catch (error) {
    console.error('更新に失敗しました:', error)
  }
}

// ラベルの編集
const editLabel = async (item: BingoItem) => {
  if (item.position === 12) return

  const newLabel = window.prompt(`${item.label} の名前を編集:`, item.label)
  if (newLabel === null || newLabel === item.label || newLabel.trim() === '') return

  try {
    const response = await apiClient.patch(`/bingo-items/${item.id}`, {
      label: newLabel.trim()
    })
    
    const index = bingoItems.value.findIndex(i => i.id === item.id)
    if (index !== -1) {
      bingoItems.value[index] = response.data.data
    }
  } catch (error) {
    console.error('ラベルの更新に失敗しました:', error)
  }
}

// ビンゴ判定
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

onMounted(() => {
  fetchBingoItems()
})
</script>

<template>
  <main class="container">
    <header>
      <h1>技術スタック・ビンゴ</h1>
      <p>習得した技術にチェックを入れてビンゴを目指そう！</p>
      
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
    </header>

    <div v-if="isLoading" class="loading">
      読み込み中...
    </div>

    <div v-else class="bingo-grid" :class="{ 'editing': isEditMode }">
      <div 
        v-for="item in bingoItems" 
        :key="item.id" 
        class="bingo-cell"
        :class="{ 
          'is-achieved': item.is_achieved,
          'is-free': item.position === 12 
        }"
        @click="handleCellClick(item)"
      >
        <div class="cell-content">
          <span class="cell-label">{{ item.label }}</span>
          <span v-if="item.achieved_at && !isEditMode" class="cell-date">
            {{ formatDate(item.achieved_at) }}
          </span>
          <span v-if="isEditMode && item.position !== 12" class="edit-icon">✏️</span>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.container {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
  font-family: 'Helvetica Neue', Arial, sans-serif;
  color: #333;
}

header {
  text-align: center;
  margin-bottom: 2rem;
}

h1 {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.controls {
  margin-bottom: 1.5rem;
}

.btn-edit {
  padding: 8px 16px;
  border-radius: 20px;
  border: 2px solid #3498db;
  background-color: white;
  color: #3498db;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-edit:hover {
  background-color: #3498db;
  color: white;
}

.btn-edit.active {
  background-color: #e74c3c;
  border-color: #e74c3c;
  color: white;
}

.bingo-banner {
  background-color: #ffeb3b;
  color: #f44336;
  padding: 1rem;
  border-radius: 12px;
  font-size: 1.8rem;
  font-weight: bold;
  margin-top: 1rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.loading {
  text-align: center;
  padding: 3rem;
}

.bingo-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
  aspect-ratio: 1 / 1;
}

.bingo-grid.editing .bingo-cell:not(.is-free) {
  border: 2px dashed #3498db;
}

.bingo-cell {
  background-color: #fff;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  user-select: none;
}

.bingo-cell:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.cell-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.bingo-cell.is-achieved {
  background-color: #4caf50;
  color: white;
  border-color: #43a047;
}

.bingo-cell.is-free {
  background-color: #ff9800;
  color: white;
  border-color: #f57c00;
  cursor: default;
}

.cell-label {
  font-weight: 700;
  font-size: 0.9rem;
  line-height: 1.2;
}

.cell-date {
  font-size: 0.6rem;
  opacity: 0.9;
  background-color: rgba(255, 255, 255, 0.2);
  padding: 2px 4px;
  border-radius: 4px;
}

.edit-icon {
  font-size: 0.8rem;
}
</style>
