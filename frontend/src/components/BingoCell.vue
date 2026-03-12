<script setup lang="ts">
import type { BingoItem } from '../types'

const props = defineProps<{
  item: BingoItem
  isEditMode: boolean
}>()

const emit = defineEmits<{
  (e: 'click', item: BingoItem): void
}>()

const formatDate = (ds: string | null) => {
  if (!ds || typeof ds !== 'string') return ''
  const parts = ds.split('-')
  if (parts.length !== 3) return ds
  const [y, m, d] = parts
  return `${y}年${m}月${d}日`
}
</script>

<template>
  <div 
    class="bingo-cell"
    :class="{ 'is-achieved': item.is_achieved, 'is-free': item.position === 12 }"
    @click="emit('click', item)"
  >
    <div class="cell-content">
      <span class="cell-label">{{ item.label }}</span>
      <span v-if="item.achieved_at && !isEditMode" class="cell-date">{{ formatDate(item.achieved_at) }}</span>
      <span v-if="isEditMode && item.position !== 12" class="edit-icon">✏️</span>
    </div>
  </div>
</template>

<style scoped>
.bingo-cell { background: #fff; border: 1px solid #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 4px; cursor: pointer; transition: 0.2s; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); user-select: none; }
.bingo-cell:hover { transform: scale(1.02); box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.bingo-cell.is-achieved { background: #4caf50; color: white; border-color: #43a047; }
.bingo-cell.is-free { background: #ff9800; color: white; border-color: #f57c00; cursor: default; }
.cell-label { font-weight: 700; font-size: 0.75rem; line-height: 1.1; }
.cell-date { font-size: 0.5rem; opacity: 0.9; margin-top: 2px; display: block; }
.edit-icon { font-size: 0.7rem; }
</style>
