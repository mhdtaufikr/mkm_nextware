<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'code',
        'serial_number',
        'qty',
        'qty_process',
        'rack',
        'rack_source',
        'external_location_id',
        'external_location_id_source',
        'ref_number_outbound',
        'status',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
