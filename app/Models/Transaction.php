<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Unit;

class Transaction extends Model
{
    protected $fillable = [
        'trip_id', 'agent_id', 'produce_type_id', 'type',
        'quantity_kg', 'unit_id', 'unit_price', 'total_amount', 'currency',
        'location', 'category', 'transaction_date', 'sync_status', 'notes',
        'latitude', 'longitude', 'moisture_content',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity_kg'      => 'float',
        'unit_price'       => 'integer',
        'total_amount'     => 'integer',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function produceType(): BelongsTo
    {
        return $this->belongsTo(ProduceType::class);
    }

    public function getSyncBadgeAttribute(): string
    {
        return match ($this->sync_status) {
            'offline' => 'sp-of',
            'pending' => 'sp-pe',
            default   => 'sp-sy',
        };
    }

    public function getSyncLabelAttribute(): string
    {
        return match ($this->sync_status) {
            'offline' => 'Offline ' . ($this->notes ?? ''),
            'pending' => 'Pending',
            default   => 'Synced',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->total_amount >= 0 ? '+' : '';
        return $sign . number_format($this->total_amount);
    }
}
