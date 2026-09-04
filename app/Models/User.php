<?php

namespace App\Models;

use App\Notifications\PasswordResetNotification;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PLAYER = 'player';

    public const PROTECTED_ADMIN_IDENTITIES = [
        [
            'name' => 'Sjoerd Koffeman',
            'email' => 'skoffeman@live.nl',
        ],
        [
            'name' => 'Paulus de Boer',
            'email' => 'paulusdeboer8@outlook.com',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function player()
    {
        return $this->hasOne(Player::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPlayer(): bool
    {
        return $this->role === self::ROLE_PLAYER;
    }

    /**
     * Use the user id as the password-reset token identifier. The email
     * address is deliberately not used because multiple users may share it.
     */
    public function getEmailForPasswordReset(): string
    {
        return (string) $this->getKey();
    }

    public function isProtectedAdmin(): bool
    {
        foreach (self::PROTECTED_ADMIN_IDENTITIES as $identity) {
            if (strcasecmp($this->name, $identity['name']) === 0
                && strcasecmp($this->email, $identity['email']) === 0) {
                return true;
            }
        }

        return false;
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new PasswordResetNotification($token));
    }
}
