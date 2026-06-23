<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class FirebaseService
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client();
    }

    /**
     * Get Firebase Service Account credentials.
     */
    private function getCredentials(): array
    {
        $path = env('FIREBASE_CREDENTIALS_PATH', base_path('google-services.json'));
        
        if (!file_exists($path)) {
            $fallbackPath = base_path('firebase-service-account.json');
            if (file_exists($fallbackPath)) {
                $path = $fallbackPath;
            } else {
                throw new RuntimeException("Firebase service account credentials file not found at: {$path}");
            }
        }

        $content = json_decode(file_get_contents($path), true);
        if (!$content || !isset($content['private_key']) || !isset($content['client_email']) || !isset($content['project_id'])) {
            throw new RuntimeException("Invalid Firebase credentials file. Must contain project_id, private_key, and client_email.");
        }

        return $content;
    }

    /**
     * Generate an OAuth2 access token for Google API.
     */
    public function getAccessToken(): string
    {
        return Cache::remember('firebase.access_token', 3300, function (): string {
            $credentials = $this->getCredentials();

            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claim = json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]);

            $base64UrlHeader = $this->base64UrlEncode($header);
            $base64UrlClaim = $this->base64UrlEncode($claim);

            $signatureInput = $base64UrlHeader . '.' . $base64UrlClaim;
            $signature = '';

            $privateKey = $credentials['private_key'];
            if (!openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Failed to sign JWT for Firebase OAuth2.');
            }

            $base64UrlSignature = $this->base64UrlEncode($signature);
            $jwt = $signatureInput . '.' . $base64UrlSignature;

            $response = $this->http->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            if (!isset($data['access_token'])) {
                throw new RuntimeException('Failed to obtain Firebase access token: ' . json_encode($data));
            }

            return $data['access_token'];
        });
    }

    /**
     * Send push notification using FCM v1 API.
     */
    public function sendPush(string $token, string $title, string $body, array $data = []): array
    {
        $credentials = $this->getCredentials();
        $projectId = $credentials['project_id'];
        $accessToken = $this->getAccessToken();

        // Convert all elements in data to string as FCM v1 requires string values for data keys
        $stringData = [];
        foreach ($data as $key => $value) {
            $stringData[(string) $key] = (string) $value;
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ]
        ];

        if (!empty($stringData)) {
            $payload['message']['data'] = $stringData;
        }

        $response = $this->http->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
