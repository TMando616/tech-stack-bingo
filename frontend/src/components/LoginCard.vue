<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  isLoading: boolean
}>()

const emit = defineEmits<{
  (e: 'login', email: string, password: string): void
}>()

const email = ref('test@example.com')
const password = ref('password')

const handleLogin = () => {
  emit('login', email.value, password.value)
}
</script>

<template>
  <div class="login-card">
    <h2>ログイン</h2>
    <div class="form-group">
      <label>メールアドレス</label>
      <input v-model="email" type="email">
    </div>
    <div class="form-group">
      <label>パスワード</label>
      <input v-model="password" type="password">
    </div>
    <button class="btn-primary" :disabled="isLoading" @click="handleLogin">
      {{ isLoading ? 'ログイン中...' : 'ログイン' }}
    </button>
  </div>
</template>

<style scoped>
.login-card { background: white; border: 1px solid #ddd; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 400px; margin: 2rem auto; text-align: center; }
.form-group { margin-bottom: 1rem; text-align: left; }
.form-group label { display: block; font-size: 0.8rem; font-weight: bold; margin-bottom: 4px; }
.form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
.btn-primary { width: 100%; padding: 10px; background-color: #3498db; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
</style>
