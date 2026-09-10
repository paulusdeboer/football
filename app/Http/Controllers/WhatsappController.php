<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use App\Services\WhapiClient;
use App\Services\WhapiException;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class WhatsappController extends Controller
{
    public function __construct(private WhapiClient $client, private WhatsappService $whatsapp) {}

    public function index()
    {
        return Inertia::render('Settings/Whatsapp', ['settings' => WhatsappSetting::current()->publicData()]);
    }

    private function json(callable $callback)
    {
        try {
            return response()->json($callback())->header('Cache-Control', 'no-store');
        } catch (WhapiException $e) {
            return response()->json(['message' => __('whatsapp.errors')[$e->reason]], 422)->header('Cache-Control', 'no-store');
        }
    }

    private function token(WhatsappSetting $settings): string
    {
        if (! $settings->token) {
            throw new WhapiException('not_configured');
        }

        return $settings->token;
    }

    private function check(WhatsappSetting $settings): void
    {
        try {
            $health = $this->client->health($this->token($settings));
        } catch (WhapiException $e) {
            $settings->update(['connection_status' => 'error', 'checked_at' => now()]);
            throw $e;
        }
        $phone = $health['phone'];
        if ($health['connection_status'] === 'connected' && $settings->phone && $settings->phone !== $phone) {
            $settings->fill(['enabled' => false, 'group_id' => null, 'group_name' => null, 'version' => $settings->version + 1]);
        }
        $settings->fill(['connection_status' => $health['connection_status'], 'checked_at' => now()]);
        if ($phone) {
            $settings->phone = $phone;
        }
        $settings->save();
    }

    public function status()
    {
        return $this->json(fn () => DB::transaction(function () {
            $settings = WhatsappSetting::lockForUpdate()->findOrFail(1);
            // Commit the error status too, without returning provider details.
            try {
                $this->check($settings);
            } catch (WhapiException $e) {
                return ['settings' => $settings->publicData(), 'message' => __('whatsapp.errors')[$e->reason]];
            }

            return ['settings' => $settings->publicData()];
        }));
    }

    public function saveToken(Request $request)
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:2048', 'regex:/^\S+$/']]);

        return $this->json(fn () => DB::transaction(function () use ($data) {
            $settings = WhatsappSetting::lockForUpdate()->findOrFail(1);
            // Validate without requiring an already linked phone.
            $health = $this->client->health($data['token']);
            $settings->update([
                'token' => $data['token'], 'enabled' => false, 'group_id' => null, 'group_name' => null,
                'phone' => $health['phone'], 'connection_status' => $health['connection_status'],
                'checked_at' => now(), 'version' => $settings->version + 1,
            ]);

            return ['settings' => $settings->publicData()];
        }));
    }

    public function qr()
    {
        return $this->json(fn () => $this->client->qr($this->token(WhatsappSetting::current())));
    }

    public function groups(Request $request)
    {
        $data = $request->validate(['offset' => ['nullable', 'integer', 'min:0', 'max:100000']]);

        return $this->json(fn () => $this->client->groups($this->token(WhatsappSetting::current()), (int) ($data['offset'] ?? 0)));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'group_id' => ['nullable', 'string', 'max:255', 'regex:/^[\d-]+@g\.us$/'],
            'enabled' => ['required', 'boolean'], 'version' => ['required', 'integer'],
        ]);

        return $this->json(fn () => DB::transaction(function () use ($data) {
            $settings = WhatsappSetting::lockForUpdate()->findOrFail(1);
            if ((int) $data['version'] !== $settings->version) {
                throw new WhapiException('settings_changed');
            }
            $groupId = $data['group_id'] ?? null;
            // Switching off must also work during a provider outage.
            if (! $data['enabled'] && $groupId === $settings->group_id) {
                $settings->update(['enabled' => false]);
            } else {
                $this->check($settings);
                if ($settings->connection_status !== 'connected') {
                    throw new WhapiException('disconnected');
                }
                $group = $this->client->group($this->token($settings), $groupId ?? '');
                $changed = $groupId !== $settings->group_id;
                $settings->update([
                    'group_id' => $group['id'], 'group_name' => $group['name'],
                    'enabled' => $changed ? false : $data['enabled'],
                    'version' => $settings->version + ($changed ? 1 : 0),
                ]);
            }

            return ['settings' => $settings->publicData()];
        }));
    }

    public function test(Request $request)
    {
        $data = $request->validate(['action_key' => ['required', 'uuid']]);
        $message = DB::transaction(function () use ($request, $data) {
            $settings = WhatsappSetting::lockForUpdate()->findOrFail(1);
            $hash = hash('sha256', 'test');

            return $this->whatsapp->existing($data['action_key'], $request->user()->id, $hash)
                ?? $this->whatsapp->prepare($settings, null, $request->user()->id, $data['action_key'], 'test', $hash);
        });

        return response()->json(['message' => $this->whatsapp->data($this->whatsapp->dispatch($message))]);
    }

    public function retry(Request $request, Game $game, WhatsappMessage $message)
    {
        abort_unless($message->game_id === $game->id, 404);
        $data = $request->validate(['action_key' => ['required', 'uuid'], 'checked_group' => ['nullable', 'boolean']]);
        $retry = DB::transaction(function () use ($request, $data, $game, $message) {
            $settings = WhatsappSetting::lockForUpdate()->findOrFail(1);
            $game = Game::lockForUpdate()->findOrFail($game->id);
            $hash = hash('sha256', 'retry:'.$message->id);
            if ($existing = $this->whatsapp->existing($data['action_key'], $request->user()->id, $hash)) {
                return $existing;
            }
            $message->refresh();
            if (! $this->whatsapp->data($message)['can_retry'] || WhatsappMessage::where('retry_of_id', $message->id)->exists()) {
                throw ValidationException::withMessages(['whatsapp' => __('whatsapp.errors.stale')]);
            }
            if ($message->displayStatus() === 'uncertain' && ! $request->boolean('checked_group')) {
                throw ValidationException::withMessages(['whatsapp' => __('whatsapp.check_group')]);
            }
            $message->update(['status' => 'superseded']);

            return $this->whatsapp->prepare($settings, $game, $request->user()->id, $data['action_key'], $message->kind, $hash, $message->id);
        });
        $this->whatsapp->dispatch($retry);

        return redirect()->route('games.show', $game);
    }
}
