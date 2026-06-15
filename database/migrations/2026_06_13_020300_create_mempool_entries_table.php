<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mempool_entries', function (Blueprint $table) {
            $table->string('txid', 64)->primary();
            $table->unsignedBigInteger('fee')->default(0);
            $table->unsignedInteger('vsize')->default(0);
            $table->double('fee_rate')->default(0);
            $table->json('depends')->nullable();
            $table->timestamp('time');
            $table->json('raw')->nullable();
            $table->timestamps();

            $table->index('fee_rate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mempool_entries');
    }
};
