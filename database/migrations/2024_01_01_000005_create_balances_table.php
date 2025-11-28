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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member')->constrained('members')->onDelete('cascade');
            $table->enum('process', ['income', 'outcome']);
            $table->decimal('amount', 10, 2);
            $table->foreignId('project')->constrained('projects')->onDelete('cascade');
            $table->enum('action', ['complete', 'un-complete'])->default('un-complete');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};

