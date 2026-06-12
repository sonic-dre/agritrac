<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LookupValue extends Model
{
    protected $fillable = ['group', 'label', 'value', 'emoji', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
