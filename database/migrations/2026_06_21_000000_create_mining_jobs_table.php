<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mining_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('height')->index();
            $table->string('previous_block_hash', 64)->index();
            $table->string('challenge', 64);
            $table->string('target_prefix', 32);
            $table->string('status', 16)->default('open')->index();
            $table->string('winner_address')->nullable();
            $table->string('winning_nonce', 64)->nullable();
            $table->string('winning_hash', 64)->nullable();
            $table->string('block_hash', 64)->nullable()->index();
            $table->unsignedBigInteger('reward_sats')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('solved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mining_jobs');
    }
};
