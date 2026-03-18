import { ref } from 'vue'
import { api } from '../api/axios'
import type { BingoBoard } from '../types'
import { useToast } from 'vue-toastification'

export function useBingoBoards() {
  const boards = ref<BingoBoard[]>([])
  const currentBoard = ref<BingoBoard | null>(null)
  const toast = useToast()

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
      toast.error('ボードの取得に失敗しました。')
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
      toast.success('ボードを作成しました！')
      return data
    } catch (error) {
      console.error('ボード作成失敗:', error)
      toast.error('ボードの作成に失敗しました。')
      return null
    }
  }

  const deleteBoard = async (boardId: number) => {
    if (!confirm('本当にこのボードを削除しますか？')) return
    try {
      await api.delete(`/bingo-boards/${boardId}`)
      boards.value = boards.value.filter(b => b.id !== boardId)
      if (currentBoard.value?.id === boardId) {
        currentBoard.value = boards.value[0] || null
      }
      toast.success('ボードを削除しました。')
      return true
    } catch (error) {
      console.error('ボード削除失敗:', error)
      toast.error('ボードの削除に失敗しました。')
      return false
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
    deleteBoard,
    resetBoards
  }
}
