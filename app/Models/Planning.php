<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Planning extends Model
{
    use HasFactory;

    protected $table = 'plannings';

    protected $fillable = [
        'location_code',
        'cutting_center',
        'code',
        'type',        // inbound | outbound
        'plan_date',
        'qty',
    ];

    protected $casts = [
        'plan_date' => 'date',
    ];

    /**
     * Scope helper (optional tapi enak dipakai)
     */
    public function scopeInbound($query)
    {
        return $query->where('type', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('type', 'outbound');
    }
}
