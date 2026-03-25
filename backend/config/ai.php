<?php

// Normalize key: strip whitespace; allow GOOGLE_API_KEY as fallback (common naming in Cloud Console).
$rawKey = env('GEMINI_API_KEY') ?: env('GOOGLE_API_KEY');
$apiKey = is_string($rawKey) && $rawKey !== '' ? trim($rawKey) : null;

return [

    /*
    |--------------------------------------------------------------------------
    | Google Gemini (generateContent)
    |--------------------------------------------------------------------------
    | Get an API key: https://aistudio.google.com/apikey
    | Set GEMINI_API_KEY in .env (must be an AI Studio / Generative Language API key).
    | After changing .env, run: php artisan config:clear
    */

    'api_key' => $apiKey,

    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),

    'base_url' => rtrim(env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/'),

    'timeout' => (int) env('GEMINI_TIMEOUT', 45),

];
