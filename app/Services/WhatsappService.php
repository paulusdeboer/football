<?php

namespace App\Services;

use App\Models\Game;
use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WhatsappService
{
    public function __construct(private WhapiClient $client) {}

    public function body(Game $game, string $kind): string
    {
        if ($kind === 'rating_requests') {
            return $this->ratingRequestsBody($game, $game->ratingRequests()->with('player.user')->get());
        }

        $header = $kind === 'update' ? 'Gewijzigde indeling' : 'Teamindeling';
        $lines = [$header.' — '.Carbon::parse($game->played_at)->format('d-m-Y')];
        $players = $game->teams()->get();
        $ratings = $game->gamePlayerRatings()->get()->keyBy('player_id');
        foreach (['team1' => 1, 'team2' => 2] as $team => $number) {
            $members = $players->where('pivot.team', $team)->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            $total = $members->sum(fn ($player) => $ratings->get($player->id)?->rating ?? 0);
            $lines[] = '';
            $lines[] = "Spelers in team {$number} ({$total})";
            foreach ($members as $player) {
                $type = match ($ratings->get($player->id)?->type) {
                    'attacker' => 'Aanvaller', 'defender' => 'Verdediger', 'both' => 'Beide', default => 'Onbekend',
                };
                $lines[] = "({$type}) ".preg_replace('/[\r\n]+/u', ' ', $player->name);
            }
        }

        return implode("\n", $lines);
    }

    public function ratingRequestsBody(Game $game, Collection $requests): string
    {
        $lines = [
            'Beoordelingsaanvragen — '.Carbon::parse($game->played_at)->format('d-m-Y'),
            'Uitslag: Team 1 '.($game->team1_score ?? '—').' - '.($game->team2_score ?? '—').' Team 2',
            '',
            'Verzoeken verstuurd naar:',
        ];

        $sortedRequests = $requests->sortBy(fn ($request) => $request->player?->name ?? '', SORT_NATURAL | SORT_FLAG_CASE)->values();
        foreach ($sortedRequests as $request) {
            $name = preg_replace('/[\r\n]+/u', ' ', (string) ($request->player?->name ?? 'Onbekende speler'));
            $email = (string) ($request->player?->user?->email ?? 'onbekend e-mailadres');
            $recipient = "{$name} ({$email})";
            $lines[] = $recipient;
        }

        if ($sortedRequests->isEmpty()) {
            $lines[] = '—';
        }

        return implode("\n", $lines);
    }

    /** Called under the singleton settings lock, inside the game transaction. */
    public function prepare(WhatsappSetting $settings, ?Game $game, int $actor, string $key, string $kind, string $hash, ?int $retryOf = null, ?string $body = null): WhatsappMessage
    {
        return WhatsappMessage::create([
            'action_key' => $key, 'game_id' => $game?->id, 'actor_user_id' => $actor,
            'retry_of_id' => $retryOf, 'kind' => $kind, 'request_hash' => $hash,
            'destination' => $settings->group_id, 'settings_version' => $settings->version,
            'body' => $body ?? ($game ? $this->body($game, $kind) : 'Testbericht van Vrijdag voetbal. De WhatsApp-koppeling werkt.'),
        ]);
    }

    public function existing(string $key, int $actor, string $hash): ?WhatsappMessage
    {
        $message = WhatsappMessage::where('action_key', $key)->first();
        if ($message && ($message->actor_user_id !== $actor || $message->request_hash !== $hash)) {
            throw ValidationException::withMessages(['whatsapp' => __('whatsapp.changed_request')]);
        }

        return $message;
    }

    public function current(WhatsappMessage $message, WhatsappSetting $settings): bool
    {
        if ($message->settings_version !== $settings->version || $message->destination !== $settings->group_id) {
            return false;
        }
        if ($message->game_id) {
            $game = Game::find($message->game_id);

            return $game && $message->body === $this->body($game, $message->kind);
        }

        return true;
    }

    public function dispatch(WhatsappMessage $message): WhatsappMessage
    {
        // Persist the claim BEFORE network I/O. A crashed process is uncertain,
        // never an invitation for an automatic second send.
        $token = DB::transaction(function () use ($message) {
            $settings = WhatsappSetting::lockForUpdate()->findOrFail(1);
            $message->refresh();
            if ($message->status !== 'prepared') {
                return null;
            }
            if (! $this->current($message, $settings)) {
                $message->update(['status' => 'failed', 'error_code' => 'stale']);

                return null;
            }
            if (! $settings->token || ! $settings->group_id || ($message->kind !== 'test' && ! $settings->ready())) {
                $message->update(['status' => 'failed', 'error_code' => 'not_configured']);

                return null;
            }
            $message->update(['status' => 'sending', 'attempted_at' => now(), 'error_code' => null]);

            return $settings->token;
        });

        if ($token === null) {
            return $message->fresh();
        }
        try {
            $id = $this->client->send($token, $message->destination, $message->body);
            $message->update(['status' => 'accepted', 'provider_message_id' => $id]);
        } catch (WhapiException $e) {
            $message->update(['status' => $e->uncertain ? 'uncertain' : 'failed', 'error_code' => $e->reason]);
        }

        return $message->fresh();
    }

    public function data(WhatsappMessage $message): array
    {
        $settings = WhatsappSetting::current();

        return [
            'id' => $message->id, 'status' => $message->displayStatus(),
            'at' => ($message->attempted_at ?? $message->created_at)->toIso8601String(),
            'error' => $message->error_code ? __('whatsapp.errors')[$message->error_code] : null,
            'can_retry' => in_array($message->displayStatus(), ['failed', 'uncertain', 'prepared'], true)
                && $settings->ready() && $this->current($message, $settings),
        ];
    }
}
