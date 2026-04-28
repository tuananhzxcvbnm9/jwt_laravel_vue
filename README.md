# Laravel + Vue JWT HttpOnly Cookie Monorepo

Monorepo gồm:

- `backend/`: Laravel API (JWT access token + opaque refresh token trong HttpOnly cookie)
- `frontend/`: Vue 3 + Vite + TypeScript
- `docker-compose.yml`: local dev stack (backend + frontend + MySQL)

## 1) Backend setup

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

### Biến môi trường chính (`backend/.env`)

- `APP_URL=http://localhost:8000`
- `FRONTEND_URL=http://localhost:5173`
- `JWT_ACCESS_SECRET=<long-random-secret>`
- `JWT_ACCESS_TTL_MINUTES=15`
- `JWT_REFRESH_TTL_DAYS=30`
- `JWT_COOKIE_SECURE=false` (local)
- `JWT_COOKIE_SAME_SITE=lax`

> Production: bắt buộc `JWT_COOKIE_SECURE=true` + HTTPS.

## 2) Frontend setup

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

`VITE_API_URL=http://localhost:8000/api`

## 3) Chạy bằng Docker Compose

```bash
docker compose up --build
```

Sau đó chạy migrate:

```bash
docker compose exec backend php artisan migrate
```

## 4) Auth flow

1. `POST /api/auth/register` hoặc `POST /api/auth/login`.
2. Backend set 2 cookie HttpOnly:
   - `access_token` (path `/api`, TTL ngắn)
   - `refresh_token` (path `/api/auth`, TTL dài)
3. Frontend gọi API với `withCredentials: true`.
4. Khi API protected trả `401`, axios interceptor gọi `POST /api/auth/refresh`.
5. Backend rotate refresh token, revoke token cũ, set cặp cookie mới.
6. Frontend retry request cũ 1 lần.
7. Logout (`POST /api/auth/logout`) revoke refresh token và clear cookie.

## 5) Security notes

- Không dùng `localStorage/sessionStorage` cho token.
- Không set `Authorization: Bearer ...` ở frontend.
- JWT không trả về response body.
- `SameSite=Lax` mặc định.
- `supports_credentials=true` và `allowed_origins` không dùng `*`.
- Refresh token rotation + revoke khi logout.

## 6) Test nhanh bằng browser/Postman

- Register/login, kiểm tra tab Cookies có `HttpOnly` token.
- Gọi `GET /api/auth/me` (sau login) phải trả user.
- Chờ access token hết hạn, gọi route protected để verify refresh tự động.
- Logout, gọi lại `/api/auth/me` phải `401`.

## 7) Lệnh kiểm thử

Backend:

```bash
cd backend
php artisan test
```

Frontend:

```bash
cd frontend
npm run type-check
npm run build
```
