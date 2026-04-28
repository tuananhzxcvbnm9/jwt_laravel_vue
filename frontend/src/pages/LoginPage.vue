<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { getMe, login } from '@/services/auth.service'

const router = useRouter()
const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''

  try {
    await login({ email: email.value, password: password.value })
    await getMe()
    await router.push('/dashboard')
  } catch {
    errorMessage.value = 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="card">
      <h1>Đăng nhập</h1>
      <p class="subtitle">Sử dụng email/password để truy cập dashboard.</p>
      <form @submit.prevent="handleLogin" class="form">
        <label>
          Email
          <input v-model="email" type="email" required autocomplete="email" />
        </label>
        <label>
          Mật khẩu
          <input v-model="password" type="password" required autocomplete="current-password" />
        </label>
        <button :disabled="loading" type="submit">
          {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
        </button>
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      </form>
      <p>Chưa có tài khoản? <RouterLink to="/register">Đăng ký</RouterLink></p>
    </section>
  </main>
</template>
