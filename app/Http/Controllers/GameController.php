<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\RatingRequestMail;

class GameController extends Controller
{
    public function create()
    {
        // Fetch 12 random players
        $players = Player::inRandomOrder()->limit(12)->get();

        // Logic to balance teams can be added here

        $team1 = $players->slice(0, 6);  // first 6 players
        $team2 = $players->slice(6, 6);  // next 6 players

        // Create a new game
        $game = Game::create(['played_at' => now()]);

        // Assign players to teams
        foreach ($team1 as $player) {
            $game->teams()->create(['player_id' => $player->id, 'team' => 'team1']);
        }
        foreach ($team2 as $player) {
            $game->teams()->create(['player_id' => $player->id, 'team' => 'team2']);
        }

        return redirect()->route('games.show', $game);
    }

    public function storeResult(Request $request, Game $game)
    {
        $game->update([
            'team1_score' => $request->team1_score,
            'team2_score' => $request->team2_score,
        ]);

        // Adjust ratings based on the result (Implement logic)
        $this->adjustRatings($game);

        // Send emails to 3 random players to rate others
        $this->sendRatingRequests($game);

        return redirect()->route('games.show', $game);
    }

    private function adjustRatings(Game $game)
    {
        // Logic to adjust player ratings based on game result
    }

    private function sendRatingRequests(Game $game)
    {
        // Pick 3 random players and send them email requests to rate others
        $players = $game->teams()->inRandomOrder()->limit(3)->get();
        foreach ($players as $player) {
            $tokenUrl = URL::temporarySignedRoute(
                'players.rate', now()->addHours(24), ['game' => $game->id, 'player' => $player->id]
            );

            Mail::to($player->user->email)->send(new RatingRequestMail($game, $tokenUrl));
        }
    }
}
