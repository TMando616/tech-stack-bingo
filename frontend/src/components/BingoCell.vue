<script setup lang="ts">
import { computed } from 'vue'
import type { BingoItem } from '../types'

const props = defineProps<{
  item: BingoItem
  isEditMode: boolean
}>()

const emit = defineEmits<{
  (e: 'click', item: BingoItem): void
}>()

const iconUrl = computed(() => {
  if (!props.item.label || props.item.label === 'FREE') return null
  
  // ラベル名からアイコンのスラッグを推測
  const slug = props.item.label
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/\./g, '')
    .replace(/\+/g, 'plus')
    .replace(/#/g, 'sharp')
  
  return `https://cdn.simpleicons.org/${slug}`
})

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
      <div v-if="iconUrl" class="cell-icon">
        <img :src="iconUrl" :alt="item.label" @error="(e: any) => e.target.style.display = 'none'">
      </div>
      <span class="cell-label">{{ item.label }}</span>
      <span v-if="item.achieved_at && !isEditMode" class="cell-date">{{ formatDate(item.achieved_at) }}</span>
      <span v-if="isEditMode && item.position !== 12" class="edit-icon">✏️</span>
    </div>
  </div>
</template>

<style scoped>
.bingo-cell { background: #fff; border: 1px solid #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 4px; cursor: pointer; transition: 0.2s; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); user-select: none; min-height: 80px; }
.bingo-cell:hover { transform: scale(1.02); box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.bingo-cell.is-achieved { background: #4caf50; color: white; border-color: #43a047; }
.bingo-cell.is-free { background: #ff9800; color: white; border-color: #f57c00; cursor: default; }
.cell-icon { margin-bottom: 4px; display: flex; justify-content: center; align-items: center; }
.cell-icon img { width: 24px; height: 24px; filter: grayscale(1) opacity(0.5); }
.is-achieved .cell-icon img { filter: brightness(0) invert(1); }
.cell-label { font-weight: 700; font-size: 0.75rem; line-height: 1.1; }
.cell-date { font-size: 0.5rem; opacity: 0.9; margin-top: 2px; display: block; }
.edit-icon { font-size: 0.7rem; }
</style>
