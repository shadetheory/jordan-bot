<?php

namespace App\Ai\Tools;

use App\Models\Card;
use App\Support\JordanPocket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchCards implements Tool
{
    public function __construct(public string $source = 'cli') {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Pull cards that might answer this. You do not own this data. Returns what_this_is, does, does_not, steps_on, voice_line.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:500'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $cards = JordanPocket::speakable(
            Card::pull($validated['query'], $validated['limit'] ?? 8),
            $this->source,
        );

        if ($cards->isEmpty()) {
            return 'Nothing matched.';
        }

        return $cards->map(fn (Card $card): array => $card->toPullArray())->values()->toJson();
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Words to pull cards with.')->required(),
            'limit' => $schema->integer()->min(1)->max(20),
        ];
    }
}
