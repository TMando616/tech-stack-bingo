<script setup lang="ts">
import { ref, watch } from 'vue'
import type { BingoItem } from '../types'

const props = defineProps<{
  show: boolean
  item: BingoItem | null
  isSaving: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'save', label: string): void
}>()

const inputLabel = ref('')
const errorMessage = ref('')

watch(() => props.item, (newItem) => {
  if (newItem) {
    inputLabel.value = newItem.label
    errorMessage.value = ''
  }
}, { immediate: true })

const handleSave = () => {
  const label = inputLabel.value.trim()
  
  if (!label) {
    errorMessage.value = '項目名を入力してください。'
    return
  }
  if (label.length > 20) {
    errorMessage.value = '20文字以内で入力してください。'
    return
  }
  // 特殊文字の簡易バリデーション (タグ等の入力を防ぐ)
  if (/[<>&`'"]/.test(label)) {
    errorMessage.value = '使用できない記号(<, >, &, `, \', ")が含まれています。'
    return
  }

  errorMessage.value = ''
  emit('save', label)
}
</script>

<template>
  <div v-if="show" class="modal-overlay" @click.self="emit('close')">
    <div class="modal-content">
      <h3>項目の編集</h3>
      <input 
        v-model="inputLabel" 
        type="text" 
        class="modal-input" 
        :class="{ 'is-invalid': errorMessage }"
        placeholder="項目名を入力"
        @keyup.enter="handleSave"
        autofocus
      />
      <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
      <div class="modal-actions">
        <button class="btn-cancel" @click="emit('close')" :disabled="isSaving">キャンセル</button>
        <button class="btn-save" @click="handleSave" :disabled="isSaving">
          {{ isSaving ? '保存中...' : '保存' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; padding: 1.5rem; border-radius: 8px; width: 90%; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
.modal-content h3 { margin-top: 0; margin-bottom: 1rem; color: #2c3e50; }
.modal-input { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 1rem; margin-bottom: 1rem; }
.modal-input:focus { outline: none; border-color: #3498db; box-shadow: 0 0 0 2px rgba(52,152,219,0.2); }
.modal-input.is-invalid { border-color: #e74c3c; box-shadow: 0 0 0 2px rgba(231,76,60,0.2); margin-bottom: 0.5rem; }
.error-message { color: #e74c3c; font-size: 0.85rem; margin-bottom: 1rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
.btn-cancel { padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; color: #666; }
.btn-cancel:hover:not(:disabled) { background: #f5f5f5; }
.btn-save { padding: 8px 16px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
.btn-save:hover:not(:disabled) { background: #2980b9; }
.btn-save:disabled, .btn-cancel:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
