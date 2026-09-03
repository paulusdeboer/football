<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class TranslationCoverageTest extends TestCase
{
    public function test_all_static_translation_keys_have_a_dutch_translation(): void
    {
        $nlJson = json_decode(
            File::get(lang_path('nl.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $missing = [];

        foreach (File::allFiles(base_path('app')) as $file) {
            $this->collectPhpKeys($file->getContents(), $file->getRelativePathname(), $missing);
        }

        foreach (File::allFiles(resource_path('js')) as $file) {
            $this->collectReactKeys($file->getContents(), $file->getRelativePathname(), $nlJson, $missing);
        }

        foreach (File::allFiles(resource_path('views')) as $file) {
            $this->collectPhpKeys($file->getContents(), $file->getRelativePathname(), $missing);
        }

        foreach ($missing as $key => $locations) {
            $missing[$key] = implode(', ', array_unique($locations));
        }

        $this->assertSame([], $missing, "Missing Dutch translations:\n".json_encode($missing, JSON_PRETTY_PRINT));
    }

    private function collectPhpKeys(string $contents, string $file, array &$missing): void
    {
        preg_match_all('/\b(?:__|trans_choice|trans)\(\s*([\'\"])(.*?)\1/s', $contents, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $key = $match[2];
            if (! Lang::has($key, 'nl')) {
                $missing[$key][] = $file;
            }
        }
    }

    private function collectReactKeys(string $contents, string $file, array $nlJson, array &$missing): void
    {
        preg_match_all('/\bt\(\s*([\'\"])(.*?)\1/s', $contents, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $key = $match[2];
            if (! array_key_exists($key, $nlJson)) {
                $missing[$key][] = $file;
            }
        }
    }
}
