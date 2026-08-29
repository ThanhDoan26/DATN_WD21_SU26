<?php

return [

    'gemini' => [

        'api_key' => env('GEMINI_API_KEY'),

        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),

        'fallback_models' => [
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
            'gemini-flash-lite-latest',
            'gemini-3.1-flash-lite',
            'gemini-3-flash-preview',
        ],

        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',

    ],

];