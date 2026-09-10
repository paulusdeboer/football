<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WhapiClient
{
    public function request(#[\SensitiveParameter] string $token, string $method, string $path, array $data = []): array
    {
        try {
            $response = Http::baseUrl('https://gate.whapi.cloud')
                ->withToken($token)->acceptJson()->connectTimeout(3)->timeout(10)
                ->withoutRedirecting()
                ->send($method, $path, $method === 'GET' ? ['query' => $data] : ['json' => $data]);
        } catch (ConnectionException) {
            throw new WhapiException('connection', $method === 'POST');
        }

        if (! $response->successful()) {
            $reason = match ($response->status()) {
                401 => 'authorization', 402 => 'limit', 403 => 'forbidden',
                409 => 'already_connected', 429 => 'rate_limit', default => 'provider',
            };
            throw new WhapiException($reason, $method === 'POST' && ($response->serverError() || $response->status() === 408));
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new WhapiException('response', $method === 'POST');
        }

        return $json;
    }

    public function health(string $token): array
    {
        $data = $this->request($token, 'GET', '/health', ['wakeup' => 'false']);
        $status = data_get($data, 'status.text');
        if (! is_string($status)) {
            throw new WhapiException('response');
        }

        return [
            'connection_status' => $status === 'AUTH' ? 'connected' : 'disconnected',
            'phone' => data_get($data, 'user.phone') ?? data_get($data, 'user.id'),
        ];
    }

    public function qr(string $token): array
    {
        $data = $this->request($token, 'GET', '/users/login');
        $base64 = $data['base64'] ?? '';
        if (! is_string($base64)) {
            throw new WhapiException('qr_unavailable');
        }
        // Only render raster images, never arbitrary provider URLs or SVGs.
        $raw = preg_replace('#^data:image/(png|jpeg);base64,#', '', $base64);
        $bytes = base64_decode($raw, true);
        if (($data['status'] ?? '') !== 'OK' || ! $bytes || ! ($size = @getimagesizefromstring($bytes)) || ! in_array($size['mime'], ['image/png', 'image/jpeg'], true)) {
            throw new WhapiException('qr_unavailable');
        }

        return ['image' => 'data:'.$size['mime'].';base64,'.base64_encode($bytes), 'expires_in' => max(1, min(120, (int) ($data['expire'] ?? 30)))];
    }

    public function groups(string $token, int $offset = 0): array
    {
        $data = $this->request($token, 'GET', '/groups', ['count' => 100, 'offset' => $offset]);
        if (! isset($data['groups']) || ! is_array($data['groups'])) {
            throw new WhapiException('response');
        }
        $groups = collect($data['groups'])->filter(fn ($group) => is_string($group['id'] ?? null) && str_ends_with($group['id'], '@g.us'))
            ->map(fn ($group) => ['id' => $group['id'], 'name' => (string) ($group['name'] ?? $group['id'])])->values()->all();
        $next = $offset + count($data['groups']);

        return ['groups' => $groups, 'next_offset' => count($data['groups']) === 100 ? $next : null];
    }

    public function group(string $token, string $id): array
    {
        // Validate against the provider's group list, including later pages.
        $offset = 0;
        for ($page = 0; $page < 100; $page++) {
            $result = $this->groups($token, $offset);
            foreach ($result['groups'] as $group) {
                if ($group['id'] === $id) {
                    return $group;
                }
            }
            if ($result['next_offset'] === null) {
                break;
            }
            $offset = $result['next_offset'];
        }
        throw new WhapiException('group_missing');
    }

    public function send(string $token, string $destination, string $body): ?string
    {
        $data = $this->request($token, 'POST', '/messages/text', ['to' => $destination, 'body' => $body]);
        if (($data['sent'] ?? null) !== true) {
            throw new WhapiException('response', true);
        }

        $id = data_get($data, 'message.id');

        return is_string($id) ? $id : null;
    }
}
