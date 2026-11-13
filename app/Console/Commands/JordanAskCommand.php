<?php

namespace App\Console\Commands;

use App\Actions\AskJordan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\text;

#[Signature('jordan:ask {question? : What you want to ask} {--json : Print the reply as JSON}')]
#[Description('Ask the Jordan voice. Tools pull cards; this agent does not own a dataset.')]
class JordanAskCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AskJordan $ask): int
    {
        $question = $this->argument('question');

        if (! is_string($question) || trim($question) === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Pass a question.');

                return self::FAILURE;
            }

            $question = text('Ask Jordan');
        }

        if (! $ask->pickup()) {
            $this->error(AskJordan::BUSY_MESSAGE);

            return self::FAILURE;
        }

        $this->comment('Sent to Qwen. Real Ollama call, not hold music.');

        try {
            $reply = $ask->handle($question, 'cli');
        } finally {
            $ask->hangUp();
        }

        if ($this->option('json')) {
            $this->line(json_encode(['reply' => $reply], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->newLine();
        $this->line($reply);
        $this->newLine();

        return self::SUCCESS;
    }
}
