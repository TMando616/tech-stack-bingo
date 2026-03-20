import { ref } from 'vue'
import { api } from '../api/axios'
import type { BingoBoard } from '../types'
import { useToast } from 'vue-toastification'

export function useBingoBoards() {
  const boards = ref<BingoBoard[]>([])
  const currentBoard = ref<BingoBoard | null>(null)
  const isLoading = ref(false)
  const toast = useToast()

  const fetchBoards = async () => {
    try {
      isLoading.value = true
      const response = await api.get('/bingo-boards')
      const data = response.data.data || response.data
      boards.value = Array.isArray(data) ? data : []
      
      if (boards.value.length > 0 && !currentBoard.value) {
        currentBoard.value = boards.value[0] || null
      }
      return true
    } catch (error) {
      console.error('ボード取得失敗:', error)
      toast.error('ボードの取得に失敗しました。')
      return false
    } finally {
      isLoading.value = false
    }
  }

  const createBoard = async (title: string, template?: string) => {
    if (!title.trim()) return null
    try {
      isLoading.value = true
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
    } finally {
      isLoading.value = false
    }
  }

  const deleteBoard = async (boardId: number) => {
    if (!confirm('本当にこのボードを削除しますか？')) return false
    try {
      isLoading.value = true
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
    } finally {
      isLoading.value = false
    }
  }

  const updateBoard = async (boardId: number, data: { title?: string, theme?: string }) => {
    try {
      isLoading.value = true
      const response = await api.patch(`/bingo-boards/${boardId}`, data)
      const updatedData = response.data.data || response.data
      
      const index = boards.value.findIndex(b => b.id === boardId)
      if (index !== -1) {
        boards.value[index] = updatedData
      }
      if (currentBoard.value?.id === boardId) {
        currentBoard.value = updatedData
      }
      toast.success('ボード設定を更新しました。')
      return updatedData
    } catch (error) {
      console.error('ボード更新失敗:', error)
      toast.error('ボードの更新に失敗しました。')
      return null
    } finally {
      isLoading.value = false
    }
  }

  const createBoardWithTemplate = async (templateKey: string, templateName: string) => {
    return await createBoard(`${templateName} Bingo`, templateKey)
  }

  const resetBoards = () => {
    boards.value = []
    currentBoard.value = null
  }

  return {
    boards,
    currentBoard,
    isLoading,
    fetchBoards,
    createBoard,
    updateBoard,
    createBoardWithTemplate,
    deleteBoard,
    resetBoards
  }
}
