<?php

use App\Ai\Tools\ListBoundaries;
use App\Ai\Tools\SearchCards;
use App\Models\Card;
use App\Support\JordanPocket;
use Laravel\Ai\Tools\Request;

it('drops glue words before it searches', function () {
    expect(Card::terms('is this the lexi')->all())->toBe(['lexi'])
        ->and(Card::terms('the and for')->isEmpty())->toBeTrue();
});

it('pulls cards by a real term and nothing for glue', function () {
    Card::factory()->create([
        'what_this_is' => 'Lexi',
        'does' => 'She is the face of the shop',
        'does_not' => 'She does not store the facts',
        'private' => false,
        'voice_line' => 'she talks',
    ]);

    Card::factory()->create([
        'what_this_is' => 'Pottery',
        'does' => 'Makes bowls',
        'private' => false,
    ]);

    expect(Card::pull('who is lexi'))->toHaveCount(1)
        ->and(Card::pull('who is lexi')->first()->what_this_is)->toBe('Lexi')
        ->and(Card::pull('the and for'))->toBeEmpty();
});

it('keeps private notes and house names out of the web pocket', function () {
    Card::factory()->create([
        'what_this_is' => 'Secret pipeline',
        'does' => 'Ingests the dumps',
        'private' => true,
        'voice_line' => 'keep this one',
    ]);

    Card::factory()->create([
        'what_this_is' => 'Asgard store',
        'does' => 'Holds the facts',
        'private' => false,
        'voice_line' => 'the cabinet',
    ]);

    Card::factory()->create([
        'what_this_is' => 'The offer',
        'does' => 'Answers the question',
        'private' => false,
        'voice_line' => 'ask this face',
    ]);

    expect(JordanPocket::forSource('secret pipeline', 'web'))->toBeEmpty()
        ->and(JordanPocket::forSource('secret pipeline', 'cli'))->toHaveCount(1)
        ->and(JordanPocket::forSource('asgard store', 'web'))->toBeEmpty()
        ->and(JordanPocket::forSource('the offer', 'web'))->toHaveCount(1);

    expect(JordanPocket::render('the offer', 'web'))->toContain('ask this face')
        ->and(JordanPocket::render('the offer', 'web'))->not->toContain('Asgard');
});

it('searches and lists boundaries through the tools', function () {
    Card::factory()->create([
        'what_this_is' => 'Lexi',
        'does' => 'She is the face',
        'does_not' => 'She does not store facts',
        'steps_on' => 'the cabinet',
        'private' => false,
    ]);

    Card::factory()->create([
        'what_this_is' => 'The offer',
        'does' => 'Answers the question',
        'does_not' => 'Does not file tickets',
        'steps_on' => 'none',
        'private' => false,
    ]);

    expect((new SearchCards('cli'))->handle(new Request(['query' => 'lexi'])))->toContain('Lexi')
        ->and((new SearchCards('web'))->handle(new Request(['query' => 'lexi'])))->toBe('Nothing matched.')
        ->and((new SearchCards('web'))->handle(new Request(['query' => 'offer'])))->toContain('The offer')
        ->and((new SearchCards('web'))->handle(new Request(['query' => 'missing product'])))->toBe('Nothing matched.')
        ->and((new ListBoundaries('web'))->handle(new Request(['product' => 'offer'])))->toContain('Answers the question')
        ->and((new ListBoundaries('web'))->handle(new Request(['product' => 'lexi'])))->toBe('No boundaries.')
        ->and((new ListBoundaries('cli'))->handle(new Request([])))->toContain('Lexi');
});

it('says when a question has nothing already in mind', function () {
    expect(JordanPocket::render('anything at all', 'web'))
        ->toContain('Nothing already in mind');
});
