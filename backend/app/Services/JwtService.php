<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use RuntimeException;

class JwtService
{
    public function makeAccessToken(User $user): string
    {
        $now = time();
        $ttl = config('jwt.access_ttl_minutes') * 60;

        $payload = [
            'iss' => config('app.url'),
            'sub' => (string) $user->id,
            'type' => 'access',
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
        ];

        return JWT::encode($payload, (string) config('jwt.access_secret'), 'HS256');
    }

    public function parseAccessToken(string $token): object
    {
        $payload = JWT::decode(
            $token,
            new Key((string) config('jwt.access_secret'), 'HS256')
        );

        if (($payload->type ?? null) !== 'access') {
            throw new RuntimeException('Invalid token type.');
        }

        return $payload;
    }

    public function makeRefreshToken(): string
    {
        return Str::random(80);
    }

    public function hashRefreshToken(string $token): string
    {
        return hash_hmac('sha256', $token, (string) config('app.key'));
    }
}
