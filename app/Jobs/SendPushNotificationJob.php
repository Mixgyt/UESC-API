<?php

namespace App\Jobs;

use App\Models\DeviceToken;
use App\Services\FirebaseService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
        public string $title,
        public string $body,
        public array $data = []
    ) {
    }

    public function handle(FirebaseService $firebase): void
    {
        try {
            $firebase->sendPush($this->token, $this->title, $this->body, $this->data);
            Log::info("Push notification sent successfully to token: " . substr($this->token, 0, 15) . "...");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;
            $body = $response ? $response->getBody()->getContents() : '';

            Log::error("FCM API error ({$statusCode}): {$body}");

            if ($statusCode === 404 || str_contains($body, 'UNREGISTERED')) {
                Log::warning("Token is unregistered or invalid. Deleting from database: " . substr($this->token, 0, 15) . "...");
                DeviceToken::query()->where('token', $this->token)->delete();
            }
        } catch (Throwable $e) {
            Log::error("Failed to send push notification: " . $e->getMessage());
            throw $e;
        }
    }
}
