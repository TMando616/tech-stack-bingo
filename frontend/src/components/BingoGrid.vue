<script setup lang="ts">
import BingoCell from './BingoCell.vue'
import type { BingoItem } from '../types'

const props = defineProps<{
  items: BingoItem[]
  isEditMode: boolean
}>()

const emit = defineEmits<{
  (e: 'cellClick', item: BingoItem): void
}>()
</script>

<template>
  <div class="bingo-grid" :class="{ editing: isEditMode }">
    <BingoCell 
      v-for="item in items" 
      :key="item.id" 
      :item="item" 
      :is-edit-mode="isEditMode"
      @click="emit('cellClick', item)"
    />
  </div>
</template>

<style scoped>
.bingo-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; aspect-ratio: 1/1; }
.bingo-grid.editing :deep(.bingo-cell:not(.is-free)) { border: 2px dashed #3498db; }
</style>
