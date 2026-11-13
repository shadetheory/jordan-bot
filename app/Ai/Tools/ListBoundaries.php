<?php

namespace App\Ai\Tools;

use App\Models\Card;
use App\Support\JordanPocket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListBoundaries implements Tool
{
    public function __construct(public string $source = 'cli') {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Pull does / does_not / steps_on stamps. Optionally filter by a product or project name.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'product' => ['nullable', 'string', 'max:200'],
        ]);

        $product = trim((string) ($validated['product'] ?? ''));

        $cards = JordanPocket::speakable(
            Card::query()
                ->when($product !== '', fn ($query) => $query->matching($product))
                ->orderBy('what_this_is')
                ->orderByDesc('id')
                ->limit(20)
                ->get(),
            $this->source,
        );

        if ($cards->isEmpty()) {
            return 'No boundaries.';
        }

        return $cards->map(fn (Card $card): array => [
            'id' => $card->id,
            'what_this_is' => $card->what_this_is,
            'does' => $card->does,
            'does_not' => $card->does_not,
            'steps_on' => $card->steps_on,
        ])->values()->toJson();
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product' => $schema->string()->description('Product or project to pull boundaries for.'),
        ];
    }
}
