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
        $lastUserId = User::max('id') ?? 0;
        $userData = [];
        $ids = [];

        for ($i = 0; $i < self::PLAYER_COUNT; $i++) {
            $ids[] = $lastUserId + $i + 1;
            $userData[] = [
                'name' => $this->fakerService->name(),
                'email' => $this->fakerService->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'created_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', 'Europe/Amsterdam'),
                'updated_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', 'Europe/Amsterdam'),
            ];
        }

        User::insert($userData);
        $users = User::whereIn('id', $ids)->get();

        $playerData = [];

        foreach ($users as $user) {
            $playerData[] = [
                'name' => $user->name,
                'rating' => $this->fakerService->numberBetween(500, 1000),
                'type' => $this->fakerService->randomElement(['attacker', 'defender', 'both']),
                'user_id' => $user->id,
                'created_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', 'Europe/Amsterdam'),
                'updated_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', 'Europe/Amsterdam'),
            ];
        }

        Player::insert($playerData);
    }
}
