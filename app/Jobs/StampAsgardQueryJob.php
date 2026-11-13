<?php

namespace App\Jobs;

use App\Actions\StampAsgardFact;
use App\Services\AsgardQuery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class StampAsgardQueryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [1, 5, 15];

    public function __construct(public string $question, public string $tenant = 'asgard')
    {
        //
    }

    public function handle(AsgardQuery $asgard, StampAsgardFact $stamp): void
    {
        $facts = $asgard->facts($this->question, $this->tenant);

        if ($facts === null) {
            throw new RuntimeException('Asgard query door closed.');
        }

        foreach ($facts as $fact) {
            $stamp->handle($fact);
        }
    }
}
