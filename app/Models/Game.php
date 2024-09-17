<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
protected $fillable = ['played_at', 'team1_score', 'team2_score'];

public function teams()
{
return $this->belongsToMany(Player::class, 'teams')->withPivot('team');
}

public function ratings()
{
return $this->hasMany(Rating::class);
}
}
