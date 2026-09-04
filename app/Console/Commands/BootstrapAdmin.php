<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class BootstrapAdmin extends Command
{
    protected $signature = 'users:bootstrap-admin {name : The existing user name} {email : The existing user email}';

    protected $description = 'Promote an existing user to the administrator role during deployment';

    public function handle(): int
    {
        $user = User::query()
            ->where('name', $this->argument('name'))
            ->where('email', $this->argument('email'))
            ->first();

        if (! $user) {
            $this->error('No active user found with that name and email address.');

            return self::FAILURE;
        }

        $user->update(['role' => User::ROLE_ADMIN]);
        $this->info("{$user->email} is now an administrator.");

        return self::SUCCESS;
    }
}
