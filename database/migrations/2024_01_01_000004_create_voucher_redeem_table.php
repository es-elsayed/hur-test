<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voucher_redeem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('member')->constrained('members')->onDelete('cascade');
            $table->boolean('redeem')->default(false);
            $table->json('projects')->nullable()->default(json_encode([]));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_redeem');
    }
};

