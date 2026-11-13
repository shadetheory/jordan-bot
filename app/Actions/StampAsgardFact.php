<?php

namespace App\Actions;

use App\Models\Card;
use Illuminate\Support\Str;

class StampAsgardFact
{
    /**
     * @param  array{id: int|null, tenant: string|null, title: string|null, blob: string}  $fact
     */
    public function handle(array $fact): Card
    {
        $blob = trim($fact['blob']);
        $tenant = $fact['tenant'] ?: 'asgard';
        $id = $fact['id'];
        $path = $id !== null
            ? 'asgard:'.$tenant.':'.$id
            : 'asgard:'.$tenant.':'.sha1($blob);

        $what = trim((string) ($fact['title'] ?: Str::of($blob)->before('.')->limit(80)));

        if ($what === '') {
            $what = 'asgard-fact';
        }

        return Card::query()->updateOrCreate(
            ['source_path' => $path],
            [
                'source_type' => 'asgard',
                'what_this_is' => $what,
                'does' => Str::limit($blob, 500),
                'does_not' => 'Not a dump. Official Asgard SQL fact. Not iMessage, not a second catalog.',
                'steps_on' => 'jordan-bot if we write this back to Asgard. Lexi if we speak it as the face.',
                'speaker' => 'unknown',
                'private' => false,
                'voice_line' => Str::limit($blob, 180),
                'body' => $blob,
            ],
        );
    }
}
