<?php

namespace App\Http\Controllers;

use App\Models\Ask;
use App\Support\SpokenLines;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SpeakController extends Controller
{
    public function __invoke(Request $request, Ask $ask): Response|JsonResponse
    {
        $key = (string) config('services.xai.key');
        $voiceId = (string) config('services.xai.voice_id');

        if ($key === '' || $voiceId === '') {
            return response()->json([
                'message' => 'Voice is not configured. Set XAI_API_KEY and XAI_VOICE_ID.',
            ], 503);
        }

        $sessionId = (string) $request->session()->id();

        if ($ask->session_id === null || ! hash_equals((string) $ask->session_id, $sessionId)) {
            return response()->json([
                'message' => 'That reply is not in this chat.',
            ], 404);
        }

        $reply = trim((string) $ask->reply);

        if ($ask->status !== 'done' || $reply === '') {
            return response()->json([
                'message' => 'That reply is not stored yet.',
            ], 404);
        }

        $spoken = SpokenLines::fromStoredReply($reply);

        if ($spoken === '') {
            return response()->json([
                'message' => 'That reply has nothing to say out loud.',
            ], 422);
        }

        try {
            $response = Http::withToken($key)
                ->accept('audio/mpeg')
                ->timeout(60)
                ->post('https://api.x.ai/v1/tts', [
                    'text' => $spoken,
                    'voice_id' => $voiceId,
                    'language' => 'en',
                ]);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Speech failed.',
            ], 502);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Speech failed.',
            ], 502);
        }

        $type = (string) $response->header('Content-Type');

        return response($response->body(), 200, [
            'Content-Type' => str_starts_with($type, 'audio/') ? $type : 'audio/mpeg',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
