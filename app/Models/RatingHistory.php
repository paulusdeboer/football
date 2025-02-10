<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingHistory extends Model
{
    protected $fillable = ['player_id', 'rating', 'game_id'];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
