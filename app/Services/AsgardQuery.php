<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsgardQuery
{
    /**
     * Official facts for one tenant. Null means the door is closed, not "no facts".
     *
     * @return list<array{id: int|null, tenant: string|null, title: string|null, blob: string}>|null
     */
    public function facts(string $question, ?string $tenant = null): ?array
    {
        $question = trim($question);
        $url = $this->url();
        $token = $this->token();
        $tenant = $tenant ?: (string) config('services.asgard.tenant');

        if ($question === '' || $url === null || $token === null || $tenant === '') {
            return null;
        }

        try {
            $response = Http::connectTimeout(3)
                ->timeout((int) config('services.asgard.timeout', 15))
                ->withToken($token)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'question' => $question,
                    'tenant' => $tenant,
                    'writer_kind' => 'cli',
                    'context' => ['repo' => 'jordanpartridge/jordan-bot'],
                ]);
        } catch (\Throwable $exception) {
            Log::warning('asgard.query.exception', ['error' => $exception->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('asgard.query.http', [
                'status' => $response->status(),
            ]);

            return null;
        }

        $body = $response->json();

        if (! is_array($body) || ! is_array($body['facts'] ?? null)) {
            return null;
        }

        $facts = [];

        foreach ($body['facts'] as $fact) {
            if (! is_array($fact) || ! is_string($fact['blob'] ?? null) || trim($fact['blob']) === '') {
                continue;
            }

            $facts[] = [
                'id' => isset($fact['id']) && is_numeric($fact['id']) ? (int) $fact['id'] : null,
                'tenant' => is_string($fact['tenant'] ?? null) ? $fact['tenant'] : $tenant,
                'title' => is_string($fact['title'] ?? null) ? $fact['title'] : null,
                'blob' => $fact['blob'],
            ];
        }

        return $facts;
    }

    public function enabled(): bool
    {
        return $this->url() !== null && $this->token() !== null;
    }

    private function url(): ?string
    {
        $base = trim((string) config('services.asgard.url'));

        if ($base === '') {
            return null;
        }

        return rtrim($base, '/').'/api/lexi/knowledge/query';
    }

    private function token(): ?string
    {
        $token = trim((string) config('services.asgard.token'));

        return $token === '' ? null : $token;
    }
}
