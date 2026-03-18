<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBankAccount extends Model
{
    protected $fillable = [
        'vendor_id',
        'account_holder_name',
        'bank_name',
        'account_number',
        'routing_number',
        'swift_code',
        'currency',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Masked account number for display (e.g. ****1234).
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        $num = $this->account_number;
        if (strlen($num) <= 4) {
            return str_repeat('*', strlen($num));
        }
        return str_repeat('*', strlen($num) - 4) . substr($num, -4);
    }
}
