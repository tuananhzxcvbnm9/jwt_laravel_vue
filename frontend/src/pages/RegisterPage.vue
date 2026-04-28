<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { register } from '@/services/auth.service'

const router = useRouter()
const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const errorMessage = ref('')

async function handleRegister() {
  loading.value = true
  errorMessage.value = ''

  try {
    await register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    await router.push('/dashboard')
  } catch {
    errorMessage.value = 'Đăng ký thất bại. Vui lòng thử lại.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="card">
      <h1>Tạo tài khoản</h1>
      <p class="subtitle">Tài khoản mới sẽ được đăng nhập ngay sau khi đăng ký.</p>
      <form @submit.prevent="handleRegister" class="form">
        <label>
          Họ tên
          <input v-model="name" type="text" required autocomplete="name" />
        </label>
        <label>
          Email
          <input v-model="email" type="email" required autocomplete="email" />
        </label>
        <label>
          Mật khẩu
          <input v-model="password" type="password" required autocomplete="new-password" />
        </label>
        <label>
          Nhập lại mật khẩu
          <input v-model="passwordConfirmation" type="password" required autocomplete="new-password" />
        </label>
        <button :disabled="loading" type="submit">
          {{ loading ? 'Đang tạo tài khoản...' : 'Đăng ký' }}
        </button>
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      </form>
      <p>Đã có tài khoản? <RouterLink to="/login">Đăng nhập</RouterLink></p>
    </section>
  </main>
</template>
