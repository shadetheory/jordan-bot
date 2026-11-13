<?php

namespace App\Console\Commands;

use App\Actions\DecideSmall;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\text;

#[Signature('jordan:decide {question? : The small call} {--json : Print JSON}')]
#[Description('Small-scale Jordan call: park, later, nah, try_this. Does not act.')]
class JordanDecideCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DecideSmall $decide): int
    {
        $question = $this->argument('question');

        if (! is_string($question) || trim($question) === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Pass a question.');

                return self::FAILURE;
            }

            $question = text('Small call');
        }

        $decision = $decide->handle($question);

        if ($this->option('json')) {
            $this->line(json_encode($decision, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info($decision['call']);
        $this->line($decision['why']);

        if (filled($decision['next'])) {
            $this->comment($decision['next']);
        }

        $this->newLine();

        return self::SUCCESS;
    }
}
