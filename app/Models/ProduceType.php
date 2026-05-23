<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProduceType extends Model
{
    protected $fillable = [
        'name', 'emoji', 'slug', 'current_price', 'change_percent',
        'signal', 'primary_location', 'accent_color',
    ];

    protected $casts = [
        'current_price' => 'integer',
        'change_percent' => 'float',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function priceRecords(): HasMany
    {
        return $this->hasMany(PriceRecord::class);
    }

    public function getSignalLabelAttribute(): string
    {
        return match ($this->signal) {
            'buy'  => '↑ Buy Now',
            'sell' => '↓ Sell Fast',
            default => '→ Hold',
        };
    }

    public function getSignalClassAttribute(): string
    {
        return match ($this->signal) {
            'buy'  => 'buy',
            'sell' => 'sell',
            default => 'hold',
        };
    }
}
