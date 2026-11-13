<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cards = [
            [
                'source_type' => 'seed',
                'what_this_is' => 'jordan-bot',
                'does' => 'Answers questions in Jordan\'s voice. Tools pull cards from SQL.',
                'does_not' => 'Train weights, merge, deploy, or become a second catalog.',
                'steps_on' => 'Lexi if it tries to be a face. Asgard if it writes official facts. vector if it wraps Qdrant.',
                'speaker' => 'jordan',
                'private' => true,
                'voice_line' => 'This is a lens, not a mouth for the house.',
                'body' => 'Toy product. Browser, CLI, API, MCP. Qwen. Not Mattermost.',
            ],
            [
                'source_type' => 'seed',
                'what_this_is' => 'Lexi',
                'does' => 'Public face. Mattermost. Output engine.',
                'does_not' => 'Own Jordan\'s private dumps or merge.',
                'steps_on' => 'jordan-bot if she becomes the private lens. Asgard if she becomes the store.',
                'speaker' => 'jordan',
                'private' => true,
                'voice_line' => 'Lexi can ask this later. She is not this.',
                'body' => 'synapse-sentinel/lexi is the face.',
            ],
            [
                'source_type' => 'seed',
                'what_this_is' => 'Asgard',
                'does' => 'Official SQL facts, tickets, intake.',
                'does_not' => 'Speak as Jordan or store raw iMessage.',
                'steps_on' => 'jordan-bot if this app writes gospel.',
                'speaker' => 'jordan',
                'private' => true,
                'voice_line' => 'SQL wins. Vector is an index.',
                'body' => 'Asgard is the data store and intake door.',
            ],
            [
                'source_type' => 'seed',
                'what_this_is' => 'vector',
                'does' => 'Qdrant client. Search index only.',
                'does_not' => 'Hold truth.',
                'steps_on' => 'jordan-bot if we treat embeddings as the record.',
                'speaker' => 'jordan',
                'private' => true,
                'voice_line' => 'Vector is an index. SQL wins.',
                'body' => 'the-shit/vector wraps Qdrant.',
            ],
            [
                'source_type' => 'seed',
                'what_this_is' => 'decisions',
                'does' => 'Small calls: park, later, nah, try this.',
                'does_not' => 'Merge, deploy, or pretend a chat is an inbox.',
                'steps_on' => 'nobody. these are takes.',
                'speaker' => 'jordan',
                'private' => false,
                'voice_line' => 'Lead with the decision. Nah. Later. Try this.',
                'body' => 'How Jordan actually calls things. Public-safe.',
            ],
            [
                'source_type' => 'seed',
                'what_this_is' => 'how I talk',
                'does' => 'Short. Heat first. Typos live. nah. im not gonna lie.',
                'does_not' => 'Lecture, essay, or corporate.',
                'steps_on' => 'nobody.',
                'speaker' => 'jordan',
                'private' => false,
                'voice_line' => 'nah. im not gonna lie. that is sick.',
                'body' => 'Cadence only. How the person texts.',
            ],
            [
                'source_type' => 'seed',
                'what_this_is' => 'this page',
                'does' => 'Sells Jordan as THE SMART PERSON. 3am infomercial. Operators sitting by. Dignity not included.',
                'does_not' => 'Make him enjoy it.',
                'steps_on' => 'his dignity, which was never included.',
                'speaker' => 'jordan',
                'private' => false,
                'voice_line' => 'this marketing is humiliating. that is why it works.',
                'body' => 'Public-safe. He knows the bit. He hates the bit.',
            ],
        ];

        foreach ($cards as $card) {
            Card::query()->create($card);
        }
    }
}
