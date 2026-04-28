export type User = {
  id: number
  name: string
  email: string
}

export type AuthResponse = {
  message: string
  user: User
}
