<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

class TeamBuilder
{
    public function build(Collection $players): array
    {
        $attackers = $players->where('type', 'attacker')->sortByDesc('rating')->values();
        $defenders = $players->where('type', 'defender')->sortByDesc('rating')->values();
        $allRounders = $players->where('type', 'both')->sortByDesc('rating')->values();
        $team1 = collect();
        $team2 = collect();

        $this->balanceAttackersAndDefenders($attackers, $defenders, $allRounders);
        $this->distributeAttackersAndDefenders($team1, $team2, $attackers, $defenders);
        $this->distributeDraftStyle($team1, $team2, $allRounders);
        $this->optimizeTeams($team1, $team2);

        return ['team1' => $team1, 'team2' => $team2];
    }

    private function balanceAttackersAndDefenders(&$attackers, &$defenders, $allRounders): void
    {
        if ($attackers->count() % 2 !== 0 && $allRounders->isNotEmpty()) {
            $attackers->push($allRounders->pop());
            $attackers = $attackers->sortByDesc('rating')->values();
        }

        if ($defenders->count() % 2 !== 0 && $allRounders->isNotEmpty()) {
            $defenders->push($allRounders->pop());
            $defenders = $defenders->sortByDesc('rating')->values();
        }
    }

    private function distributeAttackersAndDefenders($team1, $team2, &$attackers, &$defenders): void
    {
        $attackers = $this->checkAndDistributePlayers($team1, $team2, $attackers);
        $defenders = $this->checkAndDistributePlayers($team1, $team2, $defenders);

        $remainingPlayers = $attackers->merge($defenders);
        if ($remainingPlayers->isNotEmpty()) {
            $this->distributeDraftStyle($team1, $team2, $remainingPlayers);
        }
    }

    private function checkAndDistributePlayers($team1, $team2, $players): Collection
    {
        if ($players->count() >= 2) {
            $this->distributeDraftStyle($team1, $team2, $players->count() === 2
                ? $players
                : $players->slice(0, 2));

            return $players->slice(2);
        }

        return $players;
    }

    private function distributeDraftStyle($team1, $team2, $players): void
    {
        foreach ($players as $player) {
            if ($team1->count() < $team2->count() || $team1->sum('rating') < $team2->sum('rating')) {
                $team1->push($player);
            } else {
                $team2->push($player);
            }
        }
    }

    private function optimizeTeams(&$team1, &$team2): void
    {
        $swappedWith = [];
        $madeSwap = true;
        $tolerance = 20;

        while ($madeSwap) {
            $madeSwap = false;
            $bestSwap = null;
            $team1Rating = $team1->sum('rating');
            $team2Rating = $team2->sum('rating');

            if (abs($team1Rating - $team2Rating) <= $tolerance) {
                break;
            }

            foreach ($team1 as $player1) {
                foreach ($team2 as $player2) {
                    if ($player1->type !== $player2->type) {
                        continue;
                    }

                    $newTeam1Rating = $team1Rating - $player1->rating + $player2->rating;
                    $newTeam2Rating = $team2Rating - $player2->rating + $player1->rating;

                    if (abs($newTeam1Rating - $newTeam2Rating) < abs($team1Rating - $team2Rating)
                        && ! in_array($player2->id, $swappedWith, true)) {
                        if (! $bestSwap || abs($newTeam1Rating - $newTeam2Rating) < abs($bestSwap['ratingDiff'])) {
                            $bestSwap = [
                                'player1' => $player1,
                                'player2' => $player2,
                                'ratingDiff' => $newTeam1Rating - $newTeam2Rating,
                            ];
                        }
                    }
                }
            }

            if ($bestSwap) {
                $team1 = $team1->filter(fn ($player) => $player->id !== $bestSwap['player1']->id);
                $team2 = $team2->filter(fn ($player) => $player->id !== $bestSwap['player2']->id);
                $team1->push($bestSwap['player2']);
                $team2->push($bestSwap['player1']);
                $swappedWith[] = $bestSwap['player2']->id;
                $madeSwap = true;
            }
        }
    }
}
