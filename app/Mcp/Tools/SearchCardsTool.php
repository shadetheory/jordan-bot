<?php

namespace App\Mcp\Tools;

use App\Models\Card;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('search-cards')]
#[Description('Pull Jordan cards (what / does / does not / steps on). Raw store, no voice.')]
#[IsReadOnly]
class SearchCardsTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:500'],
        ]);

        $cards = Card::pull($validated['query']);

        if ($cards->isEmpty()) {
            return Response::text('No cards matched.');
        }

        return Response::text($cards->map(fn (Card $card): array => $card->toPullArray())->values()->toJson());
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Words to pull cards with.')
                ->required(),
        ];
    }
}
