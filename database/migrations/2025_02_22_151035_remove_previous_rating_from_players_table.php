<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('previous_rating');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->timestamp('previous_rating')->nullable();
        });
    }
};
