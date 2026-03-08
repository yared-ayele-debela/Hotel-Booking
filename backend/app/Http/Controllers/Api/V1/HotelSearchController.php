<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\HotelSearchRequest;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HotelSearchController extends BaseApiController
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    /**
     * Search hotels: location, date availability, price range, min rating. Paginated.
     */
    public function index(HotelSearchRequest $request): JsonResponse
    {
        $query = Hotel::query()->where('status', 'active');

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        } elseif ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        } elseif ($request->filled('country')) {
            $query->where('country', 'like', '%'.$request->country.'%');
        }

        if ($request->filled('latitude') && $request->filled('longitude') && $request->filled('radius_km')) {
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;
            $km = (float) $request->radius_km;
            // Haversine approximation: 1 degree ~ 111 km
            $query->whereNotNull('latitude')->whereNotNull('longitude')
                ->whereRaw('( 6371 * acos( least(1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)) ) ) ) <= ?', [$lat, $lng, $lat, $km]);
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            $roomIds = $this->availabilityService->getRoomIdsWithAvailability(
                $request->check_in,
                $request->check_out,
                1
            );
            if ($roomIds === []) {
                return $this->success(['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => $request->input('per_page', 15), 'total' => 0]]);
            }
            $hotelIds = Room::whereIn('id', $roomIds)->pluck('hotel_id')->unique()->values()->all();
            $query->whereIn('id', $hotelIds);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('rooms', function ($q) use ($request) {
                if ($request->filled('min_price')) {
                    $q->where('base_price', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('base_price', '<=', $request->max_price);
                }
            });
        }

        if ($request->filled('min_rating')) {
            $hotelIds = DB::table('reviews')
                ->join('bookings', 'reviews.booking_id', '=', 'bookings.id')
                ->where('reviews.approved', true)
                ->groupBy('bookings.hotel_id')
                ->havingRaw('AVG(reviews.rating) >= ?', [$request->min_rating])
                ->pluck('bookings.hotel_id');
            $query->whereIn('id', $hotelIds);
        }

        if ($request->filled('min_capacity')) {
            $query->whereHas('rooms', function ($q) use ($request) {
                $q->where('capacity', '>=', (int) $request->min_capacity);
            });
        }

        if ($request->filled('amenities') && count($request->amenities) > 0) {
            $slugs = array_map('strval', $request->amenities);
            $query->whereHas('amenities', function ($q) use ($slugs) {
                $q->whereIn('slug', $slugs);
            }, '>=', count($slugs));
        }

        $query->selectRaw('hotels.*, (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r INNER JOIN bookings b ON r.booking_id = b.id WHERE b.hotel_id = hotels.id AND r.approved = 1) as average_rating, (SELECT COUNT(*) FROM reviews r INNER JOIN bookings b ON r.booking_id = b.id WHERE b.hotel_id = hotels.id AND r.approved = 1) as review_count, (SELECT MIN(rooms.base_price) FROM rooms WHERE rooms.hotel_id = hotels.id AND rooms.deleted_at IS NULL) as min_room_price');

        if ($request->filled('sort')) {
            match ($request->sort) {
                'price_low' => $query->orderByRaw('min_room_price ASC'),
                'price_high' => $query->orderByRaw('min_room_price DESC'),
                'rating' => $query->orderByRaw('average_rating DESC'),
                'name' => $query->orderBy('name', 'asc'),
                default => null,
            };
        }

        $perPage = (int) $request->input('per_page', 15);
        $paginator = $query->with(['rooms', 'rooms.images', 'images', 'amenities'])->paginate($perPage);

        return $this->success([
            'data' => HotelResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Single hotel with rooms. Optional check_in/check_out for availability context.
     */
    public function show(int $id): JsonResponse
    {
        $hotel = Hotel::query()
            ->where('id', $id)
            ->where('status', 'active')
            ->selectRaw('hotels.*, (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r INNER JOIN bookings b ON r.booking_id = b.id WHERE b.hotel_id = hotels.id AND r.approved = 1) as average_rating, (SELECT COUNT(*) FROM reviews r INNER JOIN bookings b ON r.booking_id = b.id WHERE b.hotel_id = hotels.id AND r.approved = 1) as review_count')
            ->with(['rooms.hotel', 'rooms.images', 'rooms.amenities', 'images', 'amenities'])
            ->firstOrFail();
        return $this->success(new HotelResource($hotel));
    }
}
