import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '@/pages/LoginPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import DashboardPage from '@/pages/DashboardPage.vue'
import { getMe } from '@/services/auth.service'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },
    { path: '/login', component: LoginPage, meta: { guestOnly: true } },
    { path: '/register', component: RegisterPage, meta: { guestOnly: true } },
    { path: '/dashboard', component: DashboardPage, meta: { requiresAuth: true } },
  ],
})

router.beforeEach(async (to) => {
  const requiresAuth = Boolean(to.meta.requiresAuth)
  const guestOnly = Boolean(to.meta.guestOnly)

  try {
    await getMe()
    if (guestOnly) {
      return '/dashboard'
    }

    return true
  } catch {
    if (requiresAuth) {
      return '/login'
    }

    return true
  }
})

export default router
