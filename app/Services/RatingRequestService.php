<?php

namespace App\Services;

use App\Mail\RatingRequestMail;
use App\Models\Game;
use App\Models\Player;
use App\Models\RatingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Throwable;

class RatingRequestService
{
    public const LINK_LIFETIME_HOURS = 72;

    public function createInitialRequests(Game $game): void
    {
        if ($game->ratingRequests()->exists()) {
            return;
        }

        $lastGame = Game::whereNotNull('team1_score')
            ->whereNotNull('team2_score')
            ->where('id', '<', $game->id)
            ->latest('played_at')
            ->first();
        $excludedPlayerIds = $lastGame
            ? $lastGame->ratingRequests()->pluck('player_id')->all()
            : [];

        $players = $game->teams()
            ->whereNotIn('players.id', $excludedPlayerIds)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        foreach ($players as $player) {
            $ratingRequest = $this->createRequest($game, $player);
            $this->send($ratingRequest);
        }
    }

    public function resend(RatingRequest $ratingRequest, int $actorId): void
    {
        $this->ensureGameCanManage($ratingRequest->game);
        $this->ensureCanManage($ratingRequest);

        $now = now();
        $ratingRequest->update([
            'status' => RatingRequest::STATUS_PENDING,
            'sent_at' => $now,
            'expires_at' => $now->copy()->addHours(self::LINK_LIFETIME_HOURS),
            'completed_at' => null,
            'revoked_at' => null,
            'token_version' => $ratingRequest->token_version + 1,
        ]);
        $ratingRequest->events()->create([
            'actor_user_id' => $actorId,
            'previous_player_id' => $ratingRequest->player_id,
            'new_player_id' => $ratingRequest->player_id,
            'type' => 'resend',
            'expires_at' => $ratingRequest->expires_at,
        ]);

        $this->send($ratingRequest, $actorId);
    }

    public function replace(RatingRequest $ratingRequest, ?int $playerId, int $actorId): RatingRequest
    {
        $this->ensureGameCanManage($ratingRequest->game);
        $this->ensureCanManage($ratingRequest);
        $candidates = $this->replacementCandidates($ratingRequest->game, $ratingRequest);
        $replacement = $playerId
            ? $candidates->firstWhere('id', $playerId)
            : ($candidates->isEmpty() ? null : $candidates->random());

        if (! $replacement) {
            throw ValidationException::withMessages([
                'player_id' => __('No suitable replacement player is available.'),
            ]);
        }

        $now = now();
        $ratingRequest->update([
            'status' => RatingRequest::STATUS_REVOKED,
            'revoked_at' => $now,
        ]);
        $ratingRequest->events()->create([
            'actor_user_id' => $actorId,
            'previous_player_id' => $ratingRequest->player_id,
            'new_player_id' => $replacement->id,
            'type' => 'replace',
            'expires_at' => $now,
        ]);

        $newRequest = $this->createRequest($ratingRequest->game, $replacement, $ratingRequest, $actorId);
        $this->send($newRequest, $actorId);

        return $newRequest;
    }

    public function replacementCandidates(Game $game, ?RatingRequest $current = null): Collection
    {
        return $game->teams()
            ->whereHas('user')
            ->whereDoesntHave('givenRatings', fn ($query) => $query->where('game_id', $game->id))
            ->whereDoesntHave('ratingRequests', function ($query) use ($game): void {
                $query->where('game_id', $game->id)
                    ->whereIn('status', [RatingRequest::STATUS_PENDING, RatingRequest::STATUS_SEND_FAILED]);
            })
            ->when($current, fn ($query) => $query->where('players.id', '!=', $current->player_id))
            ->orderBy('players.name')
            ->get(['players.id', 'players.name']);
    }

    public function markCompleted(RatingRequest $ratingRequest): void
    {
        if ($ratingRequest->status === RatingRequest::STATUS_COMPLETED) {
            return;
        }

        $ratingRequest->update([
            'status' => RatingRequest::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
        $ratingRequest->events()->create([
            'type' => 'complete',
            'previous_player_id' => $ratingRequest->player_id,
            'new_player_id' => $ratingRequest->player_id,
            'expires_at' => $ratingRequest->expires_at,
        ]);
    }

    public function signedUrl(RatingRequest $ratingRequest, string $routeName = 'players.rate'): string
    {
        return URL::temporarySignedRoute($routeName, $ratingRequest->expires_at, [
            'game' => $ratingRequest->game_id,
            'ratingRequest' => $ratingRequest->id,
            'v' => $ratingRequest->token_version,
        ]);
    }

    public function ensureUsable(RatingRequest $ratingRequest): void
    {
        if (! $ratingRequest->isActive()) {
            abort(403, __('This rating request is no longer available.'));
        }

        if ($ratingRequest->expires_at?->isPast()) {
            $ratingRequest->update(['status' => RatingRequest::STATUS_EXPIRED]);
            abort(403, __('This rating request has expired.'));
        }
    }

    public function ensureViewable(RatingRequest $ratingRequest): void
    {
        if ($ratingRequest->status === RatingRequest::STATUS_REVOKED) {
            abort(403, __('This rating request is no longer available.'));
        }

        if ($ratingRequest->isActive() && $ratingRequest->expires_at?->isPast()) {
            $ratingRequest->update(['status' => RatingRequest::STATUS_EXPIRED]);
            abort(403, __('This rating request has expired.'));
        }
    }

    public function ensureMatchesVersion(RatingRequest $ratingRequest, int|string|null $version): void
    {
        abort_unless((int) $version === (int) $ratingRequest->token_version, 403);
    }

    private function createRequest(Game $game, Player $player, ?RatingRequest $replacementOf = null, ?int $actorId = null): RatingRequest
    {
        $now = now();
        $ratingRequest = RatingRequest::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'status' => RatingRequest::STATUS_PENDING,
            'sent_at' => $now,
            'expires_at' => $now->copy()->addHours(self::LINK_LIFETIME_HOURS),
            'token_version' => 1,
            'replacement_of_id' => $replacementOf?->id,
        ]);

        $ratingRequest->events()->create([
            'actor_user_id' => $actorId,
            'previous_player_id' => $replacementOf?->player_id,
            'new_player_id' => $player->id,
            'type' => 'initial_send',
            'expires_at' => $ratingRequest->expires_at,
        ]);

        return $ratingRequest->load(['game', 'player.user']);
    }

    private function send(RatingRequest $ratingRequest, ?int $actorId = null): void
    {
        try {
            Mail::to($ratingRequest->player->user->email)
                ->send(new RatingRequestMail($ratingRequest->game, $this->signedUrl($ratingRequest)));
        } catch (Throwable $exception) {
            $ratingRequest->update(['status' => RatingRequest::STATUS_SEND_FAILED]);
            $ratingRequest->events()->create([
                'actor_user_id' => $actorId,
                'previous_player_id' => $ratingRequest->player_id,
                'new_player_id' => $ratingRequest->player_id,
                'type' => 'send_failed',
                'expires_at' => $ratingRequest->expires_at,
                'details' => $exception->getMessage(),
            ]);
            Log::error("Failed to send rating request {$ratingRequest->id}: {$exception->getMessage()}");
        }
    }

    private function ensureCanManage(RatingRequest $ratingRequest): void
    {
        abort_unless($ratingRequest->status !== RatingRequest::STATUS_COMPLETED, 422, __('This rating request is already completed.'));
        abort_unless($ratingRequest->status !== RatingRequest::STATUS_REVOKED, 422, __('This rating request has been revoked.'));
    }

    private function ensureGameCanManage(Game $game): void
    {
        abort_unless(! $game->isInPast(), 422, __('Rating requests for past games cannot be changed.'));
    }
}
