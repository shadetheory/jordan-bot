<?php

namespace App\Http\Controllers;

use App\Actions\AskJordan;
use App\Support\AskQuota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $closed = AskQuota::isClosed($request);

        return view('chat', [
            'closed' => $closed,
            'opensAt' => $closed ? AskQuota::opensAt() : null,
            'remaining' => AskQuota::remaining($request),
            'capped' => AskQuota::isCapped(),
            'askPerDay' => AskQuota::max(),
        ]);
    }

    public function store(Request $request, AskJordan $ask): StreamedResponse|JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
        ]);

        if (! $ask->pickup()) {
            return response()->json([
                'message' => AskJordan::BUSY_MESSAGE,
            ], 503);
        }

        if (str_contains((string) $request->header('Accept'), 'text/event-stream')) {
            $stream = $ask->stream($validated['question'], 'web', $request->session()->id());
            $askId = $ask->askId;

            return response()->stream(function () use ($stream, $ask, $askId): void {
                try {
                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }

                    echo 'data: '.json_encode(['type' => 'ask', 'id' => $askId], JSON_THROW_ON_ERROR)."\n\n";
                    flush();

                    foreach ($stream as $event) {
                        echo 'data: '.$event."\n\n";
                        flush();
                    }

                    echo "data: [DONE]\n\n";
                    flush();
                } finally {
                    $ask->hangUp();
                }
            }, 200, [
                'Content-Type' => 'text/event-stream; charset=utf-8',
                'Cache-Control' => 'no-cache, no-transform',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        try {
            $reply = $ask->handle($validated['question'], 'web', $request->session()->id());

            return response()->json([
                'id' => $ask->askId,
                'reply' => $reply,
            ]);
        } finally {
            $ask->hangUp();
        }
    }
}
