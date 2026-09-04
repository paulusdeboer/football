<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['played_at', 'team1_score', 'team2_score'];

    public function isInPast(): bool
    {
        return Carbon::parse($this->played_at)->isPast();
    }

    public function isLatestCompleted(): bool
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
