<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'external_id',
        'name','display_name','description',
        'lat','lng','address','phone',
        'location_type','location_code',
        'is_default','zip_code','timezone',
        'organization_id','status',
        'amount_balance','total_user','is_enable_wallet',
        'raw_payload'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_enable_wallet' => 'boolean',
        'raw_payload' => 'array',
        'lat' => 'float',
        'lng' => 'float',
    ];
}
