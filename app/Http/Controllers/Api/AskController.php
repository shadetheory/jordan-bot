<?php

namespace App\Http\Controllers\Api;

use App\Actions\AskJordan;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, AskJordan $ask): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
        ]);

        if (! $ask->pickup()) {
            return response()->json([
                'message' => AskJordan::BUSY_MESSAGE,
            ], 503);
        }

        try {
            return response()->json([
                'reply' => $ask->handle($validated['question'], 'api'),
            ]);
        } finally {
            $ask->hangUp();
        }
    }
}
