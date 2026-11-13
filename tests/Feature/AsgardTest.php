<?php

use App\Actions\StampAsgardFact;
use App\Jobs\StampAsgardQueryJob;
use App\Models\Card;
use App\Services\AsgardQuery;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

it('keeps the door closed when asgard is not configured', function () {
    config([
        'services.asgard.url' => '',
        'services.asgard.token' => '',
    ]);

    Http::fake();

    $asgard = app(AsgardQuery::class);

    expect($asgard->enabled())->toBeFalse()
        ->and($asgard->facts('SQL wins'))->toBeNull();

    Http::assertNothingSent();
});

it('returns null when asgard answers with an error', function () {
    Http::fake([
        'http://asgard.test/*' => Http::response('down', 503),
    ]);

    expect(app(AsgardQuery::class)->facts('SQL wins'))->toBeNull();
});

it('returns null when the asgard connection fails', function () {
    Http::fake(function (): void {
        throw new ConnectionException('asgard unreachable');
    });

    expect(app(AsgardQuery::class)->facts('SQL wins'))->toBeNull();
});

it('keeps only facts that have a blob', function () {
    Http::fake([
        'http://asgard.test/api/lexi/knowledge/query' => Http::response([
            'facts' => [
                ['id' => 7, 'tenant' => 'asgard', 'title' => 'SQL wins', 'blob' => 'SQL wins. Always.'],
                ['blob' => '   '],
                'nope',
            ],
        ]),
    ]);

    $facts = app(AsgardQuery::class)->facts('SQL wins');

    expect($facts)->toBe([
        [
            'id' => 7,
            'tenant' => 'asgard',
            'title' => 'SQL wins',
            'blob' => 'SQL wins. Always.',
        ],
    ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'http://asgard.test/api/lexi/knowledge/query'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['question'] === 'SQL wins'
            && $request['tenant'] === 'asgard';
    });
});

it('stamps an asgard fact into a card without calling the network', function () {
    $card = app(StampAsgardFact::class)->handle([
        'id' => 7,
        'tenant' => 'asgard',
        'title' => 'SQL wins',
        'blob' => 'SQL wins. Always.',
    ]);

    expect($card->source_type)->toBe('asgard')
        ->and($card->source_path)->toBe('asgard:asgard:7')
        ->and($card->what_this_is)->toBe('SQL wins')
        ->and($card->private)->toBeFalse();

    $again = app(StampAsgardFact::class)->handle([
        'id' => 7,
        'tenant' => 'asgard',
        'title' => 'SQL wins',
        'blob' => 'SQL still wins.',
    ]);

    expect($again->is($card))->toBeTrue()
        ->and(Card::query()->count())->toBe(1)
        ->and($again->does)->toContain('SQL still wins.');
});

it('fails the stamp job closed when asgard returns nothing', function () {
    config([
        'services.asgard.url' => '',
        'services.asgard.token' => '',
    ]);

    expect(fn () => (new StampAsgardQueryJob('SQL wins'))->handle(
        app(AsgardQuery::class),
        app(StampAsgardFact::class),
    ))->toThrow(RuntimeException::class, 'Asgard query door closed.');

    expect(Card::query()->count())->toBe(0);
});
