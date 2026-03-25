<?php

namespace App\Http\Requests\Api;

class StoreBookingDisputeRequest extends BaseApiRequest
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
        $rules = [
            'customer_notes' => ['required', 'string', 'min:20', 'max:10000'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ];

        if ($this->user()) {
            $rules['contact_name'] = ['nullable', 'string', 'max:255'];
            $rules['contact_email'] = ['nullable', 'email', 'max:255'];
        } else {
            $rules['contact_name'] = ['required', 'string', 'max:255'];
            $rules['contact_email'] = ['required', 'email', 'max:255'];
        }

        return $rules;
    }
}
