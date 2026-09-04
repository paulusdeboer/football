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
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
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
        $locales = array_unique([app()->getLocale(), config('app.fallback_locale', 'en')]);

        foreach ($locales as $locale) {
            $path = lang_path("{$locale}.json");
            if (File::exists($path)) {
                $translations = array_replace($translations, json_decode(File::get($path), true) ?: []);
            }
        }

        return $translations;
    }
}
