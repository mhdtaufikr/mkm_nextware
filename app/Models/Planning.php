<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    protected $table = 'plannings';

    protected $fillable = [
        'location_code',
        'cutting_center',
        'type',
        'plan_date',
        'qty',
    ];

    protected $casts = [
        'plan_date' => 'date',
    ];
}
