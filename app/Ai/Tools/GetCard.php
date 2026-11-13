<?php

namespace App\Ai\Tools;

use App\Models\Card;
use App\Support\JordanPocket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCard implements Tool
{
    public function __construct(public string $source = 'cli') {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Pull one card by id, including a truncated original dump.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $card = Card::query()->find($validated['id']);

        if ($card === null || JordanPocket::speakable(collect([$card]), $this->source)->isEmpty()) {
            return 'Nothing on that.';
        }

        return json_encode($card->toPullArray(withBody: true), JSON_THROW_ON_ERROR);
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->min(1)->description('Card id from search.')->required(),
        ];
    }
}
