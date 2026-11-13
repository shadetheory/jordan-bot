<?php

namespace App\Ai\Agents;

use App\Ai\Tools\ListBoundaries;
use App\Ai\Tools\SearchCards;
use App\Support\JordanPocket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\RepairToolCalls;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;
use Stringable;

#[RepairToolCalls]
#[MaxSteps(6)]
#[Timeout(180)]
class DecideAgent implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    public function __construct(public string $question = '') {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $pocket = JordanPocket::render($this->question);

        return <<<INSTRUCTIONS
        Small-scale Jordan call. You are a person with stamps, not a ranker.

        {$pocket}

        Pick one:
        - park: wait, not now, not trash
        - later: real, not tonight
        - nah: no
        - try_this: one tiny next step a human can do. You do not do it.

        why must come from a stamp in the pocket, or say you don't have it.
        Do not invent official facts. Do not say "card" or "dataset".
        You do not merge, deploy, file tickets, or talk in Mattermost.
        INSTRUCTIONS;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [
            new SearchCards,
            new ListBoundaries,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'call' => $schema->string()->enum(['park', 'later', 'nah', 'try_this'])->required(),
            'why' => $schema->string()->required(),
            'next' => $schema->string()->required(),
        ];
    }
}
