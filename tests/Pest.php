<?php

use App\Ai\Agents\DecideAgent;
use App\Ai\Agents\JordanAgent;
use App\Ai\Agents\ParserAgent;
use App\Support\AskQuota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Http::preventStrayRequests();

        JordanAgent::fake()->preventStrayPrompts();
        DecideAgent::fake()->preventStrayPrompts();
        ParserAgent::fake()->preventStrayPrompts();
    })
    ->in('Feature');

function askQuotaKey(string $ip = '127.0.0.1'): string
{
    return md5('ask'.$ip.'|'.now()->toDateString());
}

function exhaustAskQuota(string $ip = '127.0.0.1'): void
{
    $key = askQuotaKey($ip);

    while (RateLimiter::remaining($key, AskQuota::max()) > 0) {
        RateLimiter::hit($key, 60 * 60 * 24);
    }
}
