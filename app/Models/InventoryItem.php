<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'external_id',
        'external_inventory_id',
        'external_location_id',
        'code',
        'serial_number',
        'rack',
        'rack_type',
        'status',
        'qty',
        'receive_date',
        'location_code',
        'product_name',
        'product_payload',
        'location_payload',
        'custom_field',
        'raw_payload',
    ];

    protected $casts = [
        'receive_date' => 'datetime',
        'product_payload' => 'array',
        'location_payload' => 'array',
        'custom_field' => 'array',
        'raw_payload' => 'array',
    ];
}
