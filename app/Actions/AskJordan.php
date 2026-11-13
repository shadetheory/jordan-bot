<?php

namespace App\Actions;

use App\Ai\Agents\JordanAgent;
use App\Models\Ask;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Laravel\Ai\Responses\StreamedAgentResponse;
use Throwable;

class AskJordan
{
    public ?int $askId = null;

    public const BUSY_MESSAGE = 'THE SMART PERSON IS ON THE OTHER LINE. Operators are sitting by. Try again.';

    public const BUSY_KEY = 'jordan.ask.busy';

    public function pickup(): bool
    {
        return Cache::add(self::BUSY_KEY, 1, (int) config('jordan.timeout'));
    }

    public function hangUp(): void
    {
        Cache::forget(self::BUSY_KEY);
    }

    public function handle(string $question, string $source = 'cli', ?string $thread = null): string
    {
        $ask = $this->start($question, $source, $thread);

        try {
            $response = (new JordanAgent($question, $source, $thread))->prompt(
                $question,
                provider: Lab::Ollama,
                model: (string) config('jordan.model'),
                timeout: (int) config('jordan.timeout'),
            );

            return $this->finish($ask, $response->text);
        } catch (Throwable $exception) {
            $this->fail($ask, $exception);

            throw $exception;
        }
    }

    public function stream(string $question, string $source = 'web', ?string $thread = null): StreamableAgentResponse
    {
        $ask = $this->start($question, $source, $thread);

        return (new JordanAgent($question, $source, $thread))->stream(
            $question,
            provider: Lab::Ollama,
            model: (string) config('jordan.model'),
            timeout: (int) config('jordan.timeout'),
        )->then(function (StreamedAgentResponse $response) use ($ask): void {
            $this->finish($ask, $response->text);
        });
    }

    public function start(string $question, string $source, ?string $thread = null): Ask
    {
        $ask = Ask::query()->create([
            'source' => $source,
            'session_id' => $thread,
            'status' => 'started',
            'question' => $question,
        ]);

        $this->askId = $ask->id;

        Log::info('jordan.ask.start', [
            'id' => $ask->id,
            'source' => $source,
            'question' => $question,
        ]);

        return $ask;
    }

    public function finish(Ask $ask, string $reply): string
    {
        $ask->update([
            'status' => 'done',
            'reply' => $reply,
        ]);

        Log::info('jordan.ask.done', [
            'id' => $ask->id,
            'source' => $ask->source,
        ]);

        return $reply;
    }

    public function fail(Ask $ask, Throwable $exception): void
    {
        $ask->update([
            'status' => 'failed',
            'reply' => $exception->getMessage(),
        ]);

        Log::error('jordan.ask.failed', [
            'id' => $ask->id,
            'source' => $ask->source,
            'error' => $exception->getMessage(),
        ]);
    }
}
