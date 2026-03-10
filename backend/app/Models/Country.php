<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = ['name', 'code', 'image', 'tax_rate', 'tax_name'];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class, 'country_id');
    }
}
