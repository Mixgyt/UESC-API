<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->string('hash', 64)->primary();
            $table->unsignedInteger('height')->unique()->index();
            $table->timestamp('time');
            $table->unsignedInteger('tx_count')->default(0);
            $table->unsignedInteger('size')->default(0);
            $table->unsignedInteger('weight')->default(0);
            $table->double('difficulty')->default(0);
            $table->unsignedBigInteger('miner_reward')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
