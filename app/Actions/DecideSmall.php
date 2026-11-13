<?php

namespace App\Actions;

use App\Ai\Agents\DecideAgent;
use Laravel\Ai\Enums\Lab;

class DecideSmall
{
    /**
     * @return array{call: string, why: string, next: string}
     */
    public function handle(string $question): array
    {
        $response = (new DecideAgent($question))->prompt(
            $question,
            provider: Lab::Ollama,
            model: (string) config('jordan.model'),
            timeout: (int) config('jordan.timeout'),
        );

        return [
            'call' => (string) $response['call'],
            'why' => (string) $response['why'],
            'next' => (string) $response['next'],
        ];
    }
}
