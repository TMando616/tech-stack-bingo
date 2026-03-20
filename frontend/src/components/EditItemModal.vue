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
  (e: 'save', label: string, description: string, link: string): void
}>()

const inputLabel = ref('')
const inputDescription = ref('')
const inputLink = ref('')
const errorMessage = ref('')

watch(() => props.item, (newItem) => {
  if (newItem) {
    inputLabel.value = newItem.label
    inputDescription.value = newItem.description || ''
    inputLink.value = newItem.link || ''
    errorMessage.value = ''
  }
}, { immediate: true })

const handleSave = () => {
  const label = inputLabel.value.trim()
  const description = inputDescription.value.trim()
  const link = inputLink.value.trim()
  
  if (!label) {
    errorMessage.value = '項目名を入力してください。'
    return
  }
  if (label.length > 20) {
    errorMessage.value = '20文字以内で入力してください。'
    return
  }
  
  // URLバリデーション
  if (link && !/^https?:\/\/.+/.test(link)) {
    errorMessage.value = '有効なURL (http:// または https://) を入力してください。'
    return
  }

  errorMessage.value = ''
  emit('save', label, description, link)
}
</script>

<template>
  <div v-if="show" class="modal-overlay" @click.self="emit('close')">
    <div class="modal-content">
      <h3>項目の編集</h3>
      
      <div class="form-group">
        <label>項目名</label>
        <input 
          v-model="inputLabel" 
          type="text" 
          class="modal-input" 
          :class="{ 'is-invalid': errorMessage }"
          placeholder="項目名を入力"
          autofocus
        />
      </div>

      <div class="form-group">
        <label>概要 (ツールチップに表示)</label>
        <textarea 
          v-model="inputDescription" 
          class="modal-input" 
          placeholder="技術の概要やメモ"
          rows="3"
        ></textarea>
      </div>

      <div class="form-group">
        <label>ドキュメントURL</label>
        <input 
          v-model="inputLink" 
          type="url" 
          class="modal-input" 
          placeholder="https://example.com"
          @keyup.enter="handleSave"
        />
      </div>

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
.modal-content { background: white; padding: 1.5rem; border-radius: 8px; width: 90%; max-width: 450px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
.modal-content h3 { margin-top: 0; margin-bottom: 1.5rem; color: #2c3e50; text-align: center; }

.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.85rem; color: #666; margin-bottom: 0.4rem; font-weight: bold; }

.modal-input { width: 100%; padding: 0.7rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 0.95rem; font-family: inherit; }
textarea.modal-input { resize: vertical; }
.modal-input:focus { outline: none; border-color: #3498db; box-shadow: 0 0 0 2px rgba(52,152,219,0.2); }
.modal-input.is-invalid { border-color: #e74c3c; box-shadow: 0 0 0 2px rgba(231,76,60,0.2); }

.error-message { color: #e74c3c; font-size: 0.85rem; margin-bottom: 1rem; padding: 0.5rem; background: #fdf2f2; border-radius: 4px; border-left: 3px solid #e74c3c; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem; }
