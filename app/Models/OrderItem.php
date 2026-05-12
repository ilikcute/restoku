<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price',
        'cost_price',
        'quantity',
        'return_quantity',
        'return_amount',
        'notes',
        'discount_amount',
        'tax_amount',
        'service_charge',
        'subtotal',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'order_item_promotions')
            ->withPivot('discount_amount');
    }
}
