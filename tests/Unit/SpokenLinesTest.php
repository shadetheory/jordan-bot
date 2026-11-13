<?php

use App\Support\SpokenLines;

it('strips markdown into lines that can be spoken', function () {
    $spoken = SpokenLines::fromStoredReply(<<<'REPLY'
    **nah.** later.

    See [the note](https://example.test/secret) and `this`.

    - one step
    1. two steps
    REPLY);

    expect($spoken)->toBe('nah. later. [pause] See the note and this. one step two steps');
});

it('drops fenced code and images so a silent reply stays silent', function () {
    expect(SpokenLines::fromStoredReply("```\nonly code\n```"))->toBe('');
    expect(SpokenLines::fromStoredReply('![](https://example.test/host.jpg)'))->toBe('');
});

it('caps a very long reply', function () {
    $spoken = SpokenLines::fromStoredReply(str_repeat('a', 16000));

    expect(mb_strlen($spoken))->toBe(15000);
});
