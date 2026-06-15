<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('txid', 64)->primary();
            $table->string('block_hash', 64)->nullable()->index();
            $table->unsignedInteger('block_height')->nullable()->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('fee')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->unsignedInteger('vsize')->default(0);
            $table->unsignedInteger('input_count')->default(0);
            $table->unsignedInteger('output_count')->default(0);
            $table->unsignedBigInteger('total_output_sat')->default(0);
            $table->json('raw')->nullable();
            $table->timestamps();

            $table->foreign('block_hash')->references('hash')->on('blocks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
