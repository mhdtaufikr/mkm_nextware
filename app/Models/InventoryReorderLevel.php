<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryReorderLevel extends Model
{
    protected $fillable = [
        'inventory_id',
        'code',
        'cutting_center',
        'reorder_level',
        'reorder_qty',
        'unit_price',
        'reorder_value',
        'is_active',
        'last_calculated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_calculated_at' => 'datetime',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
