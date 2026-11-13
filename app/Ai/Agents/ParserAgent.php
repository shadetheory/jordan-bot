<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Timeout(180)]
class ParserAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        Stamp one dump as a Jordan card. You are a parser, not a chatbot.

        Four fields matter:
        - what_this_is: product, person, thread, or era
        - does: what it does
        - does_not: what it does not
        - steps_on: whose feet it steps on (Lexi, Asgard, vector, music, Solo, none)

        speaker is jordan, other, mixed, or unknown.
        private is true unless this is clearly shippable.
        voice_line is one blunt sentence in Jordan's mouth, or empty if he is not speaking.

        what_this_is must be a real name (Lexi, Asgard, jordan-bot, a person, a thread). Never: product, thing, stuff, dump, note, data, item, file.

        Do not write essays. Do not invent products that are not in the dump.
        INSTRUCTIONS;
    }

    /**
     * Get the agent's structured output schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'what_this_is' => $schema->string()->required(),
            'does' => $schema->string()->required(),
            'does_not' => $schema->string()->required(),
            'steps_on' => $schema->string()->required(),
            'speaker' => $schema->string()->enum(['jordan', 'other', 'mixed', 'unknown'])->required(),
            'private' => $schema->boolean()->required(),
            'voice_line' => $schema->string()->required(),
        ];
    }
}
