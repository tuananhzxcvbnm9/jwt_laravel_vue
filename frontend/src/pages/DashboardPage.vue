<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { getMe, logout } from '@/services/auth.service'
import type { User } from '@/types/auth'

const router = useRouter()
const user = ref<User | null>(null)
const loading = ref(true)
const errorMessage = ref('')
const loggingOut = ref(false)

onMounted(async () => {
  try {
    const response = await getMe()
    user.value = response.user
  } catch {
    errorMessage.value = 'Không thể tải thông tin người dùng.'
  } finally {
    loading.value = false
  }
})

async function handleLogout() {
  loggingOut.value = true
  try {
    await logout()
  } finally {
    loggingOut.value = false
    await router.push('/login')
  }
}
</script>

<template>
  <main class="dashboard-page">
    <section class="card">
      <h1>Dashboard</h1>
      <p class="subtitle">Khu vực API protected bằng JWT HttpOnly Cookie.</p>

      <p v-if="loading">Đang tải dữ liệu người dùng...</p>
      <p v-else-if="errorMessage" class="error">{{ errorMessage }}</p>
      <div v-else-if="user" class="profile-grid">
        <div><strong>ID:</strong> {{ user.id }}</div>
        <div><strong>Name:</strong> {{ user.name }}</div>
        <div><strong>Email:</strong> {{ user.email }}</div>
      </div>
      <p v-else>Không có dữ liệu.</p>

      <button type="button" :disabled="loggingOut" @click="handleLogout">
        {{ loggingOut ? 'Đang đăng xuất...' : 'Đăng xuất' }}
      </button>
    </section>
  </main>
</template>
