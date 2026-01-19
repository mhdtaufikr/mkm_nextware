<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'external_id',
        'external_location_id',
        'organization_id',
        'location_code',
        'code',
        'name',
        'qty',
        'qty_goods',
        'qty_available',
        'qty_incoming',
        'qty_outgoing',
        'stock_status',
        'created_by',
        'api_created_at',
        'api_updated_at',
        'last_calculated',
        'rack_type',
        'location_payload',
        'product_payload',
        'custom_field',
        'raw_payload',
    ];

    protected $casts = [
        'qty' => 'integer',
        'qty_goods' => 'integer',
        'qty_available' => 'integer',
        'qty_incoming' => 'integer',
        'qty_outgoing' => 'integer',
        'location_payload' => 'array',
        'product_payload' => 'array',
        'custom_field' => 'array',
        'raw_payload' => 'array',
        'api_created_at' => 'datetime',
        'api_updated_at' => 'datetime',
        'last_calculated' => 'datetime',
    ];

        public function reorderLevel()
    {
        return $this->hasOne(InventoryReorderLevel::class);
    }

}
