<?php

use App\Support\AskQuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

uses(TestCase::class);

it('reads the daily cap from config', function () {
    config(['jordan.ask_per_day' => 5]);

    expect(AskQuota::max())->toBe(5)
        ->and(AskQuota::isCapped())->toBeTrue();
});

it('is not capped when the allowance is zero', function () {
    config(['jordan.ask_per_day' => 0]);

    $request = Request::create('/ask', 'POST', server: ['REMOTE_ADDR' => '127.0.0.1']);

    expect(AskQuota::isCapped())->toBeFalse()
        ->and(AskQuota::isClosed($request))->toBeFalse()
        ->and(AskQuota::remaining($request))->toBe(0);
});

it('closes once the shared limiter has no attempts left', function () {
    config(['jordan.ask_per_day' => 2]);

    $request = Request::create('/ask', 'POST', server: ['REMOTE_ADDR' => '10.0.0.8']);
    $key = md5('ask10.0.0.8|'.now()->toDateString());

    expect(AskQuota::remaining($request))->toBe(2)
        ->and(AskQuota::isClosed($request))->toBeFalse();

    RateLimiter::hit($key, 60 * 60 * 24);
    RateLimiter::hit($key, 60 * 60 * 24);

    expect(AskQuota::remaining($request))->toBe(0)
        ->and(AskQuota::isClosed($request))->toBeTrue();
});

it('reopens at the next midnight', function () {
    $this->travelTo('2026-11-13 18:30:00');

    expect(AskQuota::opensAt()->toDateTimeString())->toBe('2026-11-14 00:00:00');
});
