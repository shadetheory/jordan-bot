<?php

namespace App\Support;

use App\Models\Card;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JordanPocket
{
    /**
     * @var list<string>
     */
    private const HOUSE = [
        'asgard',
        'lexi',
        'mattermost',
        'qwen',
        'ollama',
        'mcp',
        'gospel',
        'jordan-bot',
    ];

    /**
     * Stamps already in mind for this question. A person does not search first.
     */
    public static function render(string $question, string $source = 'cli'): string
    {
        $cards = self::forSource($question, $source);

        if ($cards->isEmpty()) {
            return <<<'TEXT'
            Nothing already in mind. You still answer. Have a take. Be a person. Do not shrug. Do not invent a workplace, a system, or a coworker.
            TEXT;
        }

        $stamps = $cards
            ->map(fn (Card $card): string => '- '.$card->asSpoken())
            ->implode("\n");

        return <<<TEXT
        Stamps already in your pocket (you know these; speak from them. Do not say "card", "dataset", "according to my notes", or "the store"):
        {$stamps}
        TEXT;
    }

    /**
     * @return Collection<int, Card>
     */
    public static function forSource(string $question, string $source): Collection
    {
        $cards = Card::pocket($question);

        return self::speakable($cards, $source);
    }

    /**
     * @param  Collection<int, Card>  $cards
     * @return Collection<int, Card>
     */
    public static function speakable(Collection $cards, string $source): Collection
    {
        if ($source !== 'web') {
            return $cards->values();
        }

        return $cards
            ->reject(fn (Card $card): bool => $card->private)
            ->reject(fn (Card $card): bool => self::leaksHouse($card))
            ->values();
    }

    public static function leaksHouse(Card $card): bool
    {
        $hay = Str::lower(collect([
            $card->what_this_is,
            $card->does,
            $card->does_not,
            $card->steps_on,
            $card->speaker,
            $card->voice_line,
            $card->source_type,
        ])->filter()->implode(' '));

        foreach (self::HOUSE as $needle) {
            if (str_contains($hay, $needle)) {
                return true;
            }
        }

        return false;
    }
}
