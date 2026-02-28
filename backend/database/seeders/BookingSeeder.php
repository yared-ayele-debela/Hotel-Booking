<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        if ($customers->isEmpty()) {
            return;
        }

        $hotels = \App\Models\Hotel::with('rooms')->get();
        if ($hotels->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

        // Create multiple customers for more realistic bookings
        $additionalCustomers = [
            ['email' => 'john.doe@test.com', 'name' => 'John Doe'],
            ['email' => 'jane.smith@test.com', 'name' => 'Jane Smith'],
            ['email' => 'robert.wilson@test.com', 'name' => 'Robert Wilson'],
            ['email' => 'mary.jones@test.com', 'name' => 'Mary Jones'],
        ];

        foreach ($additionalCustomers as $customerData) {
            $customer = User::firstOrCreate(
                ['email' => $customerData['email']],
                [
                    'name' => $customerData['name'],
                    'password' => bcrypt('password'),
                    'role' => \App\Enums\Role::CUSTOMER,
                    'status' => 'active',
                ]
            );
        }

        $allCustomers = User::where('role', 'customer')->get();

        // Past/completed bookings (15 bookings)
        for ($i = 0; $i < 15; $i++) {
            $hotel = $hotels->random();
            $room = $hotel->rooms->random();
            $customer = $allCustomers->random();
            
            if (!$room) {
                continue;
            }
            
            $checkIn = Carbon::today()->subDays(rand(10, 90));
            $checkOut = $checkIn->copy()->addDays(rand(1, 7));
            $nights = $checkIn->diffInDays($checkOut);
            $totalPrice = round($room->base_price * $nights, 2);

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'hotel_id' => $hotel->id,
                'status' => 'completed',
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_price' => $totalPrice,
                'currency' => 'USD',
            ]);

            BookingRoom::create([
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'quantity' => rand(1, 2),
                'unit_price' => $room->base_price,
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $totalPrice,
                'currency' => 'USD',
                'provider' => ['stripe', 'paypal', 'credit_card'][array_rand(['stripe', 'paypal', 'credit_card'])],
                'external_id' => 'pi_' . uniqid(),
                'status' => 'succeeded',
                'payload' => ['payment_method' => 'card'],
            ]);

            // Add reviews for most completed bookings
            if (rand(1, 10) <= 7) {
                Review::create([
                    'booking_id' => $booking->id,
                    'rating' => (int) rand(3, 5),
                    'comment' => [
                        'Excellent stay, highly recommended!',
                        'Great location and service.',
                        'Comfortable rooms and friendly staff.',
                        'Would definitely stay again.',
                        'Good value for money.',
                    ][array_rand([
                        'Excellent stay, highly recommended!',
                        'Great location and service.',
                        'Comfortable rooms and friendly staff.',
                        'Would definitely stay again.',
                        'Good value for money.',
                    ])],
                    'approved' => (bool) rand(0, 1),
                ]);
            }
        }

        // Upcoming confirmed bookings (8 bookings)
        for ($i = 0; $i < 8; $i++) {
            $hotel = $hotels->random();
            $room = $hotel->rooms->random();
            $customer = $allCustomers->random();
            
            if (!$room) {
                continue;
            }
            
            $checkIn = Carbon::today()->addDays(rand(1, 30));
            $checkOut = $checkIn->copy()->addDays(rand(1, 5));
            $nights = $checkIn->diffInDays($checkOut);
            $totalPrice = round($room->base_price * $nights, 2);

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'hotel_id' => $hotel->id,
                'status' => 'confirmed',
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_price' => $totalPrice,
                'currency' => 'USD',
            ]);

            BookingRoom::create([
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'quantity' => rand(1, 2),
                'unit_price' => $room->base_price,
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $totalPrice,
                'currency' => 'USD',
                'provider' => ['stripe', 'paypal'][array_rand(['stripe', 'paypal'])],
                'external_id' => 'pi_' . uniqid(),
                'status' => 'succeeded',
                'payload' => null,
            ]);
        }

        // Pending bookings (5 bookings)
        for ($i = 0; $i < 5; $i++) {
            $hotel = $hotels->random();
            $room = $hotel->rooms->random();
            $customer = $allCustomers->random();
            
            if (!$room) {
                continue;
            }
            
            $checkIn = Carbon::today()->addDays(rand(5, 45));
            $checkOut = $checkIn->copy()->addDays(rand(1, 4));
            $nights = $checkIn->diffInDays($checkOut);
            $totalPrice = round($room->base_price * $nights, 2);

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'hotel_id' => $hotel->id,
                'status' => 'pending',
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_price' => $totalPrice,
                'currency' => 'USD',
            ]);

            BookingRoom::create([
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'quantity' => 1,
                'unit_price' => $room->base_price,
            ]);
        }

        // Cancelled bookings (3 bookings)
        for ($i = 0; $i < 3; $i++) {
            $hotel = $hotels->random();
            $room = $hotel->rooms->random();
            $customer = $allCustomers->random();
            
            if (!$room) {
                continue;
            }
            
            $checkIn = Carbon::today()->subDays(rand(5, 20));
            $checkOut = $checkIn->copy()->addDays(rand(1, 3));
            $nights = $checkIn->diffInDays($checkOut);
            $totalPrice = round($room->base_price * $nights, 2);

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'hotel_id' => $hotel->id,
                'status' => 'cancelled',
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_price' => $totalPrice,
                'currency' => 'USD',
            ]);

            BookingRoom::create([
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'quantity' => 1,
                'unit_price' => $room->base_price,
            ]);
        }
    }
}
