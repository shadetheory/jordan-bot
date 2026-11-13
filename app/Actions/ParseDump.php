<?php

namespace App\Actions;

use App\Ai\Agents\ParserAgent;
use App\Exceptions\VagueStamp;
use App\Models\Card;
use Illuminate\Support\Str;
use Laravel\Ai\Enums\Lab;

class ParseDump
{
    /**
     * @var list<string>
     */
    private array $banned = ['product', 'thing', 'stuff', 'dump', 'note', 'this', 'data', 'item', 'file'];

    public function handle(string $sourceType, string $body, ?string $sourcePath = null): Card
    {
        $response = (new ParserAgent)->prompt(
            "Source type: {$sourceType}\nPath: ".($sourcePath ?? 'none')."\n\n".$body,
            provider: Lab::Ollama,
            model: (string) config('jordan.model'),
            timeout: (int) config('jordan.timeout'),
        );

        $attributes = [
            'source_type' => $sourceType,
            'what_this_is' => (string) $response['what_this_is'],
            'does' => (string) $response['does'],
            'does_not' => (string) $response['does_not'],
            'steps_on' => (string) $response['steps_on'],
            'speaker' => (string) $response['speaker'],
            'private' => (bool) $response['private'],
            'voice_line' => (string) $response['voice_line'],
            'body' => $body,
        ];

        $this->assertSharp($attributes);

        if ($sourcePath !== null) {
            return Card::query()->updateOrCreate(
                ['source_path' => $sourcePath],
                $attributes,
            );
        }

        return Card::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function assertSharp(array $attributes): void
    {
        $what = Str::lower(trim((string) $attributes['what_this_is']));

        if ($what === '' || Str::length($what) < 3 || in_array($what, $this->banned, true)) {
            throw new VagueStamp('vague what_this_is: '.(string) $attributes['what_this_is']);
        }

        if (trim((string) $attributes['steps_on']) === '') {
            throw new VagueStamp('empty feet');
        }
    }
}
