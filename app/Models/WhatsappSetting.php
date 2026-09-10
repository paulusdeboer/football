<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappSetting extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return ['token' => 'encrypted', 'enabled' => 'boolean', 'checked_at' => 'datetime'];
    }

    public static function current(): self
    {
        return self::findOrFail(1);
    }

    public function ready(): bool
    {
        return $this->enabled && filled($this->token) && filled($this->group_id);
    }

    public function publicData(): array
    {
        return $this->only(['enabled', 'group_id', 'group_name', 'phone', 'connection_status', 'checked_at', 'version'])
            + ['has_token' => filled($this->token)];
    }
}
