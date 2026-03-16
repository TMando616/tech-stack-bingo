<script setup lang="ts">
import { ref } from 'vue'
import type { BingoBoard } from '../types'

const props = defineProps<{
  boards: BingoBoard[]
  currentBoard: BingoBoard | null
}>()

const emit = defineEmits<{
  (e: 'selectBoard', board: BingoBoard): void
  (e: 'createBoard', title: string): void
  (e: 'createTemplate', templateKey: string, templateName: string): void
}>()

const newBoardTitle = ref('')

const handleCreate = () => {
  if (!newBoardTitle.value.trim()) return
  emit('createBoard', newBoardTitle.value.trim())
  newBoardTitle.value = ''
}

const handleCreateTemplate = (key: string, name: string) => {
  emit('createTemplate', key, name)
}
</script>

<template>
  <aside class="sidebar">
    <h3>テンプレート</h3>
    <div class="template-list">
      <button class="template-btn" @click="handleCreateTemplate('frontend', 'Frontend')">フロントエンド</button>
      <button class="template-btn" @click="handleCreateTemplate('backend', 'Backend')">バックエンド</button>
    </div>

    <h3>マイボード</h3>
    <div class="board-list">
      <div 
        v-for="board in boards" 
        :key="board.id" 
        class="board-item"
        :class="{ active: currentBoard?.id === board.id }"
        @click="emit('selectBoard', board)"
      >
        {{ board.title }}
      </div>
    </div>
    <div class="create-board">
      <input v-model="newBoardTitle" placeholder="新しいボード名" @keyup.enter="handleCreate">
      <button @click="handleCreate">+</button>
    </div>
  </aside>
</template>

<style scoped>
.sidebar { width: 220px; border-right: 1px solid #eee; padding-right: 1rem; }
.sidebar h3 { font-size: 0.9rem; margin: 1.5rem 0 0.5rem 0; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
.sidebar h3:first-child { margin-top: 0; }
.template-list { display: flex; flex-direction: column; gap: 0.5rem; }
.template-btn { padding: 8px; border: 1px dashed #3498db; border-radius: 6px; background: white; color: #3498db; cursor: pointer; font-size: 0.85rem; transition: 0.2s; }
.template-btn:hover { background: #ebf5fb; }
.board-list { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
.board-item { padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; transition: 0.2s; background: #f9f9f9; }
.board-item:hover { background: #f0f0f0; }
.board-item.active { background: #3498db; color: white; font-weight: bold; }
.create-board { display: flex; gap: 4px; }
.create-board input { flex: 1; padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.8rem; }
.create-board button { background: #2ecc71; color: white; border: none; border-radius: 4px; padding: 0 8px; cursor: pointer; }
</style>
