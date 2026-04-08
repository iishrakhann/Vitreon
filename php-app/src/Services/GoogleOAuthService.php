<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

final class GoogleOAuthService
{
    public function authorizationUrl(string $state): string
    {
        $google = Config::get('services.google', []);
        $redirectUri = (string) ($google['redirect_uri'] ?? '');

        if ($redirectUri === '') {
            $redirectUri = rtrim((string) Config::get('services.app.base_url', ''), '/') . '/auth/google/callback';
        }

        $query = http_build_query([
            'client_id' => $google['client_id'] ?? '',
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $query;
    }

    public function fetchUser(string $code): array
    {
        $google = Config::get('services.google', []);
        $redirectUri = (string) ($google['redirect_uri'] ?? '');

        if ($redirectUri === '') {
            $redirectUri = rtrim((string) Config::get('services.app.base_url', ''), '/') . '/auth/google/callback';
        }

        $tokenResponse = $this->postForm('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $google['client_id'] ?? '',
            'client_secret' => $google['client_secret'] ?? '',
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        $accessToken = $tokenResponse['access_token'] ?? null;

        if ($accessToken === null) {
            throw new \RuntimeException('Unable to fetch Google access token.');
        }

        return $this->getJson('https://www.googleapis.com/oauth2/v2/userinfo', [
            'Authorization: Bearer ' . $accessToken,
        ]);
    }

    private function postForm(string $url, array $payload): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($payload),
                'ignore_errors' => true,
                'timeout' => 15,
            ],
        ]);

        $response = file_get_contents($url, false, $context);

        return is_string($response) ? (json_decode($response, true) ?: []) : [];
    }

    private function getJson(string $url, array $headers = []): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers) . "\r\n",
                'ignore_errors' => true,
                'timeout' => 15,
            ],
        ]);

        $response = file_get_contents($url, false, $context);

        return is_string($response) ? (json_decode($response, true) ?: []) : [];
    }
}
