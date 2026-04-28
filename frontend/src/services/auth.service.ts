import api from '@/lib/api'
import type { AuthResponse } from '@/types/auth'

export type RegisterPayload = {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export type LoginPayload = {
  email: string
  password: string
}

export async function register(payload: RegisterPayload): Promise<AuthResponse> {
  const response = await api.post<AuthResponse>('/auth/register', payload)
  return response.data
}

export async function login(payload: LoginPayload): Promise<AuthResponse> {
  const response = await api.post<AuthResponse>('/auth/login', payload)
  return response.data
}

export async function getMe(): Promise<{ user: AuthResponse['user'] }> {
  const response = await api.get<{ user: AuthResponse['user'] }>('/auth/me')
  return response.data
}

export async function refreshToken(): Promise<AuthResponse> {
  const response = await api.post<AuthResponse>('/auth/refresh')
  return response.data
}

export async function logout(): Promise<{ message: string }> {
  const response = await api.post<{ message: string }>('/auth/logout')
  return response.data
}
