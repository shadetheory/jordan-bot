<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class JordanVoice
{
    /**
     * @return list<string>
     */
    public static function collect(string $sessionsRoot): array
    {
        if (! is_dir($sessionsRoot)) {
            return [];
        }

        $lines = [];

        foreach (File::allFiles($sessionsRoot) as $file) {
            if ($file->getFilename() !== 'chat_history.jsonl') {
                continue;
            }

            $handle = fopen($file->getRealPath(), 'r');

            if ($handle === false) {
                continue;
            }

            while (($row = fgets($handle)) !== false) {
                foreach (self::extractLine($row) as $line) {
                    $lines[] = $line;
                }
            }

            fclose($handle);
        }

        return self::unique($lines);
    }

    /**
     * @return list<string>
     */
    public static function extract(string $jsonl): array
    {
        $lines = [];

        foreach (explode("\n", $jsonl) as $row) {
            foreach (self::extractLine($row) as $line) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private static function extractLine(string $row): array
    {
        $row = trim($row);

        if ($row === '') {
            return [];
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = json_decode($row, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        if (($payload['type'] ?? null) !== 'user') {
            return [];
        }

        $blob = self::textOf($payload['content'] ?? null);

        if (preg_match_all('/<user_query>\s*(.*?)\s*<\/user_query>/s', $blob, $matches) === 0) {
            return [];
        }

        $lines = [];

        foreach ($matches[1] as $query) {
            $line = trim(preg_replace('/\s+/', ' ', $query) ?? '');

            if (self::keep($line)) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * @param  list<string>  $lines
     */
    public static function write(array $lines, ?string $path = null): string
    {
        $path ??= self::path();
        $dir = dirname($path);
        File::ensureDirectoryExists($dir);
        File::put($path, implode("\n\n", self::unique($lines))."\n");

        return $path;
    }

    public static function primer(string $source = 'cli', ?string $path = null): string
    {
        $path ??= self::path();
        $samples = self::samples($source, $path);

        $examples = $samples === []
            ? '- (no transcripts pulled yet. cadence still stands.)'
            : collect($samples)->map(fn (string $line): string => '- '.$line)->implode("\n");

        return <<<TEXT
        How you already talk. Steal the cadence. Do not recap these as if they asked them:
        - Short. Typos live. Heat first, then the ask.
        - "nah." "im not gonna lie." "that's sick." "love it."
        - You swear when it's funny. You do not lecture.
        - You never say you don't have it. You take a swing.

        {$examples}
        TEXT;
    }

    public static function path(): string
    {
        return (string) config('jordan.voice_path');
    }

    /**
     * @return list<string>
     */
    public static function samples(string $source, ?string $path = null): array
    {
        $path ??= self::path();

        if (! is_file($path)) {
            return [];
        }

        $lines = collect(preg_split('/\n\s*\n/', File::get($path)) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter(fn (string $line): bool => self::keep($line))
            ->values();

        if ($source === 'web') {
            $lines = $lines->reject(fn (string $line): bool => self::leaksHouse($line));
        }

        return $lines
            ->sortBy(fn (string $line): string => md5($line))
            ->take(12)
            ->values()
            ->all();
    }

    public static function leaksHouse(string $line): bool
    {
        $hay = Str::lower($line);

        foreach (['asgard', 'lexi', 'mattermost', 'qwen', 'ollama', 'mcp', 'gospel', 'jordan-bot', 'sql wins'] as $needle) {
            if (str_contains($hay, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function keep(string $line): bool
    {
        $len = Str::length($line);

        if ($len < 12 || $len > 400) {
            return false;
        }

        if (str_starts_with($line, 'The user interrupted')) {
            return false;
        }

        return ! str_contains($line, 'system-reminder') && ! str_contains($line, 'user_info');
    }

    /**
     * @param  list<string>  $lines
     * @return list<string>
     */
    private static function unique(array $lines): array
    {
        return collect($lines)
            ->unique(fn (string $line): string => Str::lower($line))
            ->values()
            ->all();
    }

    private static function textOf(mixed $content): string
    {
        if (is_string($content)) {
            return $content;
        }

        if (! is_array($content)) {
            return '';
        }

        return collect($content)
            ->map(function (mixed $chunk): string {
                if (is_string($chunk)) {
                    return $chunk;
                }

                if (is_array($chunk) && ($chunk['type'] ?? null) === 'text') {
                    return (string) ($chunk['text'] ?? '');
                }

                return '';
            })
            ->implode("\n");
    }
}
