<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::rename('rating_history', 'game_player_ratings');
    }

    public function down()
    {
        Schema::rename('game_player_ratings', 'rating_history');
    }

};
