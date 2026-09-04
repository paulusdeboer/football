<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingRequestEvent extends Model
{
    protected $fillable = [
        'rating_request_id',
        'actor_user_id',
        'previous_player_id',
        'new_player_id',
        'type',
        'expires_at',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function request()
    {
        return $this->belongsTo(RatingRequest::class, 'rating_request_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id')->withTrashed();
    }

    public function previousPlayer()
    {
        return $this->belongsTo(Player::class, 'previous_player_id')->withTrashed();
    }

    public function newPlayer()
    {
        return $this->belongsTo(Player::class, 'new_player_id')->withTrashed();
    }
}
