<?php

namespace App\Console\Commands;

use App\Models\Ask;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jordan:asks')]
#[Description('Show whether questions actually hit Qwen.')]
class JordanAsksCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $asks = Ask::query()->orderByDesc('id')->limit(20)->get();

        if ($asks->isEmpty()) {
            $this->warn('No asks yet. Nothing has been sent to Qwen.');

            return self::SUCCESS;
        }

        $this->table(
            ['id', 'status', 'source', 'question', 'reply'],
            $asks->map(fn (Ask $ask): array => [
                $ask->id,
                $ask->status,
                $ask->source,
                str($ask->question)->limit(40),
                str((string) $ask->reply)->limit(40),
            ])->all(),
        );

        return self::SUCCESS;
    }
}
