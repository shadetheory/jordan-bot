<?php

return [

    'model' => env('JORDAN_MODEL', 'qwen3.8'),

    'timeout' => (int) env('JORDAN_TIMEOUT', 180),

    'dumps_path' => env('JORDAN_DUMPS_PATH', storage_path('app/private/dumps')),

    'voice_path' => env('JORDAN_VOICE_PATH', storage_path('app/private/voice/transcripts.txt')),

    'sessions_path' => env('JORDAN_SESSIONS_PATH', ($_SERVER['HOME'] ?? getenv('HOME') ?: '/home/jordan').'/.grok/sessions'),

    'ask_per_day' => (int) env('JORDAN_ASK_PER_DAY', 5),

    'asgard_questions' => [
        'SQL wins',
        'Lexi is the face',
        'Asgard is the data store',
        'Vector is an index',
        'Chat is not the inbox',
    ],

];
