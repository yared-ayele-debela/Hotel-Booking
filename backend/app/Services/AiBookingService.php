<?php

namespace App\Services;

use App\Http\Resources\HotelResource;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Review;
use App\Models\SavedHotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AiBookingService
{
    public function __construct(
        protected GeminiClient $gemini
    ) {}

    /**
     * @return array{ai_enabled: bool, personalized: bool, tagline: string|null, data: list<array{hotel: array<string, mixed>, reason: string}>}
     */
    public function recommendations(?User $user, Request $request): array
    {
        $candidates = $this->candidateHotelIds($user);
        $hotels = $this->loadHotels($candidates);
        if ($hotels->isEmpty()) {
            return [
                'ai_enabled' => $this->gemini->isConfigured(),
                'personalized' => false,
                'tagline' => null,
                'data' => [],
            ];
        }

        $personalized = $user !== null && $this->hasPersonalSignals($user);
        $aiEnabled = $this->gemini->isConfigured();
        $tagline = null;
        /** @var array<int, string> $reasons */
        $reasons = [];
        $ordered = null;

        if ($aiEnabled) {
            $parsed = $this->runRecommendationAi($user, $hotels);
            if (is_array($parsed)) {
                $tagline = isset($parsed['tagline']) ? (string) $parsed['tagline'] : null;
                if (! empty($parsed['items']) && is_array($parsed['items'])) {
                    foreach ($parsed['items'] as $row) {
                        $hid = isset($row['hotel_id']) ? (int) $row['hotel_id'] : 0;
                        if ($hid > 0 && isset($row['reason'])) {
                            $reasons[$hid] = (string) $row['reason'];
                        }
                    }
                    $ordered = collect($parsed['items'])->pluck('hotel_id')->map(fn ($id) => (int) $id)->filter()->values()->all();
                }
            }
        }

        if (is_array($ordered) && $ordered !== []) {
            $hotels = $hotels->sortBy(fn ($h) => array_search($h->id, $ordered, true) ?: 999)->values();
        }

        $slice = $hotels->take(6);
        $data = [];
        foreach ($slice as $hotel) {
            $reason = $reasons[$hotel->id] ?? $this->fallbackReason($hotel);
            $data[] = [
                'hotel' => (new HotelResource($hotel))->toArray($request),
                'reason' => $reason,
            ];
        }

        return [
            'ai_enabled' => $aiEnabled,
            'personalized' => $personalized,
            'tagline' => $tagline ?? ($personalized
                ? 'Suggested from your wishlist and past stays.'
                : 'Popular stays with strong guest ratings.'),
            'data' => $data,
        ];
    }

    /**
     * @return array{summary: string, bullets: list<string>, tone: string, avg_rating: float, review_count: int, ai_enabled: bool}|null
     */
    public function reviewSentiment(int $hotelId, Request $request): ?array
    {
        $hotel = Hotel::query()
            ->where('id', $hotelId)
            ->where('status', 'active')
            ->whereHas('vendor', fn ($q) => $q->whereHas('vendorProfile', fn ($q2) => $q2->where('status', 'approved')))
            ->first();
        if (! $hotel) {
            return null;
        }

        return Cache::remember('hotel_review_sentiment.'.$hotelId, 86400, function () use ($hotel, $hotelId, $request) {
            $stats = Review::query()
                ->where('approved', true)
                ->whereHas('booking', fn ($q) => $q->where('hotel_id', $hotelId))
                ->selectRaw('AVG(rating) as avg_r, COUNT(*) as c')
                ->first();
            $avg = $stats && $stats->avg_r !== null ? round((float) $stats->avg_r, 2) : 0.0;
            $count = $stats ? (int) $stats->c : 0;

            $comments = Review::query()
                ->where('approved', true)
                ->whereHas('booking', fn ($q) => $q->where('hotel_id', $hotelId))
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->latest()
                ->limit(50)
                ->pluck('comment')
                ->all();

            $aiEnabled = $this->gemini->isConfigured();
            $summary = '';
            $bullets = [];
            $tone = 'mixed';

            if ($count === 0) {
                return [
                    'summary' => 'No verified reviews yet for this property.',
                    'bullets' => [],
                    'tone' => 'neutral',
                    'avg_rating' => 0.0,
                    'review_count' => 0,
                    'ai_enabled' => $aiEnabled,
                    'hotel' => (new HotelResource($hotel))->toArray($request),
                ];
            }

            if ($aiEnabled && $comments !== []) {
                $text = implode("\n---\n", array_slice($comments, 0, 40));
                $prompt = "Hotel: {$hotel->name}. Here are verified guest comments (may be multiple languages). Respond with JSON only:\n{\n  \"summary\": \"2-3 sentences on overall sentiment\",\n  \"bullets\": [\"up to 4 short bullet points: pros or themes\"],\n  \"tone\": \"positive\"|\"mixed\"|\"negative\"\n}\n\nComments:\n".$text;
                $raw = $this->gemini->chatCompletion([
                    ['role' => 'system', 'content' => 'You summarize hotel review sentiment for travelers. Be fair and concise. JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ], 0.25, ['type' => 'json_object']);
                if ($raw) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $summary = (string) ($decoded['summary'] ?? '');
                        $bullets = is_array($decoded['bullets'] ?? null) ? array_values(array_map('strval', $decoded['bullets'])) : [];
                        $tone = in_array($decoded['tone'] ?? '', ['positive', 'mixed', 'negative'], true)
                            ? $decoded['tone']
                            : 'mixed';
                    }
                }
            }

            if ($summary === '') {
                $summary = $this->fallbackSentimentSummary($avg, $count);
                $bullets = $this->fallbackBullets($avg);
                $tone = $avg >= 4 ? 'positive' : ($avg >= 3 ? 'mixed' : 'negative');
            }

            return [
                'summary' => $summary,
                'bullets' => array_slice($bullets, 0, 4),
                'tone' => $tone,
                'avg_rating' => $avg,
                'review_count' => $count,
                'ai_enabled' => $aiEnabled && $comments !== [],
                'hotel' => (new HotelResource($hotel))->toArray($request),
            ];
        });
    }

    /**
     * @return list<int>
     */
    private function candidateHotelIds(?User $user): array
    {
        $ids = [];
        if ($user) {
            $ids = array_merge(
                SavedHotel::where('user_id', $user->id)->pluck('hotel_id')->all(),
                Booking::where('customer_id', $user->id)
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->pluck('hotel_id')
                    ->all()
            );
            $cities = Booking::where('customer_id', $user->id)
                ->whereIn('status', ['confirmed', 'completed'])
                ->with('hotel:id,city_id,city')
                ->get()
                ->pluck('hotel')
                ->filter()
                ->pluck('city_id')
                ->filter()
                ->unique()
                ->values()
                ->all();
            if ($cities !== []) {
                $more = Hotel::query()
                    ->where('status', 'active')
                    ->whereIn('city_id', $cities)
                    ->whereHas('vendor', fn ($q) => $q->whereHas('vendorProfile', fn ($q2) => $q2->where('status', 'approved')))
                    ->limit(12)
                    ->pluck('id')
                    ->all();
                $ids = array_merge($ids, $more);
            }
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (count($ids) < 8) {
            $top = Hotel::query()
                ->where('status', 'active')
                ->whereHas('vendor', fn ($q) => $q->whereHas('vendorProfile', fn ($q2) => $q2->where('status', 'approved')))
                ->selectRaw('hotels.id, (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r INNER JOIN bookings b ON r.booking_id = b.id WHERE b.hotel_id = hotels.id AND r.approved = 1) as ar')
                ->orderByDesc('ar')
                ->limit(20)
                ->pluck('id')
                ->all();
            $ids = array_values(array_unique(array_merge($ids, array_map('intval', $top))));
        }

        return array_slice($ids, 0, 24);
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, Hotel>
     */
    private function loadHotels(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Hotel::query()
            ->whereIn('id', $ids)
            ->where('status', 'active')
            ->whereHas('vendor', fn ($q) => $q->whereHas('vendorProfile', fn ($q2) => $q2->where('status', 'approved')))
            ->selectRaw('hotels.*, (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r INNER JOIN bookings b ON r.booking_id = b.id WHERE b.hotel_id = hotels.id AND r.approved = 1) as average_rating, (SELECT COUNT(*) FROM reviews r INNER JOIN bookings b ON r.booking_id = b.id WHERE b.hotel_id = hotels.id AND r.approved = 1) as review_count, (SELECT MIN(rooms.base_price) FROM rooms WHERE rooms.hotel_id = hotels.id AND rooms.deleted_at IS NULL) as min_room_price')
            ->with(['rooms', 'rooms.images', 'images', 'amenities'])
            ->get()
            ->sortByDesc(fn ($h) => (float) ($h->average_rating ?? 0))
            ->values();
    }

    private function hasPersonalSignals(User $user): bool
    {
        return SavedHotel::where('user_id', $user->id)->exists()
            || Booking::where('customer_id', $user->id)->whereIn('status', ['confirmed', 'completed'])->exists();
    }

    /**
     * @param  Collection<int, Hotel>  $hotels
     * @return array{tagline?: string, items?: list<array{hotel_id: int, reason: string}>}|null
     */
    private function runRecommendationAi(?User $user, Collection $hotels): ?array
    {
        $lines = [];
        foreach ($hotels->take(18) as $h) {
            $amenities = $h->amenities->pluck('name')->take(8)->implode(', ');
            $lines[] = sprintf(
                'id=%d; name=%s; city=%s; country=%s; rating=%.2f; reviews=%d; from_price=%s; amenities=%s',
                $h->id,
                $h->name,
                (string) $h->city,
                (string) $h->country,
                (float) ($h->average_rating ?? 0),
                (int) ($h->review_count ?? 0),
                $h->min_room_price !== null ? number_format((float) $h->min_room_price, 2) : 'n/a',
                $amenities
            );
        }
        $profile = $user
            ? 'Logged-in user. Prioritize hotels matching their saved wishlist or cities they stayed in before when relevant.'
            : 'Guest user (not logged in). Favor highly rated, well-reviewed properties.';

        $prompt = $profile."\n\nHotels:\n".implode("\n", $lines)."\n\nReturn JSON only:\n{\n  \"tagline\": \"one short engaging line for the homepage\",\n  \"items\": [\n    {\"hotel_id\": <number>, \"reason\": \"one sentence why it fits\"}\n  ]\n}\nMax 6 items, best matches first.";

        $raw = $this->gemini->chatCompletion([
            ['role' => 'system', 'content' => 'You help travelers pick hotels. Output valid JSON only.'],
            ['role' => 'user', 'content' => $prompt],
        ], 0.45, ['type' => 'json_object']);

        if (! $raw) {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function fallbackReason(Hotel $hotel): string
    {
        $city = trim(implode(', ', array_filter([(string) $hotel->city, (string) $hotel->country])));

        return $city !== ''
            ? "Well-rated in {$city} — browse rooms and book with flexible options."
            : 'Popular with guests — strong ratings and verified reviews.';
    }

    private function fallbackSentimentSummary(float $avg, int $count): string
    {
        return sprintf(
            'Guests rate this property %.1f/5 on average across %d verified review%s.',
            $avg,
            $count,
            $count === 1 ? '' : 's'
        );
    }

    /**
     * @return list<string>
     */
    private function fallbackBullets(float $avg): array
    {
        if ($avg >= 4.2) {
            return ['Overall feedback is very positive.', 'Worth comparing room types before booking.'];
        }
        if ($avg >= 3.5) {
            return ['Reviews are generally favorable.', 'Read recent comments for details that matter to you.'];
        }

        return ['Ratings are mixed — read individual reviews carefully.', 'Contact the property if you have specific needs.'];
    }
}
