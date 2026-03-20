import { ref, computed } from 'vue'
import { api } from '../api/axios'
import type { BingoItem } from '../types'
import { useToast } from 'vue-toastification'

export function useBingoItems() {
  const bingoItems = ref<BingoItem[]>([])
  const isLoading = ref(false)
  const toast = useToast()

  const fetchBingoItems = async (boardId: number, initialItems?: BingoItem[]) => {
    // すでにアイテムデータがある場合はそれを使用 (通信削減)
    if (initialItems && initialItems.length === 25) {
      bingoItems.value = [...initialItems].sort((a: BingoItem, b: BingoItem) => a.position - b.position)
      return true
    }

    try {
      isLoading.value = true
      const response = await api.get(`/bingo-items?bingo_board_id=${boardId}`)
      const data = response.data.data || response.data
      bingoItems.value = data.sort((a: BingoItem, b: BingoItem) => a.position - b.position)
      return true
    } catch (error) {
      console.error('項目取得失敗:', error)
      toast.error('項目の取得に失敗しました。')
      bingoItems.value = []
      return false
    } finally {
      isLoading.value = false
    }
  }

  const toggleAchieved = async (item: BingoItem) => {
    if (item.position === 12) return false
    try {
      isLoading.value = true
      const response = await api.patch(`/bingo-items/${item.id}`, { is_achieved: !item.is_achieved })
      const updatedData = response.data.data || response.data
      const index = bingoItems.value.findIndex(i => i.id === item.id)
      if (index !== -1) {
        bingoItems.value[index] = updatedData
        // position でソートを維持
        bingoItems.value.sort((a: BingoItem, b: BingoItem) => a.position - b.position)
      }

      if (updatedData.is_achieved) {
        toast.success(`${updatedData.label} を達成しました！`)
      }
      return true
    } catch (error) {
      console.error('更新失敗:', error)
      toast.error('ステータスの更新に失敗しました。')
      return false
    } finally {
      isLoading.value = false
    }
  }

  const updateItemLabel = async (itemId: number, label: string, description?: string, link?: string) => {
    try {
      isLoading.value = true
      const response = await api.patch(`/bingo-items/${itemId}`, { 
        label,
        description,
        link
      })
      const updatedData = response.data.data || response.data
      const index = bingoItems.value.findIndex(i => i.id === itemId)
      if (index !== -1) {
        bingoItems.value[index] = updatedData
      }
      toast.success('項目を更新しました。')
      return updatedData
    } catch (error) {
      console.error('編集失敗:', error)
      toast.error('項目の更新に失敗しました。')
      return null
    } finally {
      isLoading.value = false
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

  const resetItems = () => {
    bingoItems.value = []
  }

  return {
    bingoItems,
    isLoading,
    fetchBingoItems,
    toggleAchieved,
    updateItemLabel,
    bingoCount,
    resetItems
  }
}
