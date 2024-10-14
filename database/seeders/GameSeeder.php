<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Player;
use Database\Seeders\Helper\BaseSeeder;
use DateTimeZone;

class GameSeeder extends BaseSeeder
{
    private const GAME_COUNT = 30;
    private const PLAYERS_PER_TEAM = 6;

    /**
     * Run the database seeds.
     */
    public function execute(): void
    {
        $players = Player::all();

        if ($players->count() < (self::PLAYERS_PER_TEAM * 2)) {
            $this->command->info('Not enough players to create games.');
            return;
        }

        $lastGameId = Game::max('id') ?? 0;

        $gameData = [];
        $ids = [];
        $timezone = new DateTimeZone('Europe/Amsterdam');

        for ($i = 0; $i < self::GAME_COUNT; $i++) {
            $ids[] = $lastGameId + $i + 1;
            $gameData[] = [
                'played_at' => $this->fakerService->dateTimeBetween('-1 year'),
                'team1_score' => $this->fakerService->numberBetween(0, 10),
                'team2_score' => $this->fakerService->numberBetween(0, 10),
                'created_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', $timezone),
                'updated_at' => $this->fakerService->dateTimeBetween('-1 year', 'now', $timezone),
            ];
        }

        Game::insert($gameData);
        $games = Game::whereIn('id', $ids)->get();

        foreach ($games as $game) {
            $selectedPlayers = $players->random(self::PLAYERS_PER_TEAM * 2);

            $team1 = $selectedPlayers->slice(0, self::PLAYERS_PER_TEAM);
            $team2 = $selectedPlayers->slice(self::PLAYERS_PER_TEAM);

            foreach ($team1 as $player) {
                $game->teams()->attach($player->id, ['team' => 1]);
            }

            foreach ($team2 as $player) {
                $game->teams()->attach($player->id, ['team' => 2]);
            }
        }
    }
}
