<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_REVOKED = 'revoked';

    public const STATUS_SEND_FAILED = 'send_failed';

    protected $fillable = [
        'game_id', 'player_id', 'status', 'sent_at', 'expires_at',
        'completed_at', 'revoked_at', 'token_version', 'replacement_of_id',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class)->withTrashed();
    }

    public function replacementOf()
    {
        return $this->belongsTo(self::class, 'replacement_of_id');
    }

    public function replacements()
    {
        return $this->hasMany(self::class, 'replacement_of_id');
    }

    public function events()
    {
        return $this->hasMany(RatingRequestEvent::class)->latest();
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_SEND_FAILED], true);
    }

    public function displayStatus(): string
    {
        if ($this->isActive() && $this->expires_at?->isPast()) {
            return self::STATUS_EXPIRED;
        }

        return $this->status;
    }
}
