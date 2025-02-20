<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingRequest extends Model
{
    protected $fillable = ['game_id', 'player_id'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class)->withTrashed();
    }
}
