<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'external_id',
        'ref_number',
        'type',
        'status',
        'customer_name',
        'external_location_id',
        'organization_id',
        'total',
        'total_item',
        'external_created_at',
        'external_updated_at',
        'raw_item',
        'custom_field',
        'raw_payload',
    ];

    protected $casts = [
        'raw_item' => 'array',
        'custom_field' => 'array',
        'raw_payload' => 'array',
        'external_created_at' => 'datetime',
        'external_updated_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
