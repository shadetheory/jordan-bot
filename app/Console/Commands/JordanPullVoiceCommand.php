<?php

namespace App\Console\Commands;

use App\Support\JordanVoice;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jordan:pull-voice {--from= : Folder of Grok sessions} {--limit=0 : Max lines, 0 is all}')]
#[Description('Pull Jordan utterances from Grok transcripts into the voice dump.')]
class JordanPullVoiceCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $from = $this->option('from');
        $from = is_string($from) && $from !== ''
            ? $from
            : (string) config('jordan.sessions_path');

        $limit = (int) $this->option('limit');
        $lines = JordanVoice::collect($from);

        if ($limit > 0) {
            $lines = array_slice($lines, 0, $limit);
        }

        if ($lines === []) {
            $this->error("No utterances in {$from}");

            return self::FAILURE;
        }

        $path = JordanVoice::write($lines);

        $this->info(count($lines).' utterances → '.$path);

        return self::SUCCESS;
    }
}
