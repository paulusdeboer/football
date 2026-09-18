<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['played_at', 'fee_cents', 'team1_score', 'team2_score'];

    protected function casts(): array
    {
        return [
            'fee_cents' => 'integer',
        ];
    }

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

    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(PlayerBalanceTransaction::class);
    }
}
