<?php

namespace App\Mcp\Tools;

use App\Actions\AskJordan;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('ask-jordan')]
#[Description('Ask a question and get an answer in Jordan\'s voice. Pulls cards; does not own a dataset.')]
#[IsReadOnly]
class AskJordanTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request, AskJordan $ask): Response
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
        ]);

        return Response::text($ask->handle($validated['question'], 'mcp'));
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
                ->description('The question to ask Jordan.')
                ->required(),
        ];
    }
}
