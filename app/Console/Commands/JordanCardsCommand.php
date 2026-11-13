<?php

namespace App\Console\Commands;

use App\Models\Card;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jordan:cards {--query= : Pull cards matching these words}')]
#[Description('List the card store this agent pulls from.')]
class JordanCardsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = trim((string) $this->option('query'));

        $cards = Card::query()
            ->when($query !== '', fn ($builder) => $builder->matching($query))
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        if ($cards->isEmpty()) {
            $this->warn('No cards. Seed or `php artisan jordan:ingest`.');

            return self::SUCCESS;
        }

        $this->table(
            ['id', 'what', 'does', 'does not', 'steps on'],
            $cards->map(fn (Card $card): array => [
                $card->id,
                $card->what_this_is,
                str($card->does)->limit(40),
                str($card->does_not)->limit(40),
                str($card->steps_on)->limit(40),
            ])->all(),
        );

        return self::SUCCESS;
    }
}
