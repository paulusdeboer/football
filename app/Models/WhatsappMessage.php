<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['attempted_at' => 'datetime'];
    }

    public function displayStatus(): string
    {
        // A process may have stopped after handing the request to the provider.
        if ($this->status === 'sending' && $this->attempted_at?->lt(now()->subSeconds(30))) {
            return 'uncertain';
        }

        return $this->status;
    }
}
