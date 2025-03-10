<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use softDeletes;

    protected $fillable = ['account_id', 'player_id', 'amount', 'date'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
