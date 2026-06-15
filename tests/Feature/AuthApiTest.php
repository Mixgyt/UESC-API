<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_auth_token_with_valid_credentials_returns_token(): void
    {
        User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@regtest.local',
            'password' => Hash::make('secret'),
        ]);

        $this->postJson('/api/auth/token', [
            'email' => 'admin@regtest.local',
            'password' => 'secret',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_post_auth_token_with_invalid_credentials_returns_422(): void
    {
        User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@regtest.local',
            'password' => Hash::make('secret'),
        ]);

        $this->postJson('/api/auth/token', [
            'email' => 'admin@regtest.local',
            'password' => 'wrong',
        ])->assertStatus(422);
    }

    public function test_delete_auth_token_revokes_current_token(): void
    {
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@regtest.local',
            'password' => Hash::make('secret'),
        ]);

        $token = $user->createToken('default')->plainTextToken;

        $this->withToken($token)
            ->deleteJson('/api/auth/token')
            ->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
