<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\User;
use Database\Seeders\Helper\BaseSeeder;

class PlayerSeeder extends BaseSeeder
{
    private const PLAYER_COUNT = 50;

    /**
     * Run the database seeds.
     */
    public function execute(): void
    {
        $lastPlayerId = Player::max('id') ?? 0;
        $userIdMax = User::max('id') ?? 0;

        for ($i = 0; $i < self::PLAYER_COUNT; $i++) {
            $this->add([
                'id' => $lastPlayerId + $i + 1,
                'name' => $this->fakerService->name(),
                'rating' => $this->fakerService->numberBetween(500, 1000),
                'type' => $this->fakerService->randomElement(['attacker', 'defender', 'both']),
                'user_id' => $this->fakerService->numberBetween(1, $userIdMax),
                'created_at' => $this->fakerService->dateTimeBetween('-1 year'),
                'updated_at' => $this->fakerService->dateTimeBetween('-1 year'),
            ], Player::class);
        }
    }
}
