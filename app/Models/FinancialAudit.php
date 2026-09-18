<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAudit extends Model
{
    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_DELETED = 'deleted';

    protected $fillable = [
        'transaction_id',
        'actor_user_id',
        'action',
        'before_values',
        'after_values',
    ];

    protected function casts(): array
    {
        return [
            'before_values' => 'array',
            'after_values' => 'array',
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(PlayerBalanceTransaction::class, 'transaction_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
