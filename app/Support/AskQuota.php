<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AskQuota
{
    public const CLOSED_MESSAGE = 'CLOSED. Go away. We answered all the questions we want to answer. Do not pester us.';

    public static function max(): int
    {
        return (int) config('jordan.ask_per_day');
    }

    public static function isCapped(): bool
    {
        return self::max() > 0;
    }

    public static function remaining(Request $request): int
    {
        if (! self::isCapped()) {
            return self::max();
        }

        $key = md5('ask'.$request->ip().'|'.now()->toDateString());

        return RateLimiter::remaining($key, self::max());
    }

    public static function isClosed(Request $request): bool
    {
        return self::isCapped() && self::remaining($request) <= 0;
    }

    public static function opensAt(): CarbonInterface
    {
        return now()->addDay()->startOfDay();
    }
}
