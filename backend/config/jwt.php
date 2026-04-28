<?php

return [
    'access_secret' => env('JWT_ACCESS_SECRET'),
    'access_ttl_minutes' => (int) env('JWT_ACCESS_TTL_MINUTES', 15),
    'refresh_ttl_days' => (int) env('JWT_REFRESH_TTL_DAYS', 30),

    'cookie_secure' => filter_var(env('JWT_COOKIE_SECURE', false), FILTER_VALIDATE_BOOL),
    'cookie_same_site' => env('JWT_COOKIE_SAME_SITE', 'lax'),

    'access_cookie_name' => 'access_token',
    'refresh_cookie_name' => 'refresh_token',
];
