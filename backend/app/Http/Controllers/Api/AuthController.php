<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly JwtService $jwtService) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $this->issueTokens($request, $user, 'Registered successfully');
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }

        return $this->issueTokens($request, $user, 'Login successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $rawRefreshToken = $request->cookie(config('jwt.refresh_cookie_name'));

        if (! $rawRefreshToken) {
            return response()->json(['message' => 'Missing refresh token'], 401);
        }

        $tokenHash = $this->jwtService->hashRefreshToken($rawRefreshToken);
        $storedToken = RefreshToken::query()
            ->where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $storedToken) {
            return response()->json(['message' => 'Invalid refresh token'], 401);
        }

        $storedToken->update(['revoked_at' => now()]);

        return $this->issueTokens($request, $storedToken->user, 'Token refreshed');
    }

    public function logout(Request $request): JsonResponse
    {
        $rawRefreshToken = $request->cookie(config('jwt.refresh_cookie_name'));

        if ($rawRefreshToken) {
            RefreshToken::query()
                ->where('token_hash', $this->jwtService->hashRefreshToken($rawRefreshToken))
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }

        return response()
            ->json(['message' => 'Logged out'])
            ->withCookie($this->forgetAccessCookie())
            ->withCookie($this->forgetRefreshCookie());
    }

    private function issueTokens(Request $request, User $user, string $message): JsonResponse
    {
        $accessToken = $this->jwtService->makeAccessToken($user);
        $refreshToken = $this->jwtService->makeRefreshToken();

        RefreshToken::query()->create([
            'user_id' => $user->id,
            'token_hash' => $this->jwtService->hashRefreshToken($refreshToken),
            'expires_at' => now()->addDays(config('jwt.refresh_ttl_days')),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return response()
            ->json([
                'message' => $message,
                'user' => $this->userPayload($user),
            ])
            ->withCookie($this->accessCookie($accessToken))
            ->withCookie($this->refreshCookie($refreshToken));
    }

    private function accessCookie(string $token)
    {
        return cookie(
            name: config('jwt.access_cookie_name'),
            value: $token,
            minutes: config('jwt.access_ttl_minutes'),
            path: '/api',
            domain: null,
            secure: config('jwt.cookie_secure'),
            httpOnly: true,
            raw: false,
            sameSite: config('jwt.cookie_same_site')
        );
    }

    private function refreshCookie(string $token)
    {
        return cookie(
            name: config('jwt.refresh_cookie_name'),
            value: $token,
            minutes: 60 * 24 * config('jwt.refresh_ttl_days'),
            path: '/api/auth',
            domain: null,
            secure: config('jwt.cookie_secure'),
            httpOnly: true,
            raw: false,
            sameSite: config('jwt.cookie_same_site')
        );
    }

    private function forgetAccessCookie()
    {
        return Cookie::forget(config('jwt.access_cookie_name'), '/api');
    }

    private function forgetRefreshCookie()
    {
        return Cookie::forget(config('jwt.refresh_cookie_name'), '/api/auth');
    }

    private function userPayload(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
