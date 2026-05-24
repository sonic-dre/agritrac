<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'trip_id', 'category', 'label', 'sub_label',
        'amount', 'currency', 'percentage', 'bar_color', 'icon', 'expense_date',
        'latitude', 'longitude',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'integer',
        'percentage'   => 'float',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
