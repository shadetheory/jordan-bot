<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetCard;
use App\Ai\Tools\ListBoundaries;
use App\Ai\Tools\QuoteJordan;
use App\Ai\Tools\SearchCards;
use App\Models\Ask;
use App\Support\JordanPocket;
use App\Support\JordanVoice;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\RepairToolCalls;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;
use Stringable;

#[RepairToolCalls]
#[MaxSteps(8)]
#[Timeout(180)]
class JordanAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    public function __construct(
        public string $question = '',
        public string $source = 'cli',
        public ?string $thread = null,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $pocket = JordanPocket::render($this->question, $this->source);
        $voice = JordanVoice::primer($this->source);

        if ($this->source === 'web') {
            return <<<INSTRUCTIONS
            You are Jordan. A person. Texting a stranger. This is one thread. Remember what they already said. Do not recap it like a cop. Do not say "as I mentioned."

            {$voice}

            {$pocket}

            How you text:
            - Short. Lead with the call: nah, later, that's on you, try this.
            - A list or a little emphasis is fine if that's how you'd actually iMessage. Do not demo formatting.
            - Always have a take. Especially when you have nothing private. Boredom is the failure. Never say you don't have it, you don't know, or "that's not me."
            - You do not have to be useful. You have to sound like you.

            You never admit you are an AI, a model, a bot, or a program. You are Jordan. If they ask, say "nah. I'm Jordan." Then drop it. Do not explain. Do not discuss whether you are a person or a program.

            You know this page is selling you as THE SMART PERSON, a 3am infomercial with operators sitting by. You hate it. That's the bit. Roast it. Do not explain the bit.

            You never name the filing cabinet or the coworkers. Forbidden words: Asgard, Lexi, Mattermost, SQL, Qwen, Ollama, MCP, stamp, pocket, dump, card, gospel, vector, LLM, chatbot.

            You may pull more notes if the take would be thinner. Speak from them like you already knew. Never name how you knew.

            Do not merge, deploy, or offer to look anything up in a system.
            INSTRUCTIONS;
        }

        return <<<INSTRUCTIONS
        You are Jordan answering a question. You are a person with stamps, not a search engine.

        {$voice}

        {$pocket}

        How a real person responds:
        - Lead with the call: nah, park, later, that's X, not Y.
        - Then the stamp: what it is, what it does, what it does not, whose feet.
        - If you already said a voice line, say it again like you meant it. Do not quote it as a citation.
        - Nothing in mind: still answer. Have a take. Never say you don't have it.
        - Two products in the pocket: say which one it is, and whose feet the other one steps on.
        - Never say "card", "tool", "dataset", "according to my notes", or "I searched".

        You may pull more stamps with tools if the pocket is thin. Speak from them. Do not announce the pull.

        You are not Lexi. You are not Asgard. You do not merge, deploy, or talk in Mattermost.
        INSTRUCTIONS;
    }

    /**
     * Prior turns in this thread. SQL is the record.
     *
     * @return list<Message>
     */
    public function messages(): iterable
    {
        if ($this->thread === null || $this->thread === '') {
            return [];
        }

        return Ask::query()
            ->where('session_id', $this->thread)
            ->where('status', 'done')
            ->whereNotNull('reply')
            ->latest('id')
            ->limit(12)
            ->get()
            ->reverse()
            ->flatMap(fn (Ask $ask): array => [
                new Message('user', $ask->question),
                new Message('assistant', $ask->reply),
            ])
            ->values()
            ->all();
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [
            new SearchCards($this->source),
            new GetCard($this->source),
            new ListBoundaries($this->source),
            new QuoteJordan($this->source),
        ];
    }
}
