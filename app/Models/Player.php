<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Setting;

class Player extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'rating', 'type', 'balance', 'balance_holder_id', 'user_id'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($player) {
            if ($player->user) {
                $player->user->delete();
            }
        });

        static::restoring(function ($player) {
            $user = User::withTrashed()->find($player->user_id);

            if ($user) {
                $user->restore();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Game::class, 'teams')->withPivot('team');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'rated_player_id');
    }

    public function gamePlayerRatings()
    {
        return $this->hasMany(GamePlayerRating::class);
    }
    
    /**
     * Get player balance in games instead of euros
     * 
     * @return float
     */
    public function getBalanceInGames()
    {
        $costPerGame = Setting::getValue('cost_per_game', 4.00);
        
        return $costPerGame > 0 ? $this->balance / $costPerGame : 0;
    }

    public function balanceHolder()
    {
        return $this->belongsTo(Player::class, 'balance_holder_id');
    }
}
