<?php

namespace App\Ai\Tools;

use App\Models\Card;
use App\Support\JordanPocket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class QuoteJordan implements Tool
{
    public function __construct(public string $source = 'cli') {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Pull voice_line stamps that sound like Jordan on this topic.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:500'],
        ]);

        $cards = Card::query()
            ->quoting($validated['query'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        if ($cards->isEmpty()) {
            $cards = Card::pull($validated['query'], 8);
        }

        $lines = JordanPocket::speakable($cards, $this->source)
            ->pluck('voice_line')
            ->filter(fn (mixed $line): bool => filled($line))
            ->values();

        if ($lines->isEmpty()) {
            return 'No voice lines.';
        }

        return $lines->toJson();
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Topic to quote.')->required(),
        ];
    }
}
