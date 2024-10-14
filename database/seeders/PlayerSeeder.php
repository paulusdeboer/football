<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\User;
use Database\Seeders\Helper\BaseSeeder;
use Illuminate\Support\Facades\Hash;

class PlayerSeeder extends BaseSeeder
{
    private const PLAYER_COUNT = 50;

    /**
     * Run the database seeds.
     */
    public function execute(): void
    {
        $lastPlayerId = Player::max('id') ?? 0;
        $lastUserId = User::max('id') ?? 0;

        for ($i = 0; $i < self::PLAYER_COUNT; $i++) {
            $this->add([
                'id' => $lastUserId + $i + 1,
                'name' => $this->fakerService->name(),
                'email' => $this->fakerService->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'created_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', 'Europe/Amsterdam'),
                'updated_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', 'Europe/Amsterdam'),
            ], User::class);
        }

        $users = User::all();

        foreach ($users as $user) {
            $this->add([
                'id' => $lastPlayerId + $i + 1,
                'name' => $user->name,
                'rating' => $this->fakerService->numberBetween(500, 1000),
                'type' => $this->fakerService->randomElement(['attacker', 'defender', 'both']),
                'user_id' => $user->id,
                'created_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', 'Europe/Amsterdam'),
                'updated_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', 'Europe/Amsterdam'),
            ], Player::class);
        }
    }
}
