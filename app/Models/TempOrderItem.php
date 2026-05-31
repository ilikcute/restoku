<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempOrderItem extends Model
{
    protected $fillable = [
        'temp_order_id',
        'product_name',
        'price',
        'quantity',
        'subtotal',
        'tax_amount',
        'service_charge',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
    ];

    public function tempOrder()
    {
        return $this->belongsTo(TempOrder::class, 'temp_order_id');
    }
}
