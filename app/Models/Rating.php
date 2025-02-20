<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = ['game_id', 'rated_player_id', 'rating_player_id', 'rating_value'];

    public function ratedPlayer()
    {
        return $this->belongsTo(Player::class, 'rated_player_id')->withTrashed();
    }

    public function ratingPlayer()
    {
        return $this->belongsTo(Player::class, 'rating_player_id')->withTrashed();
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
