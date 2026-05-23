<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRecord extends Model
{
    protected $fillable = [
        'produce_type_id', 'price_per_kg', 'currency', 'location', 'period_label', 'recorded_date',
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'price_per_kg'  => 'integer',
    ];

    public function produceType(): BelongsTo
    {
        return $this->belongsTo(ProduceType::class);
    }
}
