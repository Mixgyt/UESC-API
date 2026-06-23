<?php

namespace Tests\Feature;

use App\Jobs\SendPushNotificationJob;
use App\Jobs\SyncBlockchainJob;
use App\Models\DeviceToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DeviceTokenNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_and_unregister_device_tokens(): void
    {
        // 1. Test registration
        $response = $this->postJson('/api/devices/register', [
            'token' => 'test-device-token-123',
            'address' => 'bc1qtestaddress123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.token', 'test-device-token-123')
            ->assertJsonPath('data.address', 'bc1qtestaddress123');

        $this->assertDatabaseHas('device_tokens', [
            'token' => 'test-device-token-123',
            'address' => 'bc1qtestaddress123',
        ]);

        // 2. Test duplicate registration (should return 201 and not crash)
        $responseDuplicate = $this->postJson('/api/devices/register', [
            'token' => 'test-device-token-123',
            'address' => 'bc1qtestaddress123',
        ]);
        $responseDuplicate->assertStatus(201);

        // 3. Test unregistration
        $responseUnregister = $this->postJson('/api/devices/unregister', [
            'token' => 'test-device-token-123',
            'address' => 'bc1qtestaddress123',
        ]);

        $responseUnregister->assertStatus(200)
            ->assertJsonPath('message', 'Device token unregistered successfully.');

        $this->assertDatabaseMissing('device_tokens', [
            'token' => 'test-device-token-123',
            'address' => 'bc1qtestaddress123',
        ]);
    }

    public function test_sync_blockchain_job_dispatches_push_notification_on_matching_address(): void
    {
        Queue::fake();

        // Register a device token for a specific address
        $address = 'bc1qrecipientaddress456';
        DeviceToken::query()->create([
            'token' => 'target-device-token',
            'address' => $address,
        ]);

        $blockHash = str_repeat('a', 64);
        $txid = str_repeat('d', 64);

        $this->mockRpc(function ($mock) use ($blockHash, $txid, $address): void {
            $mock->shouldReceive('getBlockCount')->andReturn(0);
            $mock->shouldReceive('getBlockHash')->with(0)->andReturn($blockHash);
            $mock->shouldReceive('getBlock')->with($blockHash, 2)->andReturn([
                'hash' => $blockHash,
                'height' => 0,
                'time' => 1_700_000_000,
                'tx' => [
                    [
                        'txid' => $txid,
                        'vin' => [[]],
                        'vout' => [
                            [
                                'value' => 1.5,
                                'scriptPubKey' => [
                                    'address' => $address,
                                ],
                            ],
                        ],
                    ],
                ],
                'size' => 250,
                'weight' => 1000,
                'difficulty' => 1.0,
            ]);
            $mock->shouldReceive('getRawMempool')->with(true)->andReturn([]);
        });

        // Run the blockchain sync job
        app(SyncBlockchainJob::class)->handle(app(\App\Services\BitcoinRpcService::class));

        // Assert that the push notification job was dispatched
        Queue::assertPushed(SendPushNotificationJob::class, function (SendPushNotificationJob $job) use ($address, $txid): bool {
            return $job->token === 'target-device-token'
                && $job->title === 'Transacción Confirmada'
                && str_contains($job->body, '1.5 BTC')
                && $job->data['txid'] === $txid
                && $job->data['address'] === $address
                && $job->data['amount_btc'] === '1.5'
                && $job->data['amount_sat'] === '150000000';
        });
    }

    public function test_firebase_service_can_load_credentials_from_base64_env(): void
    {
        $mockJson = json_encode([
            'project_id' => 'env-project-123',
            'private_key' => 'env-private-key-456',
            'client_email' => 'env-email-789@gserviceaccount.com',
        ]);
        
        $base64 = base64_encode($mockJson);
        
        putenv("FIREBASE_CREDENTIALS_BASE64={$base64}");
        
        $service = new \App\Services\FirebaseService();
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getCredentials');
        $method->setAccessible(true);
        $credentials = $method->invoke($service);
        
        $this->assertEquals('env-project-123', $credentials['project_id']);
        $this->assertEquals('env-private-key-456', $credentials['private_key']);
        $this->assertEquals('env-email-789@gserviceaccount.com', $credentials['client_email']);
        
        putenv("FIREBASE_CREDENTIALS_BASE64");
    }
}
