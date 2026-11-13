<?php

use App\Actions\AskJordan;
use App\Ai\Agents\JordanAgent;
use App\Models\Ask;
use App\Support\AskQuota;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

it('rejects an ask that has no question', function () {
    $this->postJson('/api/ask', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['question']);

    $this->postJson('/ask', ['question' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['question']);

    expect(Ask::query()->count())->toBe(0);
    JordanAgent::assertNeverPrompted();
    expect(Cache::has(AskJordan::BUSY_KEY))->toBeFalse();
});

it('rejects a question longer than 4000 characters', function () {
    $this->postJson('/api/ask', [
        'question' => str_repeat('a', 4001),
    ])->assertUnprocessable()->assertJsonValidationErrors(['question']);

    expect(Ask::query()->count())->toBe(0);
    JordanAgent::assertNeverPrompted();
});

it('accepts a question at the length limit and stores the reply', function () {
    JordanAgent::fake(['nah. later.'])->preventStrayPrompts();

    $question = str_repeat('a', 4000);

    $this->postJson('/api/ask', ['question' => $question])
        ->assertOk()
        ->assertExactJson(['reply' => 'nah. later.']);

    $ask = Ask::query()->sole();

    expect($ask->source)->toBe('api')
        ->and($ask->status)->toBe('done')
        ->and($ask->session_id)->toBeNull()
        ->and($ask->question)->toBe($question)
        ->and($ask->reply)->toBe('nah. later.');

    JordanAgent::assertPrompted($question);
    expect(Cache::has(AskJordan::BUSY_KEY))->toBeFalse();
});

it('answers a web ask with the session thread and releases the line', function () {
    JordanAgent::fake(['that is on you.'])->preventStrayPrompts();

    $response = $this->postJson('/ask', ['question' => 'who are you']);

    $response->assertOk()
        ->assertJsonPath('reply', 'that is on you.');

    $ask = Ask::query()->sole();

    expect($response->json('id'))->toBe($ask->id)
        ->and($ask->source)->toBe('web')
        ->and($ask->status)->toBe('done')
        ->and($ask->session_id)->not->toBeNull()
        ->and(Cache::has(AskJordan::BUSY_KEY))->toBeFalse();
});

it('selects an event stream for an ask that wants one', function () {
    JordanAgent::fake(['nah later friend.'])->preventStrayPrompts();

    $response = $this->post('/ask', ['question' => 'stream me'], [
        'Accept' => 'text/event-stream',
    ]);

    $response->assertOk();

    expect($response->headers->get('content-type'))->toStartWith('text/event-stream')
        ->and($response->baseResponse)->toBeInstanceOf(StreamedResponse::class);

    $ask = Ask::query()->sole();

    expect($ask->source)->toBe('web')
        ->and($ask->status)->toBe('started')
        ->and($ask->question)->toBe('stream me')
        ->and($ask->reply)->toBeNull();
});

it('fails closed with the busy line and does not call the model', function () {
    Cache::add(AskJordan::BUSY_KEY, 1, 60);

    $this->postJson('/api/ask', ['question' => 'hello'])
        ->assertStatus(503)
        ->assertExactJson(['message' => AskJordan::BUSY_MESSAGE]);

    expect(Ask::query()->count())->toBe(0);
    JordanAgent::assertNeverPrompted();
});

it('records a failed ask and hangs up when the model blows up', function () {
    JordanAgent::fake(function (): void {
        throw new RuntimeException('ollama down');
    });

    $this->postJson('/api/ask', ['question' => 'ship it'])
        ->assertStatus(500);

    $ask = Ask::query()->sole();

    expect($ask->status)->toBe('failed')
        ->and($ask->reply)->toBe('ollama down')
        ->and(Cache::has(AskJordan::BUSY_KEY))->toBeFalse();

    JordanAgent::fake(['recovered.'])->preventStrayPrompts();

    $this->postJson('/api/ask', ['question' => 'again'])
        ->assertOk()
        ->assertExactJson(['reply' => 'recovered.']);
});

it('closes the api and the page together once the daily allowance is spent', function () {
    config(['jordan.ask_per_day' => 1]);

    JordanAgent::fake(['nah.'])->preventStrayPrompts();

    $this->postJson('/api/ask', ['question' => 'first'])
        ->assertOk()
        ->assertExactJson(['reply' => 'nah.']);

    $this->postJson('/ask', ['question' => 'second'])
        ->assertStatus(429)
        ->assertJsonPath('message', AskQuota::CLOSED_MESSAGE)
        ->assertJsonStructure(['message', 'opens_at'])
        ->assertHeader('Retry-After');

    $this->postJson('/api/ask', ['question' => 'third'])
        ->assertStatus(429)
        ->assertJsonPath('message', AskQuota::CLOSED_MESSAGE);

    expect(Ask::query()->count())->toBe(1);
    JordanAgent::assertPrompted('first');
    JordanAgent::assertNotPrompted('second');

    $this->get('/')
        ->assertOk()
        ->assertSee('CLOSED', false)
        ->assertDontSee('id="ask"', false);
});

it('does not rate limit asks when the daily cap is off', function () {
    config(['jordan.ask_per_day' => 0]);

    JordanAgent::fake(['one', 'two']);

    $this->postJson('/api/ask', ['question' => 'a'])->assertOk()->assertJsonPath('reply', 'one');
    $this->postJson('/api/ask', ['question' => 'b'])->assertOk()->assertJsonPath('reply', 'two');

    expect(Ask::query()->count())->toBe(2);
});
