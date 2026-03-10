<script setup lang="ts">
/**
 * メインアプリケーションコンポーネント
 * ビンゴボードの表示、達成状況の更新、ビンゴ判定を管理します。
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

// ビンゴ項目の状態管理
const bingoItems = ref<BingoItem[]>([])
const isLoading = ref(true)

// APIからビンゴ項目を取得
const fetchBingoItems = async () => {
  try {
    isLoading.value = true
    const response = await apiClient.get('/bingo-items')
    // position 順にソートして格納
    bingoItems.value = response.data.data.sort((a: BingoItem, b: BingoItem) => a.position - b.position)
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  } finally {
    isLoading.value = false
  }
}

// 達成状況の更新 (トグル)
const toggleAchieved = async (item: BingoItem) => {
  // 中央(Free)は更新不可とする
  if (item.position === 12) return

  try {
    const updatedStatus = !item.is_achieved
    const response = await apiClient.patch(`/bingo-items/${item.id}`, {
      is_achieved: updatedStatus
    })
    
    // 取得した新しいデータで更新
    const index = bingoItems.value.findIndex(i => i.id === item.id)
    if (index !== -1) {
      bingoItems.value[index] = response.data.data
    }
  } catch (error) {
    console.error('更新に失敗しました:', error)
  }
}

// ビンゴ判定ロジック
const bingoCount = computed(() => {
  if (bingoItems.value.length < 25) return 0

  let count = 0
  const grid = bingoItems.value

  // 判定用インデックスの定義
  const lines = [
    // 横 5本
    [0, 1, 2, 3, 4], [5, 6, 7, 8, 9], [10, 11, 12, 13, 14], [15, 16, 17, 18, 19], [20, 21, 22, 23, 24],
    // 縦 5本
    [0, 5, 10, 15, 20], [1, 6, 11, 16, 21], [2, 7, 12, 17, 22], [3, 8, 13, 18, 23], [4, 9, 14, 19, 24],
    // 斜め 2本
    [0, 6, 12, 18, 24], [4, 8, 12, 16, 20]
  ]

  for (const line of lines) {
    if (line.every(index => grid[index]?.is_achieved)) {
      count++
    }
  }

  return count
})

// コンポーネントマウント時に取得
onMounted(() => {
  fetchBingoItems()
})
</script>

<template>
  <main class="container">
    <header>
      <h1>技術スタック・ビンゴ</h1>
      <p>習得した技術にチェックを入れてビンゴを目指そう！</p>
      
      <!-- ビンゴ表示エリア -->
      <div v-if="bingoCount > 0" class="bingo-banner">
        🎉 {{ bingoCount }} BINGO! 🎉
      </div>
    </header>

    <div v-if="isLoading" class="loading">
      読み込み中...
    </div>

    <div v-else class="bingo-grid">
      <div 
        v-for="item in bingoItems" 
        :key="item.id" 
        class="bingo-cell"
        :class="{ 
          'is-achieved': item.is_achieved,
          'is-free': item.position === 12 
        }"
        @click="toggleAchieved(item)"
      >
        <span class="cell-label">{{ item.label }}</span>
        <span v-if="item.achieved_at" class="cell-date">{{ item.achieved_at }}</span>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* 基本レイアウトの設定 */
.container {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
  font-family: sans-serif;
}

header {
  text-align: center;
  margin-bottom: 2rem;
}

.bingo-banner {
  background-color: #ffeb3b;
  color: #f44336;
  padding: 1rem;
  border-radius: 8px;
  font-size: 1.5rem;
  font-weight: bold;
  margin-top: 1rem;
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

/* 5x5のビンゴグリッドの設定 */
.bingo-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 10px;
  aspect-ratio: 1 / 1;
}

/* ビンゴのマスのスタイル */
.bingo-cell {
  background-color: #f0f0f0;
  border: 2px solid #ddd;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 5px;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
  font-size: 0.8rem;
  overflow: hidden;
  user-select: none;
}

.bingo-cell:hover {
  background-color: #e0e0e0;
  border-color: #bbb;
}

/* 達成済みマスのスタイル */
.bingo-cell.is-achieved {
  background-color: #4caf50;
  color: white;
  border-color: #388e3c;
}

/* 中央(Free)マスのスタイル */
.bingo-cell.is-free {
  background-color: #ff9800;
  color: white;
  border-color: #f57c00;
  cursor: default;
}

.cell-label {
  font-weight: bold;
}

.cell-date {
  font-size: 0.6rem;
  margin-top: 4px;
}
</style>
