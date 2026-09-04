<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ] : null,
                'can' => [
                    'accessAdminArea' => (bool) $user?->isAdmin(),
                    'managePlayers' => (bool) $user?->isAdmin(),
                    'manageGames' => (bool) $user?->isAdmin(),
                    'viewGivenRatings' => (bool) $user?->isAdmin(),
                ],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'translations' => fn () => $this->translations(),
        ];
    }

    private function translations(): array
    {
        $translations = [];
        $locales = array_reverse(array_unique([app()->getLocale(), config('app.fallback_locale', 'en')]));

        foreach ($locales as $locale) {
            $path = lang_path("{$locale}.json");
            if (File::exists($path)) {
                $translations = array_replace($translations, json_decode(File::get($path), true) ?: []);
            }
        }

        return $translations;
    }
}
