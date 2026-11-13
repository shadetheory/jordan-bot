<?php

namespace App\Console\Commands;

use App\Actions\StampAsgardFact;
use App\Jobs\StampAsgardQueryJob;
use App\Services\AsgardQuery;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jordan:ingest-asgard {question? : Official fact to pull} {--tenant=asgard} {--queue : Dispatch instead of running now}')]
#[Description('Tap Asgard knowledge query into cards. Never writes gospel.')]
class JordanIngestAsgardCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AsgardQuery $asgard, StampAsgardFact $stamp): int
    {
        if (! $asgard->enabled()) {
            $this->error('Asgard door closed. Set ASGARD_URL and ASGARD_LEXI_PREP_TOKEN.');

            return self::FAILURE;
        }

        $question = $this->argument('question');
        $tenant = (string) $this->option('tenant');
        $questions = is_string($question) && trim($question) !== ''
            ? [trim($question)]
            : config('jordan.asgard_questions');

        if (! is_array($questions) || $questions === []) {
            $this->error('No question.');

            return self::FAILURE;
        }

        foreach ($questions as $q) {
            $q = trim((string) $q);

            if ($q === '') {
                continue;
            }

            if ($this->option('queue')) {
                StampAsgardQueryJob::dispatch($q, $tenant);
                $this->comment("queued {$q}");

                continue;
            }

            $facts = $asgard->facts($q, $tenant);

            if ($facts === null) {
                $this->error("door closed for {$q}");

                return self::FAILURE;
            }

            if ($facts === []) {
                $this->warn("no facts for {$q}");

                continue;
            }

            foreach ($facts as $fact) {
                $card = $stamp->handle($fact);
                $this->info("card #{$card->id} {$card->what_this_is}");
            }
        }

        return self::SUCCESS;
    }
}
