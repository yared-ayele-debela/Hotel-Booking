<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    public function isConfigured(): bool
    {
        return filled(config('ai.api_key'));
    }

    /**
     * Chat-style completion. Maps OpenAI-like messages to Gemini roles (user / model).
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chatCompletion(
        array $messages,
        float $temperature = 0.7,
        ?array $responseFormatJson = null
    ): ?string {
        if (! $this->isConfigured()) {
            return null;
        }

        $systemTexts = [];
        $contents = [];

        foreach ($messages as $m) {
            $role = $m['role'] ?? 'user';
            $content = (string) ($m['content'] ?? '');
            if ($role === 'system') {
                $systemTexts[] = $content;

                continue;
            }
            $geminiRole = $role === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $geminiRole,
                'parts' => [['text' => $content]],
            ];
        }

        $body = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => 8192,
            ],
        ];

        if ($systemTexts !== []) {
            $body['systemInstruction'] = [
                'parts' => [['text' => implode("\n\n", $systemTexts)]],
            ];
        }

        if ($responseFormatJson !== null) {
            $body['generationConfig']['responseMimeType'] = 'application/json';
        }

        $model = config('ai.model');
        $base = (string) config('ai.base_url');
        $url = $base.'/models/'.$model.':generateContent';

        try {
            $response = Http::timeout((int) config('ai.timeout', 45))
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url.'?key='.urlencode((string) config('ai.api_key')), $body);

            if (! $response->successful()) {
                Log::warning('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            $data = $response->json();
            $candidate = $data['candidates'][0] ?? null;
            if (! is_array($candidate)) {
                return null;
            }

            if (($candidate['finishReason'] ?? '') === 'SAFETY') {
                Log::warning('Gemini blocked response (safety)');

                return null;
            }

            $parts = $candidate['content']['parts'] ?? null;
            if (! is_array($parts)) {
                return null;
            }

            $text = '';
            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    $text .= $part['text'];
                }
            }

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            Log::warning('Gemini request failed', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
