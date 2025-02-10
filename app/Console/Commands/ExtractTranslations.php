<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExtractTranslations extends Command
{
    protected $signature = 'translations:extract';
    protected $description = 'Scan all translatable strings and add them to nl.json';

    public function handle()
    {
        $jsonPath = base_path('lang/nl.json');

        if (!File::exists($jsonPath)) {
            File::put($jsonPath, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $files = File::allFiles(base_path('resources/views'));
        $translations = [];

        foreach ($files as $file) {
            preg_match_all("/__\(['\"](.*?)['\"]\)/", File::get($file), $matches);
            foreach ($matches[1] as $match) {
                if (!array_key_exists($match, $translations)) {
                    $translations[$match] = ''; // Laat de vertaalde string leeg
                }
            }
        }

        if (File::exists($jsonPath)) {
            $existingTranslations = json_decode(File::get($jsonPath), true);

            foreach ($translations as $key => $value) {
                if (!array_key_exists($key, $existingTranslations)) {
                    $existingTranslations[$key] = $value;
                }
            }

            $translations = $existingTranslations;
        }

        File::put($jsonPath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('Translations extracted and added to nl.json!');
    }
}
