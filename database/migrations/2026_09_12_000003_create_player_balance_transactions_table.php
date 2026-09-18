<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_balance_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->restrictOnDelete();
            $table->foreignId('source_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('game_id')->nullable()->constrained('games')->nullOnDelete();
            $table->string('type', 32);
            $table->bigInteger('amount_cents');
            $table->date('occurred_on');
            $table->unsignedInteger('units')->nullable();
            $table->unsignedInteger('unit_price_cents')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['player_id', 'occurred_on']);
            $table->index(['game_id', 'type']);
            $table->unique(['game_id', 'source_player_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_balance_transactions');
    }
};
