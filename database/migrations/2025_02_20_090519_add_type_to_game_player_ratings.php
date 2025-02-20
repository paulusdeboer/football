<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('game_player_ratings', function (Blueprint $table) {
            $table->string('type')->after('rating')->nullable();
        });
    }

    public function down()
    {
        Schema::table('game_player_ratings', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
