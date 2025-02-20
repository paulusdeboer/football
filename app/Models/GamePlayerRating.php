<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamePlayerRating extends Model
{
    protected $fillable = ['game_id', 'player_id', 'rating', 'type'];

    public function player()
    {
        return $this->belongsTo(Player::class)->withTrashed();
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
