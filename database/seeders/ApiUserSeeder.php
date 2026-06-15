<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApiUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@regtest.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('secret'),
            ]
        );

        $token = $user->createToken('default')->plainTextToken;

        $this->command?->info("Token: {$token}");
    }
}
