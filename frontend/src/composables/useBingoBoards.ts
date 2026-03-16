import { ref } from 'vue'
import { api } from '../api/axios'
import type { BingoBoard } from '../types'

export function useBingoBoards() {
  const boards = ref<BingoBoard[]>([])
  const currentBoard = ref<BingoBoard | null>(null)

  const fetchBoards = async () => {
    try {
      const response = await api.get('/bingo-boards')
      // Laravel Resource の 'data' ラッパーを考慮
      const data = response.data.data || response.data
      boards.value = Array.isArray(data) ? data : []
      
      if (boards.value.length > 0 && !currentBoard.value) {
        currentBoard.value = boards.value[0] || null
      }
    } catch (error) {
      console.error('ボード取得失敗:', error)
    }
  }

  const createBoard = async (title: string, template?: string) => {
    if (!title.trim()) return
    try {
      const response = await api.post('/bingo-boards', { 
        title: title.trim(),
        template: template
      })
      const data = response.data.data || response.data
      boards.value.unshift(data)
      currentBoard.value = data
      return data
    } catch (error) {
      console.error('ボード作成失敗:', error)
      return null
    }
  }

  const resetBoards = () => {
    boards.value = []
    currentBoard.value = null
  }

  const createBoardWithTemplate = async (templateKey: string, templateName: string) => {
    return await createBoard(`${templateName} Bingo`, templateKey)
  }

  return {
    boards,
    currentBoard,
    fetchBoards,
    createBoard,
    createBoardWithTemplate,
    resetBoards
  }
}
