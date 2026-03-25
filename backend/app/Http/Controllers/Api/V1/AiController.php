<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PlatformSetting;
use App\Services\AiBookingService;
use App\Services\GeminiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends BaseApiController
{
    public function __construct(
        protected AiBookingService $aiBookingService,
        protected GeminiClient $gemini
    ) {}

    /**
     * Personalized + popular hotel picks (optional auth for personalization).
     */
    public function recommendations(Request $request): JsonResponse
    {
        $payload = $this->aiBookingService->recommendations($request->user(), $request);

        return $this->success($payload);
    }

    /**
     * AI chat assistant for booking help (Gemini when configured).
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:24'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:6000'],
        ]);

        if (! $this->gemini->isConfigured()) {
            return $this->error('AI assistant is not configured. Set GEMINI_API_KEY in the server environment.', 503, 'AI_DISABLED');
        }

        $site = PlatformSetting::get('site_name', config('app.name'));
        $system = "You are a helpful assistant for {$site}, a hotel booking website. ".
            'Answer questions about how to search, book, cancellations in general terms, and navigating the site. '.
            'Keep replies concise (under 180 words). Do not invent specific hotel names, prices, or availability; '.
            'tell users to check live search results on the site for current rates and dates.';

        $trimmed = array_slice($validated['messages'], -16);
        $messages = array_merge(
            [['role' => 'system', 'content' => $system]],
            array_map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']], $trimmed)
        );

        $reply = $this->gemini->chatCompletion($messages, 0.65);
        if ($reply === null) {
            return $this->error('The assistant is temporarily unavailable. Please try again.', 503, 'AI_UNAVAILABLE');
        }

        return $this->success([
            'message' => [
                'role' => 'assistant',
                'content' => $reply,
            ],
            'ai_enabled' => true,
        ]);
    }

    /**
     * Summarized sentiment for verified reviews (cached per hotel).
     */
    public function reviewSentiment(Request $request, int $id): JsonResponse
    {
        $data = $this->aiBookingService->reviewSentiment($id, $request);
        if ($data === null) {
            return $this->error('Hotel not found.', 404, 'NOT_FOUND');
        }

        return $this->success($data);
    }
}
