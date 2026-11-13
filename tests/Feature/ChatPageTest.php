<?php

use App\Support\AskQuota;

it('renders the ask page with the daily allowance', function () {
    $this->get(route('chat'))
        ->assertOk()
        ->assertSee('Talk to Jordan', false)
        ->assertSee('ASK NOW', false)
        ->assertSee('id="ask"', false)
        ->assertSee('name="question"', false)
        ->assertSee('5 left today', false)
        ->assertDontSee('>CLOSED<', false);
});

it('answers the health check', function () {
    $this->get('/up')->assertOk();
});

it('registers the ask and speak routes', function () {
    expect(route('chat'))->toBe(url('/'))
        ->and(route('ask.store'))->toBe(url('/ask'))
        ->and(route('asks.speak', ['ask' => 1]))->toBe(url('/asks/1/speak'));
});

it('shows the closed shop once the daily quota is spent', function () {
    exhaustAskQuota();

    $this->get('/')
        ->assertOk()
        ->assertSee('CLOSED', false)
        ->assertSee('GO AWAY', false)
        ->assertSee(AskQuota::opensAt()->toIso8601String(), false)
        ->assertDontSee('id="ask"', false)
        ->assertDontSee('ASK NOW', false);
});

it('lowers the allowance shown on the page after a rejected ask', function () {
    $this->postJson('/api/ask', [])->assertUnprocessable();

    $this->get('/')
        ->assertOk()
        ->assertSee('4 left today', false);
});
