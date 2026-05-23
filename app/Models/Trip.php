<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'agent_id', 'region', 'produce_list', 'start_date', 'total_days',
        'current_day', 'status', 'sync_status', 'offline_hours',
        'unsynced_records', 'tonnage_kg', 'amount_spent', 'advance_amount', 'revenue',
        'negotiated_price_per_kg', 'payment_type', 'currency',
    ];

    protected $casts = [
        'produce_list'            => 'array',
        'start_date'              => 'date',
        'tonnage_kg'              => 'float',
        'amount_spent'            => 'integer',
        'advance_amount'          => 'integer',
        'negotiated_price_per_kg' => 'integer',
        'revenue'                 => 'integer',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function syncRecords(): HasMany
    {
        return $this->hasMany(SyncRecord::class);
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
            'offline' => 'Offline ' . $this->offline_hours . 'h',
            'pending' => 'Pending ' . $this->offline_hours . 'h',
            default   => 'Synced',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'In Progress',
            'returning'   => 'Returning',
            'departing'   => 'Departing',
            'completed'   => 'Completed',
            default       => ucfirst($this->status),
        };
    }

    public function getProduceStringAttribute(): string
    {
        return is_array($this->produce_list) ? implode(', ', $this->produce_list) : '';
    }
}
