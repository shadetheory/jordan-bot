<?php

use App\Actions\DecideSmall;
use App\Ai\Agents\DecideAgent;
use App\Models\Ask;
use App\Support\JordanVoice;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Output\BufferedOutput;

it('pulls spoken lines out of a session transcript', function () {
    $row = json_encode([
        'type' => 'user',
        'content' => '<user_query>nah im not gonna lie this is sick</user_query>',
    ], JSON_THROW_ON_ERROR);

    expect(JordanVoice::extract($row))->toBe(['nah im not gonna lie this is sick'])
        ->and(JordanVoice::extract('not json'))->toBe([])
        ->and(JordanVoice::leaksHouse('asgard holds the facts tonight'))->toBeTrue();
});

it('drops house talk from the web voice primer', function () {
    $path = storage_path('framework/testing/voice-samples.txt');

    JordanVoice::write([
        'nah im not gonna lie',
        'asgard holds the facts tonight',
    ], $path);

    expect(JordanVoice::samples('web', $path))->toBe(['nah im not gonna lie'])
        ->and(JordanVoice::samples('cli', $path))->toContain('asgard holds the facts tonight')
        ->and(JordanVoice::primer('web', $path))->toContain('nah im not gonna lie')
        ->and(JordanVoice::primer('web', $path))->not->toContain('asgard holds');
});

it('returns no primer lines when no transcript has been pulled', function () {
    expect(JordanVoice::samples('web', storage_path('framework/testing/missing-voice.txt')))->toBe([])
        ->and(JordanVoice::primer('web', storage_path('framework/testing/missing-voice.txt')))
        ->toContain('no transcripts pulled yet');
});

it('makes a small call from a faked decision', function () {
    DecideAgent::fake([[
        'call' => 'nah',
        'why' => 'not tonight',
        'next' => 'leave it',
    ]])->preventStrayPrompts();

    $decision = app(DecideSmall::class)->handle('should I ship this');

    expect($decision)->toBe([
        'call' => 'nah',
        'why' => 'not tonight',
        'next' => 'leave it',
    ]);

    DecideAgent::assertPrompted('should I ship this');
});

it('prints a small call as json and refuses an empty one', function () {
    DecideAgent::fake([[
        'call' => 'later',
        'why' => 'real, not tonight',
        'next' => 'park it',
    ]])->preventStrayPrompts();

    $this->artisan('jordan:decide', ['question' => 'ship the page', '--json' => true])
        ->assertSuccessful()
        ->expectsOutput('{"call":"later","why":"real, not tonight","next":"park it"}');

    $this->artisan('jordan:decide', ['--no-interaction' => true])
        ->assertFailed()
        ->expectsOutputToContain('Pass a question.');
});

it('lists stored asks and says when none have been sent', function () {
    $this->artisan('jordan:asks')
        ->assertSuccessful()
        ->expectsOutputToContain('No asks yet');

    Ask::factory()->create([
        'question' => 'should I merge',
        'reply' => 'nah',
        'status' => 'done',
        'source' => 'cli',
    ]);

    $listed = new BufferedOutput;

    expect($this->app->make(Kernel::class)->call('jordan:asks', [], $listed))->toBe(0);

    $output = $listed->fetch();

    expect($output)->toContain('should I merge')
        ->and($output)->toContain('done')
        ->and($output)->toContain('nah');
});
