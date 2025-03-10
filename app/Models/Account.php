<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory;
    use softDeletes;

    protected $fillable = ['name', 'balance'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
