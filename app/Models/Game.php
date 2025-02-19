<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Game extends Model
{
    use SoftDeletes;

    protected $fillable = ['played_at', 'team1_score', 'team2_score'];

    public function teams()
    {
        return $this->belongsToMany(Player::class, 'teams')->withPivot('team');
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
