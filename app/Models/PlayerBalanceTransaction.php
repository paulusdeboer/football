<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerBalanceTransaction extends Model
{
    public const TYPE_OPENING_BALANCE = 'opening_balance';

    public const TYPE_TOP_UP = 'top_up';

    public const TYPE_MATCH_CHARGE = 'match_charge';

    protected $fillable = [
        'player_id',
        'source_player_id',
        'game_id',
        'type',
        'amount_cents',
        'occurred_on',
        'units',
        'unit_price_cents',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'occurred_on' => 'date',
            'units' => 'integer',
            'unit_price_cents' => 'integer',
        ];
    }

    public function player()
    {
        return $this->belongsTo(Player::class)->withTrashed();
    }

    public function participant()
    {
        return $this->belongsTo(Player::class, 'source_player_id')->withTrashed();
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function audits()
    {
        return $this->hasMany(FinancialAudit::class, 'transaction_id');
    }
}
