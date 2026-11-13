<?php

use App\Models\Ask;
use Illuminate\Support\Facades\Http;

function speakableAsk(string $sessionId, string $reply = 'nah. later.', string $status = 'done'): Ask
{
    return Ask::factory()->create([
        'source' => 'web',
        'session_id' => $sessionId,
        'status' => $status,
        'question' => 'who are you',
        'reply' => $reply,
    ]);
}

/**
 * Session ids have to be 40 alphanumeric characters or Laravel replaces them.
 */
function speakSession(): string
{
    return str_repeat('b', 40);
}

it('refuses speech when voice is not configured', function () {
    config([
        'services.xai.key' => '',
        'services.xai.voice_id' => '',
    ]);

    $ask = speakableAsk(speakSession());

    $this->withCredentials()
        ->withCookie((string) config('session.cookie'), speakSession())
        ->postJson(route('asks.speak', $ask))
        ->assertStatus(503)
        ->assertJsonPath('message', 'Voice is not configured. Set XAI_API_KEY and XAI_VOICE_ID.');
});

it('refuses a reply from another chat', function () {
    config([
        'services.xai.key' => 'test-xai-key',
        'services.xai.voice_id' => 'voice-test',
    ]);

    $ask = speakableAsk('someone-elses-session');

    $this->postJson(route('asks.speak', $ask))
        ->assertNotFound()
        ->assertJsonPath('message', 'That reply is not in this chat.');
});

it('refuses a reply that is not stored yet', function () {
    config([
        'services.xai.key' => 'test-xai-key',
        'services.xai.voice_id' => 'voice-test',
    ]);

    $ask = speakableAsk(speakSession(), '', 'started');

    $this->withCredentials()
        ->withCookie((string) config('session.cookie'), speakSession())
        ->postJson(route('asks.speak', $ask))
        ->assertNotFound()
        ->assertJsonPath('message', 'That reply is not stored yet.');
});

it('refuses a reply that has nothing to say out loud', function () {
    config([
        'services.xai.key' => 'test-xai-key',
        'services.xai.voice_id' => 'voice-test',
    ]);

    $ask = speakableAsk(speakSession(), "```\nonly code\n```");

    $this->withCredentials()
        ->withCookie((string) config('session.cookie'), speakSession())
        ->postJson(route('asks.speak', $ask))
        ->assertStatus(422)
        ->assertJsonPath('message', 'That reply has nothing to say out loud.');
});

it('speaks a stored reply through the faked voice endpoint', function () {
    config([
        'services.xai.key' => 'test-xai-key',
        'services.xai.voice_id' => 'voice-test',
    ]);

    Http::fake([
        'https://api.x.ai/v1/tts' => Http::response('fake-audio-bytes', 200, [
            'Content-Type' => 'audio/mpeg',
        ]),
    ]);

    $ask = speakableAsk(speakSession(), '**nah.** later.');

    $response = $this->withCookie((string) config('session.cookie'), speakSession())
        ->post(route('asks.speak', $ask));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toStartWith('audio/')
        ->and($response->getContent())->toBe('fake-audio-bytes');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.x.ai/v1/tts'
            && $request->hasHeader('Authorization', 'Bearer test-xai-key')
            && $request['voice_id'] === 'voice-test'
            && $request['language'] === 'en'
            && $request['text'] === 'nah. later.';
    });
});

it('returns speech failed when the voice endpoint does', function () {
    config([
        'services.xai.key' => 'test-xai-key',
        'services.xai.voice_id' => 'voice-test',
    ]);

    Http::fake([
        'https://api.x.ai/v1/tts' => Http::response('nope', 502),
    ]);

    $ask = speakableAsk(speakSession());

    $this->withCredentials()
        ->withCookie((string) config('session.cookie'), speakSession())
        ->postJson(route('asks.speak', $ask))
        ->assertStatus(502)
        ->assertJsonPath('message', 'Speech failed.');
});
