<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['played_at', 'team1_score', 'team2_score'];

    public function teams()
    {
        return $this->belongsToMany(Player::class, 'teams')->withPivot('team')->withTrashed();
    }

    public function ratingRequests()
    {
        return $this->hasMany(RatingRequest::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function gamePlayerRatings()
    {
        return $this->hasMany(GamePlayerRating::class);
    }
}
