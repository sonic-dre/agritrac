<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncRecord extends Model
{
    protected $fillable = [
        'trip_id', 'agent_id', 'status', 'records_count', 'offline_hours', 'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function getDotClassAttribute(): string
    {
        return match ($this->status) {
            'failed'  => 'sqd-f',
            'pending' => 'sqd-p',
            default   => 'sqd-s',
        };
    }
}
