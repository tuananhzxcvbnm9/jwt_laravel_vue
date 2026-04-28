<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class JwtCookieAuthenticate
{
    public function __construct(private readonly JwtService $jwtService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie(config('jwt.access_cookie_name'));

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            $payload = $this->jwtService->parseAccessToken($token);
        } catch (Throwable) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        $user = User::query()->find($payload->sub ?? null);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn (): User => $user);

        return $next($request);
    }
}
