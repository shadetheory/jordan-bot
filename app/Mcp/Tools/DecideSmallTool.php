<?php

namespace App\Mcp\Tools;

use App\Actions\DecideSmall;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('decide-small')]
#[Description('Small Jordan call: park, later, nah, or try_this. Pulls cards. Does not act, merge, or file.')]
#[IsReadOnly]
class DecideSmallTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request, DecideSmall $decide): Response
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
        ]);

        return Response::text(json_encode(
            $decide->handle($validated['question']),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
        ));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'question' => $schema->string()
                ->description('The small decision to call.')
                ->required(),
        ];
    }
}
