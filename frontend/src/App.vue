<script setup lang="ts">
/**
 * メインアプリケーションコンポーネント
 * ビンゴボードの表示を管理します。
 */
import { ref, onMounted } from 'vue'
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
    bingoItems.value = response.data.data
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
