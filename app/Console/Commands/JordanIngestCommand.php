<?php

namespace App\Console\Commands;

use App\Actions\ParseDump;
use App\Exceptions\VagueStamp;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

#[Signature('jordan:ingest {path? : File or folder of dumps} {--source=dump : Source type stamp}')]
#[Description('Parse dumps into cards. Local Qwen stamps does / does_not / steps_on.')]
class JordanIngestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ParseDump $parse): int
    {
        $path = $this->argument('path') ?: (string) config('jordan.dumps_path');
        $sourceType = (string) $this->option('source');

        if (! is_string($path) || $path === '' || (! is_file($path) && ! is_dir($path))) {
            $this->error("Nothing to ingest at {$path}");

            return self::FAILURE;
        }

        $paths = is_file($path)
            ? [$path]
            : collect((new Finder)->files()->in($path)->name(['*.txt', '*.md', '*.eml', '*.json'])->sortByName())
                ->map(fn ($file): string => $file->getRealPath())
                ->all();

        if ($paths === []) {
            $this->error('No text dumps found.');

            return self::FAILURE;
        }

        foreach ($paths as $fullPath) {
            $body = File::get($fullPath);

            if (trim($body) === '') {
                $this->warn("skip empty {$fullPath}");

                continue;
            }

            try {
                $card = $parse->handle($sourceType, $body, $fullPath);
            } catch (VagueStamp $exception) {
                $this->warn("skip vague {$fullPath}: {$exception->getMessage()}");

                continue;
            }

            $this->info("card #{$card->id} {$card->what_this_is}");
        }

        return self::SUCCESS;
    }
}
