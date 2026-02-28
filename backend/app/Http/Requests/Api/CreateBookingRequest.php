<?php

namespace App\Http\Requests\Api;

class CreateBookingRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hotel_id' => 'required|exists:hotels,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'rooms' => 'required|array|min:1',
            'rooms.*.room_id' => 'required|exists:rooms,id',
            'rooms.*.quantity' => 'required|integer|min:1',
            'currency' => 'nullable|string|size:3',
            'coupon_code' => 'nullable|string|max:64',
        ];
    }
}
