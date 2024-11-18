<?php

namespace Database\Seeders\Migrate;

use App\Models\Player;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class PlayersMigrateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(ConnectionInterface $connection): void
    {
        $players = $connection
            ->table('spelers', 's')
            ->select([
                's.*',
                'w.team',
                'w.score',
                'u.voor',
                'u.tegen',
            ])
            ->leftJoin('wedstrijd as w', 's.id', '=', 'w.speler_id')
            ->leftJoin('uitslag as u', 'w.datum', '=', 'u.datum')
            ->orderBy('w.datum', 'DESC');

        dump($players);die;

        if ($players->isEmpty()) {
            $this->command->info('No players found in the old database.');
            return;
        }

        $this->command->info('Found ' . $players->count() . ' players. Starting migration...');

        $userData = [];
        $playerData = [];
        $lastUserId = User::max('id') ?? 0;

        foreach ($players as $index => $oldPlayer) {
            $newUserId = $lastUserId + $index + 1;

            $userData[] = [
                'id' => $newUserId,
                'name' => $oldPlayer->naam,
                'email' => $oldPlayer->email ?? "user{$newUserId}@example.com",
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $score = isset($oldPlayer->score) ? $oldPlayer->score * 100 : 700;
            $type = match ($oldPlayer->type) {
                'A' => 'attacker',
                'D' => 'defender',
                default => 'both',
            };

            $playerData[] = [
                'name' => $oldPlayer->naam,
                'rating' => $score,
                'type' => $type,
                'user_id' => $newUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($userData)) {
            User::insert($userData);
            $this->command->info(count($userData) . ' users migrated successfully.');
        }

        if (!empty($playerData)) {
            Player::insert($playerData);
            $this->command->info(count($playerData) . ' players migrated successfully.');
        }

        $this->command->info('Player migration completed.');
    }
}
