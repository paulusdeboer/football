<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['played_at', 'team1_score', 'team2_score'];

    public function canEditResult(): bool
    {
        $latestId = self::query()
            ->whereNotNull('team1_score')
            ->whereNotNull('team2_score')
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->value('id');

        return (int) $this->id === (int) $latestId;
    }

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
